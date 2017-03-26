--TEST--
Macro expansions should be comment insensitive by default
--FILE--
<?php

macro {
    x
    /**/
    (
    #
    )
    //
} >> {
    y()
}

x(/** */);

x() && y();

x (
    //
    );

x(foo); // no match

?>
--EXPECTF--
<?php

y();

y() && y();

y();

x(foo); // no match

?>
