<?php
    error_reporting(E_ALL);
    require_once('../vendor/autoload.php');

    use PHPSandbox\PHPSandbox;

	$sandbox = PHPSandbox::create();
    $result = $sandbox->execute(function(){
        $a = 1;
        $b = 2;
        $c = $a + $b;
        return $c;
    });

    print_r($result);

    $sandbox = PHPSandbox::create();
    $sandbox->execute(function(){
        eval('evil code'); //this will generate an exception and fail
    });