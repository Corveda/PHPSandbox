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
        } else if(is_array($value) && count($value)){
            //save current array pointer
            $current_key = key($value);
            foreach($value as $key => &$_value) {
                $value[$key] = wrap($_value, $sandbox);
            }
            //rewind array pointer
            reset($value);
            //advance array to previous array key
            while(key($value) !== $current_key){
                next($value);
            }
            return $value;
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
        } else if(is_array($value) && count($value)){
            //save current array pointer
            $current_key = key($value);
            foreach($value as $key => &$_value) {
                $value[$key] = wrap($_value, $sandbox);
            }
            //rewind array pointer
            reset($value);
            //advance array to saved array pointer
            while(key($value) !== $current_key){
                next($value);
            }
            return $value;
        } else if(is_string($value) && is_callable($value)){
            return new SandboxedString($value, $sandbox);
        }
        return $value;
    }