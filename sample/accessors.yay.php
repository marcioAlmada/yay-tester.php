<?php

macro ·unsafe {
    < Accessors ( ) >
    class T_STRING·class_name
        ·optional(·chain(extends, ·ns()·extended_class))·extends
        ·optional(·chain(implements, ·ls(·ns()·implemented_class, ·token(','))·implements_list))·implements
    {
        ···body
    }
} >> {
    class T_STRING·class_name
    ·extends ?··· { extends ·extended_class }
    ·implements ?··· { implements ·implements_list ···(, ){ ·implemented_class } }
    {
        use \AccessorsTrait;
        ···body
    }
}

macro {
    private T_VARIABLE·variable { ·
        ·repeat(
            ·either(
                ·chain(
                    get,
                    ·token('('),
                    ·optional(·layer()·getter_args),
                    ·token(')'),
                    ·optional(·chain(·token(':'), ·ns()·_))·getter_return_type,
                    ·between(·token('{'), ·layer(), ·token('}'))·getter_body
                )·getter
                ,
                ·chain(
                    set,
                    ·token('('),
                    ·optional(·layer()·setter_args),
                    ·token(')'),
                    ·optional(·chain(·token(':'), ·ns()·_))·setter_return_type,
                    ·between(·token('{'), ·layer(), ·token('}'))·setter_body
                )·setter
            )
        )·accessors
    }
    ·optional(·token(';'))
} >> {
    private T_VARIABLE·variable;

    ·accessors ··· {
        ·setter ?··· {
            private function ··concat(accessor_set_ ··unvar(T_VARIABLE·variable))(·setter_args) ·setter_return_type {
                ·setter_body
            }

        }

        ·getter ?··· {
            private function ··concat(accessor_get_ ··unvar(T_VARIABLE·variable))(·getter_args) ·getter_return_type {
                ·getter_body
            }
        }
    }
}

class PropertyAccessorException extends \Exception {};

trait AccessorsTrait
{
    final function __get($property)
    {
        $getter = "accessor_get_{$property}";
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new \PropertyAccessorException(sprintf("Undefined getter for %s->%s.", __CLASS__, $property));
    }

    final function __set($property, $value)
    {
        $setter = "accessor_set_{$property}";
        if (method_exists($this, $setter)) {
            return $this->{$setter}($value);
        }

        throw new \PropertyAccessorException(sprintf("Undefined setter for %s->%s.", __CLASS__, $property));
    }
}
