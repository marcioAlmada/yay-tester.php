<?php declare(strict_types = 1);

use PhpParser\{ParserFactory, PrettyPrinter};

set_error_handler(function($errno, $errstr, $errfile, $errline){
    error_log("Error: \"{$errstr}\" at {$errfile}:{$errline}");
    throw new Exception("{$errstr} in {$errfile} at line {$errline}.");
});

require __DIR__ . "/vendor/autoload.php";

if (
    $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' ||
    !preg_match("/Development Server/", $_SERVER['SERVER_SOFTWARE'])
) {
    die('This is dangerous, only run with Builtin server and localhost access');
}

session_start();

$code = '';
$converted_code = '';
$yay = '';
$yay_code = '';
$output = '';

if (isset($_POST['code'])) {
    $code = $_POST['code'];
    $yay = $_POST['yay'];
    if ($_POST['csrf'] !== $_SESSION['csrf']) {
        die('csrf');
    }

    try {
        ob_start();
        $engine = (new \Yay\Engine);
        $yay_code = $engine->expand($yay, '', \Yay\Engine::GC_ENGINE_DISABLED);
        $converted_code = $engine->expand($code);
        $output .= ob_get_clean();
        if ($output) $output .= PHP_EOL;

    }
    catch (\Yay\YayParseError $e) {
        $converted_code = "\Yay\YayParseError: " . (string) $e->getMessage();
    }
    catch (\Throwable $e) {
        $converted_code = (string) $e;
        $converted_code .= $e->getMessage();
    }
    finally {
        ob_get_clean();
    }

    try {
        ob_start();
        eval(preg_replace('/^\<\?php/', '', $yay_code));
        eval(preg_replace('/^\<\?php/', '', $converted_code));
        $output .= ob_get_clean() . PHP_EOL;
    }
    catch (\Throwable $e) {
        $output = (string) $e;
    }
    finally {
        ob_get_clean();
    }

    try {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $prettyPrinter = new PrettyPrinter\Standard;
        $stmts = $parser->parse($converted_code);
        $converted_code = $prettyPrinter->prettyPrintFile($stmts);
    }
    catch (\Throwable $e) {
        $output = (string) $e;
    }
    finally {
        ob_get_clean();
    }

} else {
    $filename = (string)($_GET['filename'] ?? 'hygiene');
    if (!preg_match('/\A[a-z_]{1,32}\z/u', $filename)) throw new \Exception('invalid filename');
    $code = file_get_contents("sample/{$filename}.php");
    $yay = file_get_contents("sample/{$filename}.yay.php");
}

$_SESSION['csrf'] = $_SESSION['csrf'] ?? base64_encode(random_bytes(64));

?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-1.12.3.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="ace_editor/ace.js" type="text/javascript" charset="utf-8"></script>
    <style type="text/css" media="screen">
        html, body {
          margin: 0;
          height: 100%;
          background: #eee;
          font-family: sans-serif;
        }

        .top {
            background: #ddd;
            padding: 8px;
        }

        #yay, #code, #converted_code, #output {
            height: 100%;
            font-size: 100%;
        }

        .yay-logo {
            display: inline-block;
            background: #000;
            font-size: 1.1em;
            color: #fff;
            padding: 6px;
            margin: -5px 0 -4px -5px;
        }

        h2 {
            font-size: 120%;
            margin: 0;
            padding: 2px;
            font-weight: normal;
            background: #eee;
        }

        .grid {
          min-height: 94%;
          display: flex;
          flex-wrap: wrap;
          flex-direction: row;
        }

        .grid > .box {
            opacity: 1;
            display: flex;
            flex-basis: calc(50% - 8px);
            justify-content: center;
            flex-direction: column;
            border: 1px inset #000;
            padding: 2px;
            box-shadow: 3px 3px 0px rgba(0,0,0,.5);
            background: #fff;
        }

        .grid > .box > div {
          display: flex;
          justify-content: center;
          flex-direction: row;
        }

        .box.hidden {
            display: none !important;
        }

        .blur {
            border-color: transparent !important;
            opacity: 0.8 !important;
            box-shadow: none !important;
            background: none !important;
        }

        /*.box { margin: 10px 0 10px 10px; }*/

    </style>
    <script>
        var editor;
        $(function () {

            yay = ace.edit("yay");
            yay.session.setMode("ace/mode/php");
            yay.session.setUseWorker(false);

            editor = ace.edit("code");
            editor.session.setMode("ace/mode/php");

            editor.commands.addCommand({
                name: 'run',
                bindKey: {win: 'Ctrl-Enter', mac: 'Command-Enter'},
                exec: function (editor) {
                    run();
                }
            });
            editor.focus();

            var output = ace.edit("output");

            var converted_code = ace.edit("converted_code");
            converted_code.setTheme("ace/theme/twilight");
            converted_code.session.setMode("ace/mode/php");

            $('.toggler').click(function(e){
                e.preventDefault();
                var target = $(this).data('target');
                $(target).toggleClass('hidden');
                yay.resize();
                editor.resize();
                converted_code.resize();
                output.resize();
            });

            $('.ace_content').click(function(){
                $('.box').addClass('blur');
                $(this).closest('.box').removeClass('blur');
            });
        });

        function run() {
            $("#yay_textarea").val(yay.getValue());
            $("#code_textarea").val(editor.getValue());
            $("#form").submit();
        }

        function changeSample(e) {
            var filename = $('option:selected', e.currentTarget).val();
            location.href = '/?filename=' + filename;
        }
    </script>
</head>
<body>

<form method="post" id="form" class="top">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">

    <h1 class="yay-logo">YAY!</h1>

    <select onchange="changeSample(event);">
        <option>Samples</option>
        <?php
        $filename_list = array_filter(array_map(function ($v) {
            preg_match('|sample/([a-z_]{1,}).php|u', $v, $_);
            if(isset($_[1])) return $_[1];
        }, glob('sample/*.php')));
        foreach ($filename_list as $filename) {
            echo "<option>" . htmlspecialchars($filename, ENT_QUOTES) . "</option>";
        }
        ?>
    </select>
    <button type="button" onclick="run()">run</button>

    <button class="toggler" data-target=".output,.converted">◒</button>
    <button class="toggler" data-target=".yay,.converted">◑</button>

    <textarea name="code" id="code_textarea" style="display:none"></textarea>
    <textarea name="yay" id="yay_textarea" style="display:none"></textarea>
</form>

<div class="grid">

    <div class="box yay blur">
        <h2>Macro Declarations</h2>
        <div id="yay"><?php echo htmlspecialchars($yay, ENT_QUOTES) ?></div>
    </div>

    <div class="box php blur">
        <h2>Code</h2>
        <div id="code"><?php echo htmlspecialchars($code, ENT_QUOTES) ?></div>
    </div>

    <div class="box converted blur <?php if (empty($output)) { ?>hidden<?php }?>">
        <h2>Expanded Code</h2>
        <div id="converted_code"><?php echo htmlspecialchars($converted_code, ENT_QUOTES) ?></div>
    </div>

    <div class="box output blur <?php if (empty($output)) { ?>hidden<?php }?>">
        <h2>Output</h2>
        <div id="output"><?php echo htmlspecialchars($output, ENT_QUOTES) ?></div>
    </div>
</div>

</body>
</html>
