<?php
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";

$json='{"PPID":"10","PID":"2","Name":"2.1.2"}';

$planItem=new PlanItem(
    '',
    $json
);

print_r($planItem->Save());
