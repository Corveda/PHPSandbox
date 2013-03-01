<?php
    namespace PHPSandbox\Tests\Config;

    use \PHPSandbox\PHPSandbox;

    class DefaultConfigTest extends \PHPUnit_Framework_TestCase {
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

        /**
         * Test whether sandbox returns expected value
         */
        public function testHelloWorldReturned(){
            $this->assertEquals('Hello World!', $this->sandbox->execute(function(){ return 'Hello World!'; }));
        }

        /**
         * Test whether sandbox echoes expected value
         */
        public function testHelloWorldEchoed(){
            $this->expectOutputString('Hello World!');
            $this->sandbox->execute(function(){ echo 'Hello World!'; });
        }
    }