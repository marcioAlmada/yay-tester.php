<?php

function app($input) {
    $file = fopen('./view.md', "r");
    defer { fclose($file); echo "Closing {$file}."; };

    echo stream_get_contents($file), PHP_EOL;
}

app("request");
