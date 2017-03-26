<?php

macro {
    swap ( T_VARIABLE·a, T_VARIABLE·b )
} >> {
    $temp = T_VARIABLE·a;
    T_VARIABLE·a = T_VARIABLE·b;
    T_VARIABLE·b = $temp;
}
