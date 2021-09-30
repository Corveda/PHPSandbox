<?php
    use PHPSandbox\PHPSandbox,
        PHPUnit\Framework\TestCase;

    error_reporting(E_ALL);

    class CodeSamplesTest extends TestCase {
        /**
         * @var PHPSandbox
         */
        protected PHPSandbox $sandbox;

        /**
         * Sets up the test
         */
        public function setUp() : void {
            $this->sandbox = new PHPSandbox;
        }

        public function testMultipleIncludes() : void {
            $path = __DIR__ . '/samples/multiple-includes/index.php';
            $this->sandbox->validate_magic_constants = false;
            $this->sandbox->allow_classes = true;
            $this->sandbox->validate_classes = false;
            $this->sandbox->allow_includes = true;
            $result = $this->sandbox->execute(file_get_contents($path), false, $path);
            $this->assertEquals(['ok', null], $result);
        }

        public function testPropertiesWithMagickConstants() : void {
            $path = __DIR__ . '/samples/properties_with_magic_constants/index.php';
            $this->sandbox->validate_magic_constants = false;
            $this->sandbox->allow_classes = true;
            $this->sandbox->validate_classes = false;
            $this->sandbox->allow_includes = true;
            $this->sandbox->capture_output = true;
            $result = $this->sandbox->execute(file_get_contents($path), false, $path);
            $this->assertEquals($path, $result);
        }
    }
