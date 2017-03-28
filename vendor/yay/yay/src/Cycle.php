<?php declare(strict_types=1);

namespace Yay;

use
    RuntimeException
;

class Cycle {

    protected
        $id = 0
    ;

    function next() /*: void */ { $this->id++; }

    /**
     * Not security related, just making scope id not humanely predictable.
     */
    function id() : string { return ((string) $this->id); }
}
