<?php
    require_once(dirname(__FILE__) . '/parser/bootstrap.php');
    require_once(dirname(__FILE__) . '/functionparser/bootstrap.php');
    require_once('PHPSandbox/Exception.php');
    require_once('PHPSandbox/PHPSandbox.php');
    require_once('PHPSandbox/WhitelistVisitor.php');
    require_once('PHPSandbox/SandboxWhitelistVisitor.php');
    require_once('PHPSandbox/ValidatorVisitor.php');