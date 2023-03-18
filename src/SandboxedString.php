<?php
    /** Sandboxed string class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    use ArrayAccess,
        ArrayIterator,
        IteratorAggregate,
        Throwable;

    /**
     * Sandboxed string class for PHP Sandboxes.
     *
     * This class wraps sandboxed strings to intercept and check callable invocations
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 3.0
     */
    class SandboxedString implements ArrayAccess, IteratorAggregate {
        /** Value of the SandboxedString
         * @var string
         */
        private string $value;
        /** PHPSandbox instance invoked by the SandboxedString
         * @var PHPSandbox
         */
        private PHPSandbox $sandbox;
        /** Constructs the SandboxedString
         * @param   string      $value          Original string value
         * @param   PHPSandbox  $sandbox        The current sandbox instance to test against
         */
        public function __construct(string $value, PHPSandbox $sandbox){
            $this->value = $value;
            $this->sandbox = $sandbox;
        }
        /** Returns the original string value
         * @return string
         */
        public function __toString() : string {
            return $this->value;
        }
        /** Checks the string value against the sandbox function whitelists and blacklists for callback violations
         *
         * @throws Throwable
         *
         * @return mixed|null
         */
        public function __invoke() : string {
            if($this->sandbox->checkFunc($this->value)){
                $name = strtolower($this->value);
                if((in_array($name, PHPSandbox::$defined_funcs) && $this->sandbox->overwrite_defined_funcs)
                    || (in_array($name, PHPSandbox::$sandboxed_string_funcs) && $this->sandbox->overwrite_sandboxed_string_funcs)
                    || (in_array($name, PHPSandbox::$arg_funcs) && $this->sandbox->overwrite_func_get_args)){
                    return call_user_func_array([$this->sandbox, '_' . $this->value], func_get_args());
                }
                return call_user_func_array($name, func_get_args());
            }
            return '';
        }
        /** Set string value at specified offset
         * @param   mixed       $offset            Offset to set value
         * @param   mixed       $value             Value to set
         */
        public function offsetSet($offset, $value) : void {
            if($offset === null){
                $this->value .= $value;
            } else {
                $this->value[$offset] = $value;
            }
        }
        /** Get string value at specified offset
         * @param   mixed       $offset            Offset to get value
         *
         * @return  string      Value to return
         */
        public function offsetGet($offset) : string {
            return $this->value[$offset];
        }
        /** Check if specified offset exists in string value
         * @param   mixed       $offset            Offset to check
         *
         * @return  bool        Return true if offset exists, false otherwise
         */
        public function offsetExists($offset) : bool {
            return isset($this->value[$offset]);
        }
        /** Unset string value at specified offset
         * @param   mixed       $offset            Offset to unset
         */
        public function offsetUnset($offset) : void {
            unset($this->value[$offset]);
        }
        /** Return iterator for string value
         * @return  ArrayIterator      Array iterator to return
         */
        public function getIterator() : ArrayIterator {
            return new ArrayIterator(str_split($this->value));
        }
    }