<?php

use PHPUnit\Framework\Exception;

use PHPUnit\Runner\Exception as OtherException;

$exception = new Exception('ok');
echo $exception->getMessage();

$exception = new OtherException('ok');
echo $exception->getMessage();
