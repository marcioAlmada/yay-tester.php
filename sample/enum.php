<?php

enum ProcessState {
    Created,
    Ready,
    Running,
    Blocked,
    Terminated
}


function set_process_state(int $pid, ProcessState $state) {
    echo "Process {$pid} was set to state {$state}", PHP_EOL;
}

set_process_state(500, ProcessState->Ready);

$state = ProcessState->Running;

switch($state) {
    case ProcessState->Created:
        echo sprintf("Process is %s", ProcessState->Created);
        break;
    case ProcessState->Ready:
        echo sprintf("Process is %s", ProcessState->Ready);
        break;
    case ProcessState->Running:
        echo sprintf("Process is %s", ProcessState->Running);
        break;
    case ProcessState->Blocked:
        echo sprintf("Process is %s", ProcessState->Blocked);
        break;
    case ProcessState->Terminated:
        echo sprintf("Process is %s", ProcessState->Terminated);
        break;
    default:
        throw new \LogicException('Invalid process state!');
}
