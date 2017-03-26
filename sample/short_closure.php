<?php

$offset = -4;
$multiplier = 42;
//
$range = array_map(function($i) use ($multiplier, $offset) {
    return $i * $multiplier - $offset;
}, range(0, 10));

$range = array_map(($i) => { $i * $multiplier - $offset }, range(0, 10));
//
var_dump($range);
