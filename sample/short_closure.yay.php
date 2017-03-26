<?php

macro {
    ( ···args ) => { ···body }
} >> {
    (function ($context){
        return function (···args) use($context) {
            extract($context);
            return ···body;
        };
    })(get_defined_vars())
}
