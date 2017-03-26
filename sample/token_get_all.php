<?php

$source = <<<'SRC'
<?php

class Test extends TestCase {
    protected $property;
}
SRC;

$tokens =
    array_map(
        function($token){
            static $line = 0;

            if (is_array($token)) {
                $line = $token[2];
                return [token_name($token[0]), $token[1], $token[2]];
            }

            return [$token, $token, $line];
        },
        token_get_all($source)
    );

var_dump($tokens);
