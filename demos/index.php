<?php
    error_reporting(E_ALL);
	require_once('../src/sandbox/bootstrap.php');

    use PHPSandbox\PHPSandbox;

	$sandbox = PHPSandbox::create();
    $sandbox->execute(function(){
        eval('evil code'); //this will generate an exception and fail
    });