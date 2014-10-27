<?php
    namespace PHPSandbox;

    /** Wrap output value in SandboxString
     *
     * @param   mixed                   $value      Value to wrap
     * @param   PHPSandbox              $sandbox    Sandbox instance of calling code
     *
     * @return  mixed|SandboxedString   Returns the wrapped value
     */
    function wrap($value, $sandbox){
        if(!($value instanceof SandboxedString) && is_object($value) && method_exists($value, '__toString')){
            $strval = $value->__toString();
            return is_callable($strval) ? new SandboxedString($strval, $sandbox) : $value;
        } else if(is_string($value) && is_callable($value)){
            return new SandboxedString($value, $sandbox);
        }
        return $value;
    }

    /** Wrap output value in SandboxString by reference
     *
     * @param   mixed                   $value      Value to wrap
     * @param   PHPSandbox              $sandbox    Sandbox instance of calling code
     *
     * @return  mixed|SandboxedString   Returns the wrapped value
     */
    function &wrapByRef(&$value, $sandbox){
        if(!($value instanceof SandboxedString) && is_object($value) && method_exists($value, '__toString')){
            $strval = $value->__toString();
            return is_callable($strval) ? new SandboxedString($strval, $sandbox) : $value;
        } else if(is_string($value) && is_callable($value)){
            return new SandboxedString($value, $sandbox);
        }
        return $value;
    }