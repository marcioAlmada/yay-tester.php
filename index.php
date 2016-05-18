<?php
declare(strict_types = 1);
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
$output = '';

if (isset($_POST['code'])) {
    $code = $_POST['code'];
    if ($_POST['csrf'] !== $_SESSION['csrf']) {
        die('csrf');
    }

    try {
        $converted_code = yay_parse($code);

    } catch (\Yay\YayParseError $e) {
        $converted_code = "YayParseError: " . (string) $e->getMessage();
    }
    catch (\Throwable $e) {
        $converted_code = (string) $e;
        $converted_code .= $e->getMessage();
    }

    try {
        ob_start();
        eval("?>" . $converted_code);
        $output = ob_get_clean();
        $converted_code = yay_pretty($converted_code);
    }
    catch (\Throwable $e) {
        $output = (string) $e;
    }

} else {
    $filename = (string)($_GET['filename'] ?? 'unless');
    if (!preg_match('/\A[a-z]{1,32}\z/u', $filename)) throw new \Exception('invalid filename');
    $code = file_get_contents("sample/{$filename}.php");
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
        }

        .top {
            background: #ddd;
            padding:15px;
        }

        #code, #converted_code, #output {
            height: 100%;
            font-size: 120%;
        }

        h2 {
            margin: 0;
            padding: 0;
        }

        .top {

        }

        .grid {
          min-height: 92%;
          display: flex;
          flex-wrap: wrap;
          flex-direction: row;
        }
        .grid > .box {
          display: flex;
          flex-basis: calc(50% - 15px);
          justify-content: center;
          flex-direction: column;
        }

        .grid > .box:last-child {
          display: flex;
          margin-top: 0px;
          flex-basis: calc(100% - 15px);
          justify-content: center;
          flex-direction: column;
        }

        .grid > .box > div {
          display: flex;
          justify-content: center;
          flex-direction: row;
        }

        .box { margin: 10px 0 10px 10px;}
        .box1 { background-color: red; }
        .box2 { background-color: orange; }
        .box3 { background-color: purple; }
        .box4 { background-color: grey; }

    </style>
    <script>
        var editor;
        $(function () {
            editor = ace.edit("code");
            editor.setTheme("ace/theme/twilight");
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
        });

        function run() {
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
    <select onchange="changeSample(event);">
        <option>Samples</option>
        <?php
        $filename_list = array_map(function ($v) {
            preg_match('|sample/([a-z]{1,32}).php|u', $v, $_);
            return $_[1];
        }, glob('sample/*.php'));
        foreach ($filename_list as $filename) {
            echo "<option>" . htmlspecialchars($filename, ENT_QUOTES) . "</option>";
        }
        ?>
    </select>
    <button type="button" onclick="run()">YAY!</button>
    <textarea name="code" id="code_textarea" style="display:none"></textarea>
</form>

<div class="grid">

    <div class="box">
        <h2>Code</h2>
        <div id="code"><?php echo htmlspecialchars($code, ENT_QUOTES) ?></div>
    </div>

    <div class="box">
        <h2>Output Code</h2>
        <div id="converted_code"><?php echo htmlspecialchars($converted_code, ENT_QUOTES) ?></div>
    </div>

    <div class="box">
        <h2>Output</h2>
        <div id="output"><?php echo htmlspecialchars($output, ENT_QUOTES) ?></div>
        </div>
    </div>

</body>
</html>
