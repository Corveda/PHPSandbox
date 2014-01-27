<?php
    /** Error class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;
    /**
     * Error class for PHP Sandboxes.
     *
     * This class extends Exception to allow for catching PHPSandbox-specific exceptions.
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <fieryprophet@yahoo.com>
     * @version 1.3
     */
    class Error extends \Exception {
        /* START ERROR CODES */
        /* PARSER ERRORS           (1-100) */
        const PARSER_ERROR =        1;
        /* END ERROR CODES */
        /**
         * @var string      The raw message of the Error
         */
        protected $raw_message = '';
        /** Constructs the Error
         * @param string $message       The message to pass to the Error
         * @param int $code             The error code to pass to the Error
         * @param \Exception $previous  The previous exception to pass to the Error
         */
        public function __construct($message = '', $code = 0, \Exception $previous = null){
            $this->raw_message = $message;
            parent::__construct('', $code, $previous);
        }
        /** Returns raw message of the Error
         * @return  string  The raw message of the error to return
         */
        public function getRawMessage(){
            return $this->raw_message;
        }
    }