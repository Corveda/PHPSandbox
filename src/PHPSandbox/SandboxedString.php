<?php
    /** Sandboxed string class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;
    /**
     * Sandboxed string class for PHP Sandboxes.
     *
     * This class wraps sandboxed strings to intercept and check callable invocations
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <fieryprophet@yahoo.com>
     * @version 1.3.2
     */
    class SandboxedString {
        /**
         * @var string
         */
        private $value;
        /**
         * @var PHPSandbox
         */
        private $sandbox;
        /** Constructs the SandboxedString
         * @param   string      $value      Original string value
         * @param   PHPSandbox  $sandbox    The current sandbox instance to test against
         */
        public function __construct($value, PHPSandbox $sandbox){
            $this->value = $value;
            $this->sandbox = $sandbox;
        }
        /** Returns the original string value
         * @return string
         */
        public function __toString(){
            return strval($this->value);
        }
        /** Checks the string value against the sandbox function whitelists and blacklists for callback violations
         * @return mixed|null
         */
        public function __invoke(){
            if($this->sandbox->check_func($this->value)){
                $name = strtolower($this->value);
                if((in_array($name, PHPSandbox::$defined_funcs) && $this->sandbox->overwrite_defined_funcs)
                    || (in_array($name, PHPSandbox::$var_funcs) && $this->sandbox->overwrite_var_funcs)
                    || (in_array($name, PHPSandbox::$arg_funcs) && $this->sandbox->overwrite_func_get_args)){
                    return call_user_func_array(array($this->sandbox, '_' . $this->value), func_get_args());
                }
                return call_user_func_array($name, func_get_args());
            }
            return null;
        }
    }