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
     * @version 1.3.9
     */
    class Error extends \Exception {
        /* START ERROR CODES */
        /* MISC ERRORS                      (1-99)    */
        const PARSER_ERROR              =       1;
        const ESCAPE_ERROR              =       2;
        const HALT_ERROR                =       3;
        const CAST_ERROR                =       4;
        const CLOSURE_ERROR             =       5;
        const BYREF_ERROR               =       6;
        const GENERATOR_ERROR           =       7;
        const GLOBALS_ERROR             =       8;
        const DYNAMIC_VAR_ERROR         =       9;
        const STATIC_VAR_ERROR          =       10;
        const ERROR_SUPPRESS_ERROR      =       11;
        const BACKTICKS_ERROR           =       12;
        const IMPORT_ERROR              =       13;

        const DYNAMIC_STATIC_VAR_ERROR  =       20;
        const DYNAMIC_CONST_ERROR       =       21;
        const DYNAMIC_CLASS_ERROR       =       22;
        const SANDBOX_ACCESS_ERROR      =       30;
        const GLOBAL_CONST_ERROR        =       31;
        const CREATE_OBJECT_ERROR       =       32;
        /* VALIDATION ERRORS                (100-199) */
        const VALID_FUNC_ERROR          =       100;
        const VALID_KEYWORD_ERROR       =       101;
        const VALID_CONST_ERROR         =       102;
        const VALID_VAR_ERROR           =       103;
        const VALID_GLOBAL_ERROR        =       104;
        const VALID_SUPERGLOBAL_ERROR   =       105;
        const VALID_MAGIC_CONST_ERROR   =       106;
        const VALID_CLASS_ERROR         =       107;
        const VALID_TYPE_ERROR          =       108;
        const VALID_INTERFACE_ERROR     =       109;
        const VALID_TRAIT_ERROR         =       110;
        const VALID_NAMESPACE_ERROR     =       111;
        const VALID_ALIAS_ERROR         =       112;
        const VALID_OPERATOR_ERROR      =       113;
        const VALID_PRIMITIVE_ERROR     =       114;
        /* DEFINITION ERRORS                (200-299) */
        const DEFINE_FUNC_ERROR         =       200;
        const DEFINE_KEYWORD_ERROR      =       201;
        const DEFINE_CONST_ERROR        =       202;
        const DEFINE_VAR_ERROR          =       203;
        const DEFINE_GLOBAL_ERROR       =       204;
        const DEFINE_SUPERGLOBAL_ERROR  =       205;
        const DEFINE_MAGIC_CONST_ERROR  =       206;
        const DEFINE_CLASS_ERROR        =       207;
        const DEFINE_TYPE_ERROR         =       208;
        const DEFINE_INTERFACE_ERROR    =       209;
        const DEFINE_TRAIT_ERROR        =       210;
        const DEFINE_NAMESPACE_ERROR    =       211;
        const DEFINE_ALIAS_ERROR        =       212;
        const DEFINE_OPERATOR_ERROR     =       213;
        const DEFINE_PRIMITIVE_ERROR    =       214;
        /* WHITELIST ERRORS                     (300-399) */
        const WHITELIST_FUNC_ERROR          =       300;
        const WHITELIST_KEYWORD_ERROR       =       301;
        const WHITELIST_CONST_ERROR         =       302;
        const WHITELIST_VAR_ERROR           =       303;
        const WHITELIST_GLOBAL_ERROR        =       304;
        const WHITELIST_SUPERGLOBAL_ERROR   =       305;
        const WHITELIST_MAGIC_CONST_ERROR   =       306;
        const WHITELIST_CLASS_ERROR         =       307;
        const WHITELIST_TYPE_ERROR          =       308;
        const WHITELIST_INTERFACE_ERROR     =       309;
        const WHITELIST_TRAIT_ERROR         =       310;
        const WHITELIST_NAMESPACE_ERROR     =       311;
        const WHITELIST_ALIAS_ERROR         =       312;
        const WHITELIST_OPERATOR_ERROR      =       313;
        const WHITELIST_PRIMITIVE_ERROR     =       314;
        /* BLACKLIST ERRORS                     (400-499) */
        const BLACKLIST_FUNC_ERROR          =       400;
        const BLACKLIST_KEYWORD_ERROR       =       401;
        const BLACKLIST_CONST_ERROR         =       402;
        const BLACKLIST_VAR_ERROR           =       403;
        const BLACKLIST_GLOBAL_ERROR        =       404;
        const BLACKLIST_SUPERGLOBAL_ERROR   =       405;
        const BLACKLIST_MAGIC_CONST_ERROR   =       406;
        const BLACKLIST_CLASS_ERROR         =       407;
        const BLACKLIST_TYPE_ERROR          =       408;
        const BLACKLIST_INTERFACE_ERROR     =       409;
        const BLACKLIST_TRAIT_ERROR         =       410;
        const BLACKLIST_NAMESPACE_ERROR     =       411;
        const BLACKLIST_ALIAS_ERROR         =       412;
        const BLACKLIST_OPERATOR_ERROR      =       413;
        const BLACKLIST_PRIMITIVE_ERROR     =       414;
        /* END ERROR CODES */
        /**
         * @var \PHPParser_Node|null      The node of the Error
         */
        protected $node;
        /**
         * @var mixed      The data of the Error
         */
        protected $data;
        /** Constructs the Error
         * @param string                $message        The message to pass to the Error
         * @param int                   $code           The error code to pass to the Error
         * @param \PHPParser_node       $node           The parser node to pass to the Error
         * @param mixed                 $data           The error data to pass to the Error
         * @param \Exception            $previous       The previous exception to pass to the Error
         */
        public function __construct($message = '', $code = 0, \PHPParser_Node $node = null, $data = null, \Exception $previous = null){
            $this->node = $node;
            $this->data = $data;
            parent::__construct($message, $code, $previous);
        }
        /** Returns data of the Error
         *
         * @alias getData();
         *
         * @return  mixed  The data of the error to return
         */
        public function get_data(){
            return $this->data;
        }
        /** Returns data of the Error
         *
         * @alias get_data();
         *
         * @return  mixed  The data of the error to return
         */
        public function getData(){
            return $this->data;
        }
        /** Returns parser node of the Error
         *
         * @alias getNode();
         *
         * @return  \PHPParser_Node|null  The parser node of the error to return
         */
        public function get_node(){
            return $this->node;
        }
        /** Returns parser node of the Error
         *
         * @alias get_node();
         *
         * @return  \PHPParser_Node|null  The parser node of the error to return
         */
        public function getNode(){
            return $this->node;
        }
    }