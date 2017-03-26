<?php // enum implementation

macro ·unsafe {
    enum T_STRING·name · {
        ·ls
        (
            ·label()·field
            ,
            ·token(',')
        )
        ·fields
    }
} >> {
    class T_STRING·name implements Enum {
        private static $store;

        private function __construct() {}

        static function init()
        {
            if(! self::$store) {
                self::$store = new \stdclass;
                ·fields ··· {
                    self::$store->·field = new class extends T_STRING·name {
                        function __toString() {
                            return ··stringify(·field);
                        }
                    };
                }
            }
        }

        static function __callStatic(string $field, array $args) : self {
            if (isset(self::$store->$field)) return self::$store->$field;

            throw new \EnumError('Undefined enum field ' . __CLASS__ . "->{$field}.");
        }
    }

    T_STRING·name::init();
}

macro {
    // sequence that matches the enum field access syntax:
    ·ns()·class // matches a namespace
    ·midrule(function($ts) {
        $index = $ts->index();
        try {
            $ts->previous();
            $token = $ts->previous();

            if ($token->is(T_OBJECT_OPERATOR))
                return new \Yay\Error(null, null, $ts->last());

            return new \Yay\Ast;
        }
        finally {
            $ts->jump($index);
        }
    })·_
    -> // matches T_OBJECT_OPERATOR used for static access
    ·not(·token(T_CLASS))·_ // avoids matching ::class resolution syntax
    ·label()·field // matches the enum field name
    ·not(·token('('))·_ // avoids matching static method calls
} >> {
    (·class::class)::{··stringify(·field)}()
}


interface Enum {}
class EnumError extends TypeError {}
