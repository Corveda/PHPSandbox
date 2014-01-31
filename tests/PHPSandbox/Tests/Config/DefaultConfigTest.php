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

        /**
         * Test whether sandbox disallows eval keyword
         */
        public function testDisallowsEval(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ eval("echo 'Hello World!';"); });
        }

        /**
         * Test whether sandbox disallows exit keyword
         */
        public function testDisallowsExit(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ exit('Hello World!'); });
        }

        /**
         * Test whether sandbox disallows die keyword
         */
        public function testDisallowsDie(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ die('Hello World!'); });
        }

        /**
         * Test whether sandbox disallows include keyword
         */
        public function testDisallowsInclude(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ include('test.php'); });
        }

        /**
         * Test whether sandbox disallows require keyword
         */
        public function testDisallowsRequire(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ require('test.php'); });
        }

        /**
         * Test whether sandbox disallows include_once keyword
         */
        public function testDisallowsIncludeOnce(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ include_once('test.php'); });
        }

        /**
         * Test whether sandbox disallows require_once keyword
         */
        public function testDisallowsRequireOnce(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ require_once('test.php'); });
        }

        /**
         * Test whether sandbox disallows functions
         */
        public function testDisallowsFunctions(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ function test(){ return 'Hello World!'; } return test(); });
        }

        /**
         * Test whether sandbox autowhitelists trusted code
         */
        public function testAutowhitelistTrustedCode(){
            $this->sandbox->prepend(function(){
                function test2(){
                    return 'Hello World!';
                }
            });
            $this->assertEquals('Hello World!', $this->sandbox->execute(function(){ return test2(); }));
            $this->setUp(); //reset
        }

        /**
         * Test whether sandbox disallows closures
         */
        public function testDisallowsClosures(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ $test = function(){ return 'Hello World!'; }; return $test(); });
        }

        /**
         * Test whether sandbox allows variable creation
         */
        public function testAllowsVariableCreation(){
            $this->assertEquals('Hello World!', $this->sandbox->execute(function(){
                $a = 'Hello World!';
                return $a;
            }));
        }

        /**
         * Test whether sandbox allows static variable creation
         */
        public function testAllowsStaticVariableCreation(){
            $this->assertEquals('Hello World!', $this->sandbox->execute(function(){
                static $a = 'Hello World!';
                return $a;
            }));
        }

        /**
         * Test whether sandbox disallows globals
         */
        public function testDisallowsGlobals(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ global $test; return $test; });
        }

        /**
         * Test whether sandbox disallows classes
         */
        public function testDisallowsConstants(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ define('TEST', 'Hello World!'); return TEST; });
        }

        /**
         * Test whether sandbox disallows namespaces
         */
        public function testDisallowsNamespaces(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('namespace Foo;');
        }

        /**
         * Test whether sandbox disallows aliases (aka uses)
         */
        public function testDisallowsAliases(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('use Foo as Bar;');
        }

        /**
         * Test whether sandbox disallows classes
         */
        public function testDisallowsClasses(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('class Foo {}');
        }

        /**
         * Test whether sandbox disallows interfaces
         */
        public function testDisallowsInterfaces(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('interface Foo {}');
        }

        /**
         * Test whether sandbox disallows traits
         */
        public function testDisallowsTraits(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('trait Foo {}');
        }

        /**
         * Test whether sandbox disallows escaping to HTML
         */
        public function testDisallowsEscaping(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ ?>Hello World!<?php });
        }

        /**
         * Test whether sandbox disallows casting
         */
        public function testDisallowsCasting(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ $a = '1'; $b = (bool)$a; });
        }

        /**
         * Test whether sandbox disallows error suppressing
         */
        public function testDisallowsErrorSuppressing(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('$value = @$cache[$key];');
        }

        /**
         * Test whether sandbox allows references
         */
        public function testAllowsReferences(){
            $this->assertEquals('Hello World!', $this->sandbox->execute(function(){
                $a = 'Hello World!';
                $b =& $a;
                return $b;
            }));
        }

        /**
         * Test whether sandbox disallows backtick execution
         */
        public function testDisallowsBackticks(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return `ping google.com`; });
        }

        /**
         * Test whether sandbox disallows halting
         */
        public function testDisallowsHalting(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute('__halt_compiler();');
        }

        /**
         * Test whether sandbox disallows non-whitelisted functions
         */
        public function testDisallowsNonwhitelistedFunction(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return mt_rand(); });
        }

        /**
         * Test whether sandbox disallows non-whitelisted class
         */
        public function testDisallowsNonwhitelistedClass(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return \DateTime::createFromFormat('y', 'now'); });
        }

        /**
         * Test whether sandbox disallows non-whitelisted class type
         */
        public function testDisallowsNonwhitelistedType(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return new \stdClass; });
        }

        /**
         * Test whether sandbox custom function validation succeeds
         */
        public function testCustomFunctionValidationSuccess(){
            $this->expectOutputString('success');
            $this->sandbox->set_func_validator(function($name, $sandbox){
                return $name == 'phpsandbox\tests\config\test';
            });
            function test(){
                echo 'success';
            }
            $this->sandbox->execute(function(){ \PHPSandbox\Tests\Config\test(); });
        }

        /**
         * Test whether sandbox custom function validation succeeds
         */
        public function testCustomFunctionValidationFailure(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->set_func_validator(function($name, $sandbox){
                return $name == 'test';
            });
            $this->sandbox->execute(function(){ test2(); });
        }

        /**
         * Test whether sandbox custom error handler intercepts exceptions
         */
        public function testCustomErrorHandler(){
            $this->setExpectedException('PHPSandbox\Error');
            $this->sandbox->set_error_handler(function($error, $sandbox){
                throw $error;
            });
            $this->sandbox->execute(function(){ test2(); });
        }
    }