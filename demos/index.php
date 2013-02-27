<?php
    error_reporting(E_ALL);
    require_once('../vendor/autoload.php');

    function test($string){
        return 'Hello ' . $string;
    }

    $sandbox = new PHPSandbox\PHPSandbox;
    $sandbox->whitelist_func('test');
    $result = $sandbox->execute(function(){
        return test('world');
    });

    var_dump($result);  //Hello world