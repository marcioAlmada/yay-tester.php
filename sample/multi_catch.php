<?php // union catch

$logger = new EchoLogger;

try {
    throw new SomeDomainException("PHP Experience Rules!");
}
catch(IOException $e) {
    $logger->critical($e->getMessage());
}
catch(SQLException $e) {
    $logger->critical($e->getMessage());
}
catch(SomeDomainException $e) {
    $logger->critical($e->getMessage());
}
finally {
    // close some resources
}


// declarations


class EchoLogger
{
    function critical(string $message)
    {
        echo $message, PHP_EOL;
    }
}

class IOException extends \Exception
{
}

class SQLException extends \Exception
{
}

class SomeDomainException extends \Exception
{
}
