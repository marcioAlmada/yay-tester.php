<?php

macro {
    defer { ···deferred };
} >> {
    $deferred_closure = (function($context){
        return function() use ($context) {
            extract($context);
            ···deferred
        };
    })(get_defined_vars());

    $deferred = new class($deferred_closure) {
        ··unsafe {
            private $deferred = null;
            function __construct(callable $deferred){ $this->deferred = $deferred; }
            function __destruct(){ ($this->deferred)(); }
        }
    };
}
