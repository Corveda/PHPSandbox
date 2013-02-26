<?php
	error_reporting(E_ALL);
	require_once('inc/sandbox/bootstrap.php');

    use PHPSandbox\PHPSandbox;

    //class TESTL {}

    echo '<pre>';
	$sandbox = PHPSandbox::create();
    $sandbox->execute(function(){
        eval('evil code'); //this will generate an exception and fail
    });
    echo '</pre>';

    foreach($sandbox as $key => $value){
        if(is_array($value) || is_string($value)){
            echo '<pre><strong>' . htmlentities($key) . "</strong>: \r\n" . (is_array($value) ? print_r($value, true) : htmlentities($value)) . '</pre>';
        }
    }