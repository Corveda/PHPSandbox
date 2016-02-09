<?php
    use \PHPSandbox\PHPSandbox;

    error_reporting(E_ALL);

    class DefaultConfigTest extends PHPUnit_Framework_TestCase {
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

        public function testSettingAndUnsettingOptions(){
            $this->sandbox->setOption('error_level', 1);
            $this->assertEquals(1, $this->sandbox->getOption('error_level'));
            $this->sandbox->setOption('error_level', null);
            $this->assertEquals(null, $this->sandbox->getOption('error_level'));
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
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ eval("echo 'Hello World!';"); });
        }

        /**
         * Test whether sandbox disallows exit keyword
         */
        public function testDisallowsExit(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ exit('Hello World!'); });
        }

        /**
         * Test whether sandbox disallows die keyword
         */
        public function testDisallowsDie(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ die('Hello World!'); });
        }

        /**
         * Test whether sandbox disallows include keyword
         */
        public function testDisallowsInclude(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ include('test.php'); });
        }

        /**
         * Test whether sandbox disallows require keyword
         */
        public function testDisallowsRequire(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ require('test.php'); });
        }

        /**
         * Test whether sandbox disallows include_once keyword
         */
        public function testDisallowsIncludeOnce(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ include_once('test.php'); });
        }

        /**
         * Test whether sandbox disallows require_once keyword
         */
        public function testDisallowsRequireOnce(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ require_once('test.php'); });
        }

        /**
         * Test whether sandbox disallows functions
         */
        public function testDisallowsFunctions(){
            $this->expectException('PHPSandbox\Error');
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
            $this->expectException('PHPSandbox\Error');
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
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ global $test; return $test; });
        }

        /**
         * Test whether sandbox disallows classes
         */
        public function testDisallowsConstants(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ define('TEST', 'Hello World!'); return TEST; });
        }

        /**
         * Test whether sandbox disallows namespaces
         */
        public function testDisallowsNamespaces(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('namespace Foo;');
        }

        /**
         * Test whether sandbox disallows aliases (aka uses)
         */
        public function testDisallowsAliases(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('use Foo as Bar;');
        }

        /**
         * Test whether sandbox disallows classes
         */
        public function testDisallowsClasses(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('class Foo {}');
        }

        /**
         * Test whether sandbox disallows interfaces
         */
        public function testDisallowsInterfaces(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('interface Foo {}');
        }

        /**
         * Test whether sandbox disallows traits
         */
        public function testDisallowsTraits(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('trait Foo {}');
        }

        /**
         * Test whether sandbox disallows escaping to HTML
         */
        public function testDisallowsEscaping(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ ?>Hello World!<?php });
        }

        /**
         * Test whether sandbox disallows casting
         */
        public function testDisallowsCasting(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return (bool)'1'; });
        }

        /**
         * Test whether sandbox disallows error suppressing
         */
        public function testDisallowsErrorSuppressing(){
            $this->expectException('PHPSandbox\Error');
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
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return `ping google.com`; });
        }

        /**
         * Test whether sandbox disallows halting
         */
        public function testDisallowsHalting(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute('__halt_compiler();');
        }

        /**
         * Test whether sandbox disallows non-whitelisted functions
         */
        public function testDisallowsNonwhitelistedFunction(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return mt_rand(); });
        }

        /**
         * Test whether sandbox disallows non-whitelisted class
         */
        public function testDisallowsNonwhitelistedClass(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return \DateTime::createFromFormat('y', 'now'); });
        }

        /**
         * Test whether sandbox disallows non-whitelisted class type
         */
        public function testDisallowsNonwhitelistedType(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return new \stdClass; });
        }

        /**
         * Test whether sandbox custom function validation succeeds
         */
        public function testCustomFunctionValidationSuccess(){
            $this->expectOutputString('success');
            $this->sandbox->setFuncValidator(function($name){
                return $name == 'test';
            });
            function test(){
                echo 'success';
            }
            $this->sandbox->execute(function(){ test(); });
        }

        /**
         * Test whether sandbox custom function validation succeeds
         */
        public function testCustomFunctionValidationFailure(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->setFuncValidator(function($name){
                return $name == 'test';
            });
            $this->sandbox->execute(function(){ test2(); });
        }

        /**
         * Test whether sandbox custom error handler intercepts errors
         */
        public function testCustomErrorHandler(){
            $this->expectException('Exception');
            $this->sandbox->setErrorHandler(function($errno, $errstr){
                throw new \Exception($errstr);
            });
            $this->sandbox->execute(function(){ $a[1]; });
        }

        /**
         * Test whether sandbox custom exception handler intercepts exceptions
         */
        public function testCustomExceptionHandler(){
            $this->expectException('Exception');
            $this->sandbox->whitelistType('Exception');
            $this->sandbox->setExceptionHandler(function($exception){
                throw $exception;
            });
            $this->sandbox->execute(function(){ throw new \Exception; });
        }

        /**
         * Test whether sandbox converts errors to exceptions
         */
        public function testConvertErrors(){
            $this->expectException('ErrorException');
            $this->sandbox->convert_errors = true;
            $this->sandbox->setExceptionHandler(function($error){
                throw $error;
            });
            $this->sandbox->execute(function(){ $a[1]; });
        }

        /**
         * Test whether sandbox custom validation error handler intercepts validation Errors
         */
        public function testCustomValidationErrorHandler(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->setValidationErrorHandler(function($error){
                throw $error;
            });
            $this->sandbox->execute(function(){ test2(); });
        }

        /**
         * Test whether sandbox disallows violating callbacks
         */
        public function testCallbackViolations(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ array_filter(array("1"), "var_dump"); });
        }

        /**
         * Test whether sandbox disallows violating callbacks even with manipulated sandboxed strings
         */
        public function testCallbackViolationsWithStringManipulation(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ $x = substr("var_dump2", 0, -1); array_filter(array("1"), $x); });
        }

        /**
         * Test whether sandboxed strings do not cause conflicts with intval
         */
        public function testSandboxedStringsSatisfyIntval(){
            $this->sandbox->whitelistFunc('intval');
            $this->assertEquals(1, $this->sandbox->execute(function(){ return intval("1"); }));
        }

        /**
         * Test whether sandboxed strings do not cause conflicts with is_string, is_object, or is_scalar
         */
        public function testSandboxedStringsMimicStrings(){
            $this->sandbox->whitelistFunc(array(
                'is_string',
                'is_object',
                'is_scalar'
            ));
            $this->assertEquals(true, $this->sandbox->execute(function(){ return is_string("system"); }));
            $this->assertEquals(false, $this->sandbox->execute(function(){ return is_object("system"); }));
            $this->assertEquals(true, $this->sandbox->execute(function(){ return is_scalar("system"); }));
        }

        public function testStaticTypeOverwriting(){
            $class = 'B' . md5(time());
            $this->sandbox->defineClass('A', $class);
            $this->sandbox->allow_classes = true;
            $this->sandbox->allow_functions = true;
            $this->assertEquals("Yes", $this->sandbox->execute('<?php
                class ' . $class . ' {
                    public $value = "Yes";
                }

                function test' . $class . '(A $var){
                    return $var->value;
                }

                return test' . $class . '(new ' . $class . ');
            ?>'));
        }

        /**
         * Test whether sandbox disallows violating callbacks
         */
        public function testMagicConstants(){
            $this->expectException('PHPSandbox\Error');
            $this->sandbox->execute(function(){ return __DIR__;});
        }

        /**
         * Test whether sandbox disallows violating callbacks
         */
        public function testWhitelistMagicConstants(){
            $this->sandbox->whitelistMagicConst('DIR');
            $this->assertEquals(str_replace('tests', 'src', __DIR__), $this->sandbox->execute(function(){ return __DIR__; }));
        }
    }