<?php
    require_once('../vendor/autoload.php');

    function evil(){
        echo 'This function should not execute.';
    }

    $sandbox = new PHPSandbox\PHPSandbox;
    $result = $sandbox->execute(function(){
        evil();     //this will throw an exception
    });