<?php

use PHPUnit\Framework\Exception;

use RuntimeException as OtherException;

$exception = new Exception('ok');
echo $exception->getMessage();

$exception = new OtherException('ok');
echo $exception->getMessage();
