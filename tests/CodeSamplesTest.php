<?php
    use \PHPSandbox\PHPSandbox;

    error_reporting(E_ALL);

    class CodeSamplesTest extends PHPUnit_Framework_TestCase {
        /**
         * @var PHPSandbox
         */
        protected $sandbox;

        /**
         * Sets up the test
         */
        public function setUp(){
            $this->sandbox = new PHPSandbox;
        }

        public function testMultipleIncludes(){
            $path = __DIR__ . '/samples/multiple-includes/index.php';
            $this->sandbox->validate_magic_constants = false;
            $this->sandbox->allow_classes = true;
            $this->sandbox->validate_classes = false;
            $this->sandbox->allow_includes = true;
            $this->sandbox->execute(file_get_contents($path), false, $path);
        }
    }