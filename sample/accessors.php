<?php

<Accessors()>
class TimePeriod {
    function __construct()
    {
        $this->seconds = 0;
    }

    private $seconds {
        get() : int { return $this->seconds; } 
    }

    private $hours {
        get() : int { return $this->seconds / 3600; } 
        set(int $hours) { $this->seconds = $hours * 3600; } 
    }
}

//

$period = new TimePeriod;

$period->hours = 1;

var_dump($period->seconds);
var_dump($period->hours);
