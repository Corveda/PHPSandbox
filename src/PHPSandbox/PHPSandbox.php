<?php
    /** PHPSandbox class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;
    use FunctionParser\FunctionParser;

    /**
     * PHPSandbox class for PHP Sandboxes.
     *
     * This class encapsulates the entire functionality of a PHPSandbox so that an end user
     * only has to create a PHPSandbox instance, configure its options, and run their code
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <fieryprophet@yahoo.com>
     * @version 1.1
     */
    class PHPSandbox {
        /**
         * @var    string        The prefix given to the obfuscated sandbox variable passed to the generated code
         */
        protected static $function_prefix = '__PHPSandbox_';
        /**
         * @var    int           A bit flag for the import() method, signifies to import all data from a template
         */
        const IMPORT_ALL = 0;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only options from a template
         */
        const IMPORT_OPTIONS = 1;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only definitions from a template
         */
        const IMPORT_DEFINITIONS = 2;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only whitelists from a template
         */
        const IMPORT_WHITELIST = 4;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only blacklists from a template
         */
        const IMPORT_BLACKLIST = 8;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only trusted code from a template
         */
        const IMPORT_TRUSTED_CODE = 16;
        /**
         * @var    int           A bit flag for the import() method, signifies to import only sandboxed code from a template
         */
        const IMPORT_CODE = 32;
        /**
         * @var    array         A static array of superglobal names used for redefining superglobal values
         */
        public static $superglobals = array(
            '_GET',
            '_POST',
            '_COOKIE',
            '_FILES',
            '_ENV',
            '_REQUEST',
            '_SERVER',
            '_SESSION',
            'GLOBALS'
        );
        /**
         * @var    array        A static array of magic constant names used for redefining magic constant values
         */
        public static $magic_constants = array(
            '__LINE__',
            '__FILE__',
            '__DIR__',
            '__FUNCTION__',
            '__CLASS__',
            '__TRAIT__',
            '__METHOD__',
            '__NAMESPACE__'
        );
        /**
         * @var    array          A static array of defined_* and declared_* functions names used for redefining defined_* and declared_* values
         */
        public static $defined_funcs = array(
            'get_defined_functions',
            'get_defined_vars',
            'get_defined_constants',
            'get_declared_classes',
            'get_declared_interfaces',
            'get_declared_traits'
        );
        /**
         * @var    array          A static array of func_get_args, func_get_arg, and func_num_args used for redefining those functions
         */
        public static $arg_funcs = array(
            'func_get_args',
            'func_get_arg',
            'func_num_args'
        );
        /**
         * @var    string       The randomly generated name of the PHPSandbox variable passed to the generated closure
         */
        public $name = '';
        /**
         * @var    array       Array of defined functions, superglobals, etc. If an array type contains elements, then it overwrites its external counterpart
         */
        protected $definitions = array(
            'functions' => array(),
            'variables' => array(),
            'superglobals' => array(),
            'constants' => array(),
            'magic_constants' => array(),
            'namespaces' => array(),
            'aliases' => array(),
            'classes' => array(),
            'interfaces' => array(),
            'traits' => array()
        );
        /**
         * @var    array       Array of whitelisted functions, classes, etc. If an array type contains elements, then it overrides its blacklist counterpart
         */
        protected $whitelist = array(
            'functions' => array(),
            'variables' => array(),
            'globals' => array(),
            'superglobals' => array(),
            'constants' => array(),
            'magic_constants' => array(),
            'namespaces' => array(),
            'aliases' => array(),
            'classes' => array(),
            'interfaces' => array(),
            'traits' => array(),
            'keywords' => array(),
            'operators' => array(),
            'primitives' => array(),
            'types' => array()
        );
        /**
         * @var    array       Array of blacklisted functions, classes, etc. Any whitelisted array types override their counterpart in this array
         */
        protected $blacklist = array(
            'functions' => array(),
            'variables' => array(),
            'globals' => array(),
            'superglobals' => array(),
            'constants' => array(),
            'magic_constants' => array(),
            'namespaces' => array(),
            'aliases' => array(),
            'classes' => array(),
            'interfaces' => array(),
            'traits' => array(),
            'keywords' => array(
                'declare' => true,
                'include' => true,
                'eval' => true,
                'exit' => true,
                'halt' => true
            ),
            'operators' => array(),
            'primitives' => array(),
            'types' => array()
        );
        /* CONFIGURATION OPTION FLAGS */
        /**
         * @var    bool       The error_reporting level to set the PHPSandbox scope to when executing the generated closure, if set to null it will use parent scope error level.
         * @default null
         */
        public $error_level                 = null;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist prepended and appended code?
         * @default true
         */
        public $auto_whitelist_trusted_code = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist functions created in sandboxed code if $allow_functions is true?
         * @default true
         */
        public $auto_whitelist_functions    = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist constants created in sandboxed code if $allow_constants is true?
         * @default true
         */
        public $auto_whitelist_constants    = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist global variables created in sandboxed code if $allow_globals is true? (Used to whitelist them in the variables list)
         * @default true
         */
        public $auto_whitelist_globals      = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist classes created in sandboxed code if $allow_classes is true?
         * @default true
         */
        public $auto_whitelist_classes      = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist interfaces created in sandboxed code if $allow_interfaces is true?
         * @default true
         */
        public $auto_whitelist_interfaces   = true;
        /**
         * @var    bool       Should PHPSandbox automagically whitelist traits created in sandboxed code if $allow_traits is true?
         * @default true
         */
        public $auto_whitelist_traits       = true;
        /**
         * @var    bool       Should PHPSandbox automagically define variables passed to prepended, appended and prepared code closures?
         * @default true
         */
        public $auto_define_vars            = true;
        /**
         * @var    bool       Should PHPSandbox overwrite get_defined_functions, get_defined_vars, get_defined_constants, get_declared_classes, get_declared_interfaces and get_declared_traits?
         * @default true
         */
        public $overwrite_defined_funcs     = true;
        /**
         * @var    bool       Should PHPSandbox overwrite func_get_args, func_get_arg and func_num_args?
         * @default true
         */
        public $overwrite_func_get_args     = true;
        /**
         * @var    bool       Should PHPSandbox overwrite $_GET, $_POST, $_COOKIE, $_FILES, $_ENV, $_REQUEST, $_SERVER, $_SESSION and $GLOBALS superglobals? If so, unless alternate superglobal values have been defined they will return as empty arrays.
         * @default true
         */
        public $overwrite_superglobals      = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare functions?
         * @default false
         */
        public $allow_functions             = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare closures?
         * @default false
         */
        public $allow_closures              = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to create variables?
         * @default true
         */
        public $allow_variables             = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to create static variables?
         * @default true
         */
        public $allow_static_variables      = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to create objects of allow classes (e.g. new keyword)?
         * @default true
         */
        public $allow_objects               = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to define constants?
         * @default false
         */
        public $allow_constants             = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to use global keyword to access variables in the global scope?
         * @default false
         */
        public $allow_globals               = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare namespaces (utilizing the define_namespace function?)
         * @default false
         */
        public $allow_namespaces            = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to use namespaces and declare namespace aliases (utilizing the define_alias function?)
         * @default false
         */
        public $allow_aliases               = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare classes?
         * @default false
         */
        public $allow_classes               = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare interfaces?
         * @default false
         */
        public $allow_interfaces            = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to declare traits?
         * @default false
         */
        public $allow_traits                = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to create generators?
         * @default true
         */
        public $allow_generators            = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to escape to HTML?
         * @default false
         */
        public $allow_escaping              = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to cast types? (This will still be subject to allowed classes)
         * @default false
         */
        public $allow_casting               = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to suppress errors (e.g. the @ operator?)
         * @default false
         */
        public $allow_error_suppressing     = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to assign references?
         * @default true
         */
        public $allow_references            = true;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to use backtick execution? (e.g. $var = \`ping google.com\`; This will also be disabled if shell_exec is not whitelisted or if it is blacklisted, and will be converted to a defined shell_exec function call if one is defined)
         * @default false
         */
        public $allow_backticks             = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to halt the PHP compiler?
         * @default false
         */
        public $allow_halting               = false;
        /* TRUSTED CODE STRINGS */
        /**
         * @var    string     String of prepended code, will be automagically whitelisted for functions, variables, globals, constants, classes, interfaces and traits if $auto_whitelist_trusted_code is true
         */
        public $prepended_code = '';
        /**
         * @var    string     String of appended code, will be automagically whitelisted for functions, variables, globals, constants, classes, interfaces and traits if $auto_whitelist_trusted_code is true
         */
        public $appended_code = '';
        /* OUTPUT */
        /**
         * @var float|null    Float of the number of microseconds it took to prepare the sandbox
         */
        public $prepare_time = null;
        /**
         * @var float|null    Float of the number of microseconds it took to execute the sandbox
         */
        public $execution_time = null;
        /**
         * @var    string     String of preparsed code, for debugging and serialization purposes
         */
        public $preparsed_code = '';
        /**
         * @var    array      Array of parsed code broken down into AST tokens, for debugging and serialization purposes
         */
        public $parsed_ast = array();
        /**
         * @var    string     String of prepared code, for debugging and serialization purposes
         */
        public $prepared_code = '';
        /**
         * @var    array      Array of prepared code broken down into AST tokens, for debugging and serialization purposes
         */
        public $prepared_ast = array();
        /**
         * @var    string     String of generated code, for debugging and serialization purposes
         */
        public $generated_code = '';
        /**
         * @var \Closure|null Closure generated by PHPSandbox execution, stored for future executions without the need to reparse and validate the code
         */
        public $generated_closure = null;
        /** PHPSandbox class constructor
         *
         * @example $sandbox = new PHPSandbox\PHPSandbox;
         *
         * You can pass optional arrays of predefined functions, variables, etc. to the sandbox through the constructor
         *
         * @param   array   $options            Optional array of options to set for the sandbox
         * @param   array   $functions          Optional array of functions to define for the sandbox
         * @param   array   $variables          Optional array of variables to define for the sandbox
         * @param   array   $constants          Optional array of constants to define for the sandbox
         * @param   array   $namespaces         Optional array of namespaces to define for the sandbox
         * @param   array   $aliases            Optional array of aliases to define for the sandbox
         * @param   array   $superglobals       Optional array of superglobals to define for the sandbox
         * @param   array   $magic_constants    Optional array of magic constants to define for the sandbox
         * @param   array   $classes            Optional array of classes to define for the sandbox
         * @param   array   $interfaces         Optional array of interfaces to define for the sandbox
         * @param   array   $traits             Optional array of traits to define for the sandbox
         * @return  PHPSandbox                       The returned PHPSandbox variable
         */
		public function __construct(array $options = array(),
                                    array $functions = array(),
                                    array $variables = array(),
                                    array $constants = array(),
                                    array $namespaces = array(),
                                    array $aliases = array(),
                                    array $superglobals = array(),
                                    array $magic_constants = array(),
                                    array $classes = array(),
                                    array $interfaces = array(),
                                    array $traits = array()){
            $this->name = static::$function_prefix . md5(uniqid());
            $this->set_options($options)
                ->define_funcs($functions)
                ->define_vars($variables)
                ->define_consts($constants)
                ->define_namespaces($namespaces)
                ->define_aliases($aliases)
                ->define_superglobals($superglobals)
                ->define_magic_consts($magic_constants)
                ->define_classes($classes)
                ->define_interfaces($interfaces)
                ->define_traits($traits);
            return $this;
		}
        /** PHPSandbox static factory method
         *
         * You can pass optional arrays of predefined functions, variables, etc. to the sandbox through the constructor
         *
         * @example $sandbox = PHPSandbox\PHPSandbox::create();
         *
         * @param   array   $options            Optional array of options to set for the sandbox
         * @param   array   $functions          Optional array of functions to define for the sandbox
         * @param   array   $variables          Optional array of variables to define for the sandbox
         * @param   array   $constants          Optional array of constants to define for the sandbox
         * @param   array   $namespaces         Optional array of namespaces to define for the sandbox
         * @param   array   $aliases            Optional array of aliases to define for the sandbox
         * @param   array   $superglobals       Optional array of superglobals to define for the sandbox
         * @param   array   $magic_constants    Optional array of magic constants to define for the sandbox
         * @param   array   $classes            Optional array of classes to define for the sandbox
         * @param   array   $interfaces         Optional array of interfaces to define for the sandbox
         * @param   array   $traits             Optional array of traits to define for the sandbox
         *
         * @return  PHPSandbox                  The returned PHPSandbox variable
         */
        public static function create(array $options = array(),
                                      array $functions = array(),
                                      array $variables = array(),
                                      array $constants = array(),
                                      array $namespaces = array(),
                                      array $aliases = array(),
                                      array $superglobals = array(),
                                      array $magic_constants = array(),
                                      array $classes = array(),
                                      array $interfaces = array(),
                                      array $traits = array()){
            return new static($options, $functions, $variables, $constants, $namespaces, $aliases, $superglobals, $magic_constants, $classes, $interfaces, $traits);
        }
        /** PHPSandbox __invoke magic method
         *
         * Besides the code or closure to be executed, you can also pass additional arguments that will overwrite the default values of their respective arguments defined in the code
         *
         * @example $sandbox = new PHPSandbox\PHPSandbox; $sandbox(function(){ echo 'Hello world!'; });
         *
         * @param   \Closure|callable|string   $code          The closure, callable or string of code to execute
         *
         * @return  mixed                      The output of the executed sandboxed code
         */
        public function __invoke($code){
            return call_user_func_array(array($this, 'execute'), func_get_args());
        }
        /** PHPSandbox __sleep magic method
         *
         * @example $sandbox = new PHPSandbox\PHPSandbox; serialize($sandbox);
         *
         * @return  array                      An array of property keys to be serialized
         */
        public function __sleep(){
            $this->generated_closure = null;
            return array_keys(get_object_vars($this));
        }
        /** PHPSandbox __wakeup magic method
         *
         * @example $sandbox = unserialize($sandbox_string);
         */
        public function __wakeup(){
            if($this->generated_code){
                @eval($this->generated_code);
            }
        }
        /** Import JSON template into sandbox
         *
         * @example $sandbox->import(array('code' => 'echo "Hello World!";'));
         * @example $sandbox->import(file_get_contents("template.json"));
         *
         * @param   array|string    $template          The JSON array or string template to import
         * @param   int             $import_flag       Binary flags signifying which parts of the JSON template to import
         *
         * @throws  Error           Throws exception if JSON template could not be imported
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function import($template, $import_flag = 0){
            if(is_string($template)){
                $template = json_decode($template);
            }
            if(!is_array($template)){
                throw new Error("Sandbox could not import malformed JSON template!");
            }
            if(isset($template['options']) && is_array($template['options']) && (!$import_flag || ($import_flag & static::IMPORT_OPTIONS))){
                $this->set_options($template['options']);
            }
            if(isset($template['definitions']) && is_array($template['definitions']) && (!$import_flag || ($import_flag & static::IMPORT_DEFINITIONS))){
                foreach($template['definitions'] as $type => $data){
                    if(method_exists($this, 'define_' . $type)){
                        switch($type){
                            case 'func':
                                foreach($data as $key => $value){
                                    $function = null;
                                    @eval('$function = ' . $value["fullcode"] .';');
                                    if(!is_callable($function)){
                                        throw new Error("Could not import function $key! Please check your code for errors!");
                                    }
                                    $this->define_func($key, $function, $value["pass"]);
                                }
                                break;
                            case 'superglobal':
                                foreach($data as $key => $value){
                                    $this->define_superglobal($key, $value["key"], $value["value"]);
                                }
                                break;
                            case 'namespace':
                                foreach($data as $key => $value){
                                    $this->define_namespace($key);
                                }
                                break;
                            case 'alias':
                                foreach($data as $key => $value){
                                    $this->define_alias($key, $value ? $value : null);
                                }
                                break;
                            case 'class':
                                foreach($data as $key => $value){
                                    $this->define_class($key, $value);
                                }
                                break;
                            case 'interface':
                                foreach($data as $key => $value){
                                    $this->define_interface($key, $value);
                                }
                                break;
                            case 'trait':
                                foreach($data as $key => $value){
                                    $this->define_trait($key, $value);
                                }
                                break;

                            default:
                                foreach($data as $key => $value){
                                    call_user_func_array(array($this, 'define_' . $type), array($key, $value["value"]));
                                }
                                break;
                        }
                    }
                }
            }
            if(isset($template['whitelist']) && is_array($template['whitelist']) && (!$import_flag || ($import_flag & static::IMPORT_WHITELIST))){
                foreach($template['whitelist'] as $type => $data){
                    if(method_exists($this, 'whitelist_' . $type)){
                        call_user_func_array(array($this, 'whitelist_' . $type), array($data));
                    }
                }
            }
            if(isset($template['blacklist']) && is_array($template['blacklist']) && (!$import_flag || ($import_flag & static::IMPORT_BLACKLIST))){
                foreach($template['blacklist'] as $type => $data){
                    if(method_exists($this, 'blacklist_' . $type)){
                        call_user_func_array(array($this, 'blacklist_' . $type), array($data));
                    }
                }
            }
            if(!$import_flag || ($import_flag & static::IMPORT_TRUSTED_CODE)){
                $this->clear_trusted_code();
                if(isset($template['prepend_code']) && $template['prepend_code']){
                    $this->prepend($template['prepend_code']);
                }
                if(isset($template['append_code']) && $template['append_code']){
                    $this->append($template['append_code']);
                }
            }
            if(!$import_flag || ($import_flag & static::IMPORT_CODE)){
                $this->clear_closure();
                if(isset($template['code']) && $template['code']){
                    $this->prepare($template['code']);
                }
            }
            return $this;
        }
        /** Import JSON template into sandbox
         *
         * @alias   import();
         *
         * @example $sandbox->importJSON(array('code' => 'echo "Hello World!";'));
         * @example $sandbox->importJSON(file_get_contents("template.json"));
         *
         * @param   array|string    $template          The JSON array or string template to import
         * @param   int             $import_flag       Binary flags signifying which parts of the JSON template to import
         *
         * @throws  Error           Throws exception if JSON template could not be imported
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function importJSON($template, $import_flag = 0){
            return $this->import($template, $import_flag);
        }
        /** Get name of PHPSandbox variable
         * @return  string                     The name of the PHPSandbox variable
         */
        public function get_name(){
            return $this->name;
        }
        /** Set PHPSandbox option
         *
         * You can pass an $option name to set to $value, an array of $option names to set to $value, or an associative array of $option names and their values to set.
         *
         * @example $sandbox->set_option(array('allow_functions' => true));
         *
         * @example $sandbox->set_option(array('allow_functions', 'allow_classes'), true);
         *
         * @example $sandbox->set_option('allow_functions', true);
         *
         * @param   string|array    $option     String or array of strings or associative array of keys of option names to set $value to
         * @param   bool|int|null   $value      Boolean, integer or null $value to set $option to (optional)
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function set_option($option, $value = null){
            if(is_array($option)){
                return $this->set_options($option, $value);
            }
            $option = strtolower($option); //normalize option names
            switch($option){
                case 'error_level':
                    $this->error_level = is_numeric($value) ? intval($value) : null;
                    break;
                case 'auto_whitelist_trusted_code':
                    $this->auto_whitelist_trusted_code = $value ? true : false;
                    break;
                case 'auto_whitelist_functions':
                    $this->auto_whitelist_functions = $value ? true : false;
                    break;
                case 'auto_whitelist_constants':
                    $this->auto_whitelist_constants = $value ? true : false;
                    break;
                case 'auto_whitelist_globals':
                    $this->auto_whitelist_globals = $value ? true : false;
                    break;
                case 'auto_whitelist_classes':
                    $this->auto_whitelist_classes = $value ? true : false;
                    break;
                case 'auto_whitelist_interfaces':
                    $this->auto_whitelist_interfaces = $value ? true : false;
                    break;
                case 'auto_whitelist_traits':
                    $this->auto_whitelist_traits = $value ? true : false;
                    break;
                case 'auto_define_vars':
                    $this->auto_define_vars = $value ? true : false;
                    break;
                case 'overwrite_defined_funcs':
                    $this->overwrite_defined_funcs = $value ? true : false;
                    break;
                case 'overwrite_func_get_args':
                    $this->overwrite_func_get_args = $value ? true : false;
                    break;
                case 'overwrite_superglobals':
                    $this->overwrite_superglobals = $value ? true : false;
                    break;
                case 'allow_functions':
                    $this->allow_functions = $value ? true : false;
                    break;
                case 'allow_closures':
                    $this->allow_closures = $value ? true : false;
                    break;
                case 'allow_variables':
                    $this->allow_variables = $value ? true : false;
                    break;
                case 'allow_static_variables':
                    $this->allow_static_variables = $value ? true : false;
                    break;
                case 'allow_objects':
                    $this->allow_objects = $value ? true : false;
                    break;
                case 'allow_constants':
                    $this->allow_constants = $value ? true : false;
                    break;
                case 'allow_globals':
                    $this->allow_globals = $value ? true : false;
                    break;
                case 'allow_namespaces':
                    $this->allow_namespaces = $value ? true : false;
                    break;
                case 'allow_aliases':
                    $this->allow_aliases = $value ? true : false;
                    break;
                case 'allow_classes':
                    $this->allow_classes = $value ? true : false;
                    break;
                case 'allow_interfaces':
                    $this->allow_interfaces = $value ? true : false;
                    break;
                case 'allow_traits':
                    $this->allow_traits = $value ? true : false;
                    break;
                case 'allow_generators':
                    $this->allow_generators = $value ? true : false;
                    break;
                case 'allow_escaping':
                    $this->allow_escaping = $value ? true : false;
                    break;
                case 'allow_casting':
                    $this->allow_casting = $value ? true : false;
                    break;
                case 'allow_error_suppressing':
                    $this->allow_error_suppressing = $value ? true : false;
                    break;
                case 'allow_references':
                    $this->allow_references = $value ? true : false;
                    break;
                case 'allow_backticks':
                    $this->allow_backticks = $value ? true : false;
                    break;
                case 'allow_halting':
                    $this->allow_halting = $value ? true : false;
                    break;
            }
            return $this;
        }
        /** Set PHPSandbox options by array
         *
         * You can pass an array of option names to set to $value, or an associative array of option names and their values to set.
         *
         * @example $sandbox->set_option(array('allow_functions' => true));
         *
         * @example $sandbox->set_option(array('allow_functions', 'allow_classes'), true);
         *
         * @param   array|string    $options    Array of strings or associative array of keys of option names to set $value to, or JSON array or string template to import
         * @param   bool|int|null   $value      Boolean, integer or null $value to set $option to (optional)
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function set_options($options, $value = null){
            if(is_string($options) || (is_array($options) && isset($options["options"]))){
                return $this->import($options);
            }
            foreach($options as $name => $_value){
                $this->set_option(is_int($name) ? $_value : $name, is_int($name) ? $value : $_value);
            }
            return $this;
        }
        /** Reset PHPSandbox options to their default values
         *
         * @example $sandbox->reset_options();
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function reset_options(){
            foreach(get_class_vars(__CLASS__) as $option => $value){
                if($option == 'error_level' || is_bool($value)){
                    $this->set_option($option, $value);
                }
            }
            return $this;
        }
        /** Get PHPSandbox option
         *
         * You pass a string $option name to get its associated value
         *
         * @example $sandbox->get_option('allow_functions');
         *
         * @param   string          $option     String of $option name to get
         *
         * @return  boolean|int|null            Returns the value of the requested option
         */
        public function get_option($option){
            $option = strtolower($option);  //normalize option names
            switch($option){
                case 'error_level':
                    return $this->error_level;
                    break;
                case 'auto_whitelist_trusted_code':
                    return $this->auto_whitelist_trusted_code;
                    break;
                case 'auto_whitelist_functions':
                    return $this->auto_whitelist_functions;
                    break;
                case 'auto_whitelist_constants':
                    return $this->auto_whitelist_constants;
                    break;
                case 'auto_whitelist_globals':
                    return $this->auto_whitelist_globals;
                    break;
                case 'auto_whitelist_classes':
                    return $this->auto_whitelist_classes;
                    break;
                case 'auto_whitelist_interfaces':
                    return $this->auto_whitelist_interfaces;
                    break;
                case 'auto_whitelist_traits':
                    return $this->auto_whitelist_traits;
                    break;
                case 'auto_define_vars':
                    return $this->auto_define_vars;
                    break;
                case 'overwrite_defined_funcs':
                    return $this->overwrite_defined_funcs;
                    break;
                case 'overwrite_func_get_args':
                    return $this->overwrite_func_get_args;
                    break;
                case 'overwrite_superglobals':
                    return $this->overwrite_superglobals;
                    break;
                case 'allow_functions':
                    return $this->allow_functions;
                    break;
                case 'allow_closures':
                    return $this->allow_closures;
                    break;
                case 'allow_variables':
                    return $this->allow_variables;
                    break;
                case 'allow_static_variables':
                    return $this->allow_static_variables;
                    break;
                case 'allow_objects':
                    return $this->allow_objects;
                    break;
                case 'allow_constants':
                    return $this->allow_constants;
                    break;
                case 'allow_globals':
                    return $this->allow_globals;
                    break;
                case 'allow_namespaces':
                    return $this->allow_namespaces;
                    break;
                case 'allow_aliases':
                    return $this->allow_aliases;
                    break;
                case 'allow_classes':
                    return $this->allow_classes;
                    break;
                case 'allow_interfaces':
                    return $this->allow_interfaces;
                    break;
                case 'allow_traits':
                    return $this->allow_traits;
                    break;
                case 'allow_generators':
                    return $this->allow_generators;
                    break;
                case 'allow_escaping':
                    return $this->allow_escaping;
                    break;
                case 'allow_casting':
                    return $this->allow_casting;
                    break;
                case 'allow_error_suppressing':
                    return $this->allow_error_suppressing;
                    break;
                case 'allow_references':
                    return $this->allow_references;
                    break;
                case 'allow_backticks':
                    return $this->allow_backticks;
                    break;
                case 'allow_halting':
                    return $this->allow_halting;
                    break;
            }
            return null;
        }
        /** Get PHPSandbox prepended code
         * @return  string          Returns a string of the prepended code
         */
        public function get_prepended_code(){
            return $this->prepended_code;
        }
        /** Get PHPSandbox appended code
         * @return  string          Returns a string of the appended code
         */
        public function get_appended_code(){
            return $this->appended_code;
        }
        /** Get PHPSandbox preparsed code
         * @return  string          Returns a string of the preparsed code
         */
        public function get_preparsed_code(){
            return $this->preparsed_code;
        }
        /** Get PHPSandbox parsed AST array
         * @return  array           Returns an array of the parsed AST code
         */
        public function get_parsed_ast(){
            return $this->parsed_ast;
        }
        /** Get PHPSandbox prepared code
         * @return  string          Returns a string of the prepared code
         */
        public function get_prepared_code(){
            return $this->prepared_code;
        }
        /** Get PHPSandbox parsed AST array
         * @return  array           Returns an array of the parsed AST code
         */
        public function get_prepared_ast(){
            return $this->prepended_code;
        }
        /** Get PHPSandbox generated code
         * @return  string          Returns a string of the generated code
         */
        public function get_generated_code(){
            return $this->prepended_code;
        }
        /** Get PHPSandbox generated closure
         * @return  \Closure        Returns the generated closure
         */
        public function get_generated_closure(){
            return $this->generated_closure;
        }
        /** Get PHPSandbox generated closure
         * @alias   get_generated_closure()
         * @return  \Closure        Returns the generated closure
         */
        public function get_closure(){
            return $this->get_generated_closure();
        }
        /** Get PHPSandbox redefined functions in place of get_defined_functions(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $functions      Array result from get_defined_functions() is passed here
         *
         * @return  array           Returns the redefined functions array
         */
        public function _get_defined_functions(array $functions = array()){
            if(count($this->whitelist['functions'])){
                $functions = array();
                foreach($this->whitelist['functions'] as $name => $value){
                    if(isset($this->definitions['functions'][$name]) && is_callable($this->definitions['functions'][$name])){
                        $functions[$name] = $name;
                    } else if(is_callable($name) && is_string($name)){
                        $functions[$name] = $name;
                    }
                }
                foreach($this->definitions['functions'] as $name => $function){
                    if(is_callable($function)){
                        $functions[$name] = $name;
                    }
                }
                return array_values($functions);
            } else if(count($this->blacklist['functions'])){
                foreach($functions as $index => $name){
                    if(isset($this->blacklist['functions'][$name])){
                        unset($functions[$index]);
                    }
                }
                reset($functions);
                return $functions;
            }
            return array();
        }
        /** Get PHPSandbox redefined variables in place of get_defined_vars(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $variables      Array result from get_defined_vars() is passed here
         *
         * @return  array           Returns the redefined variables array
         */
        public function _get_defined_vars(array $variables = array()){
            if(isset($variables[$this->name])){
                unset($variables[$this->name]); //hide PHPSandbox variable
            }
            return $variables;
        }
        /** Get PHPSandbox redefined superglobal. This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   string          $name      Requested superglobal name (e.g. _GET, _POST, etc.)
         *
         * @return  array           Returns the redefined superglobal
         */
        public function _get_superglobal($name){
            $original_name = strtoupper($name);
            $name = $this->normalize_superglobal($name);
            if(isset($this->definitions['superglobals'][$name])){
                $superglobal = $this->definitions['superglobals'][$name];
                if(is_callable($superglobal)){
                    return call_user_func_array($superglobal, array($this));
                }
                return $superglobal;
            } else if(isset($this->whitelist['superglobals'][$name])){
                if(count($this->whitelist['superglobals'][$name])){
                    if(isset($GLOBALS[$original_name])){
                        $whitelisted_superglobal = array();
                        foreach($this->whitelist['superglobals'][$name] as $key => $value){
                            if(isset($GLOBALS[$original_name][$key])){
                                $whitelisted_superglobal[$key] = $GLOBALS[$original_name][$key];
                            }
                        }
                        return $whitelisted_superglobal;
                    }
                } else if(isset($GLOBALS[$original_name])) {
                    return $GLOBALS[$original_name];
                }
            } else if(isset($this->blacklist['superglobals'][$name])){
                if(count($this->blacklist['superglobals'][$name])){
                    if(isset($GLOBALS[$original_name])){
                        $blacklisted_superglobal = $GLOBALS[$original_name];
                        foreach($this->blacklist['superglobals'][$name] as $key => $value){
                            if(isset($blacklisted_superglobal[$key])){
                                unset($blacklisted_superglobal[$key]);
                            }
                        }
                        return $blacklisted_superglobal;
                    }
                }
            }
            return array();
        }
        /** Get PHPSandbox redefined magic constant. This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   string          $name      Requested magic constant name (e.g. __FILE__, __LINE__, etc.)
         *
         * @return  array           Returns the redefined magic constant
         */
        public function _get_magic_const($name){
            $name = $this->normalize_magic_const($name);
            if(isset($this->definitions['magic_constants'][$name])){
                $magic_constant = $this->definitions['magic_constants'][$name];
                if(is_callable($magic_constant)){
                    return call_user_func_array($magic_constant, array($this));
                }
                return $magic_constant;
            }
            return null;
        }
        /** Get PHPSandbox redefined constants in place of get_defined_constants(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $constants      Array result from get_defined_constants() is passed here
         *
         * @return  array           Returns the redefined constants
         */
        public function _get_defined_constants(array $constants = array()){
            if(count($this->whitelist['constants'])){
                $constants = array();
                foreach($this->whitelist['constants'] as $name => $value){
                    if(defined($name)){
                        $constants[$name] = $name;
                    }
                }
                foreach($this->definitions['constants'] as $name => $value){
                    if(defined($name)){ //these shouldn't be undefined, but just in case they are we don't want to report inaccurate information
                        $constants[$name] = $name;
                    }
                }
                return array_values($constants);
            } else if(count($this->blacklist['constants'])){
                foreach($constants as $index => $name){
                    if(isset($this->blacklist['constants'][$name])){
                        unset($constants[$index]);
                    }
                }
                reset($constants);
                return $constants;
            }
            return array();
        }
        /** Get PHPSandbox redefined classes in place of get_declared_classes(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $classes      Array result from get_declared_classes() is passed here
         *
         * @return  array           Returns the redefined classes
         */
        public function _get_declared_classes(array $classes = array()){
            if(count($this->whitelist['classes'])){
                $classes = array();
                foreach($this->whitelist['classes'] as $name => $value){
                    if(class_exists($name)){
                        $classes[strtolower($name)] = $name;
                    }
                }
                foreach($this->definitions['classes'] as $name => $value){
                    if(class_exists($value)){
                        $classes[strtolower($name)] = $value;
                    }
                }
                return array_values($classes);
            } else if(count($this->blacklist['classes'])){
                $valid_classes = array();
                foreach($classes as $class){
                    $valid_classes[$this->normalize_class($class)] = $class;
                }
                foreach($this->definitions['classes'] as $name => $value){
                    if(class_exists($value)){
                        $valid_classes[$this->normalize_class($name)] = $value;
                    }
                }
                foreach($valid_classes as $index => $name){
                    if(isset($this->blacklist['classes'][$this->normalize_class($name)])){
                        unset($valid_classes[$index]);
                    }
                }
                return array_values($classes);
            }
            $classes = array();
            foreach($this->definitions['classes'] as $value){
                if(class_exists($value)){
                    $classes[strtolower($value)] = $value;
                }
            }
            return array_values($classes);
        }
        /** Get PHPSandbox redefined interfaces in place of get_declared_interfaces(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $interfaces      Array result from get_declared_interfaces() is passed here
         *
         * @return  array           Returns the redefined interfaces
         */
        public function _get_declared_interfaces(array $interfaces = array()){
            if(count($this->whitelist['interfaces'])){
                $interfaces = array();
                foreach($this->whitelist['interfaces'] as $name => $value){
                    if(interface_exists($name)){
                        $interfaces[strtolower($name)] = $name;
                    }
                }
                foreach($this->definitions['interfaces'] as $name => $value){
                    if(interface_exists($value)){
                        $interfaces[strtolower($name)] = $value;
                    }
                }
                return array_values($interfaces);
            } else if(count($this->blacklist['interfaces'])){
                $valid_interfaces = array();
                foreach($interfaces as $interface){
                    $valid_interfaces[$this->normalize_interface($interface)] = $interface;
                }
                foreach($this->definitions['interfaces'] as $name => $value){
                    if(interface_exists($value)){
                        $valid_interfaces[$this->normalize_interface($name)] = $value;
                    }
                }
                foreach($valid_interfaces as $index => $name){
                    if(isset($this->blacklist['interfaces'][$this->normalize_interface($name)])){
                        unset($valid_interfaces[$index]);
                    }
                }
                return array_values($interfaces);
            }
            $interfaces = array();
            foreach($this->definitions['interfaces'] as $value){
                if(interface_exists($value)){
                    $interfaces[strtolower($value)] = $value;
                }
            }
            return array_values($interfaces);
        }
        /** Get PHPSandbox redefined traits in place of get_declared_traits(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $traits      Array result from get_declared_traits() is passed here
         *
         * @return  array           Returns the redefined traits
         */
        public function _get_declared_traits(array $traits = array()){
            if(count($this->whitelist['traits'])){
                $traits = array();
                foreach($this->whitelist['traits'] as $name => $value){
                    if(trait_exists($name)){
                        $traits[strtolower($name)] = $name;
                    }
                }
                foreach($this->definitions['traits'] as $name => $value){
                    if(trait_exists($value)){
                        $traits[strtolower($name)] = $value;
                    }
                }
                return array_values($traits);
            } else if(count($this->blacklist['traits'])){
                $valid_traits = array();
                foreach($traits as $trait){
                    $valid_traits[$this->normalize_trait($trait)] = $trait;
                }
                foreach($this->definitions['traits'] as $name => $value){
                    if(trait_exists($value)){
                        $valid_traits[$this->normalize_trait($name)] = $value;
                    }
                }
                foreach($valid_traits as $index => $name){
                    if(isset($this->blacklist['traits'][$this->normalize_trait($name)])){
                        unset($valid_traits[$index]);
                    }
                }
                return array_values($traits);
            }
            $traits = array();
            foreach($this->definitions['traits'] as $value){
                if(trait_exists($value)){
                    $traits[strtolower($value)] = $value;
                }
            }
            return array_values($traits);
        }
        /** Get PHPSandbox redefined function arguments array
         *
         * @param   array           $arguments      Array result from func_get_args() is passed here
         *
         * @return  array           Returns the redefined arguments array
         */
        public function _func_get_args(array $arguments = array()){
            foreach($arguments as $index => $value){
                if($value instanceof self){
                    unset($arguments[$index]); //hide PHPSandbox variable
                }
            }
            return $arguments;
        }
        /** Get PHPSandbox redefined function argument
         *
         * @param   array           $arguments      Array result from func_get_args() is passed here
         *
         * @param   int             $index          Requested func_get_arg index is passed here
         *
         * @return  array           Returns the redefined argument
         */
        public function _func_get_arg(array $arguments = array(), $index = 0){
            if($arguments[$index] instanceof self){
                $index++;   //get next argument instead
            }
            return isset($arguments[$index]) && !($arguments[$index] instanceof self) ? $arguments[$index] : null;
        }
        /** Get PHPSandbox redefined number of function arguments
         *
         * @param   array           $arguments      Array result from func_get_args() is passed here
         *
         * @return  int             Returns the redefined number of function arguments
         */
        public function _func_num_args(array $arguments = array()){
            $count = count($arguments);
            foreach($arguments as $argument){
                if($argument instanceof self){
                    $count--;
                }
            }
            return $count > 0 ? $count : 0;
        }
        /** Get PHPSandbox redefined function. This is an internal PHPSandbox function but requires public access to work.
         *
         * @throws  Error           Will throw exception if invalid function requested
         *
         * @return  mixed           Returns the redefined function result
         */
        public function call_func(){
            $arguments = func_get_args();
            $name = array_shift($arguments);
            $original_name = $name;
            $name = $this->normalize_func($name);
            if(isset($this->definitions['functions'][$name]) && is_callable($this->definitions['functions'][$name]['function'])){
                $function = $this->definitions['functions'][$name]['function'];
                if($this->definitions['functions'][$name]['pass_sandbox']){            //pass the PHPSandbox instance to the defined function?
                    array_unshift($arguments, $this);  //push PHPSandbox instance into first argument so user can test against it
                }
                return call_user_func_array($function, $arguments);
            }
            if(is_callable($name)){
                return call_user_func_array($name, $arguments);
            }
            throw new Error("Sandboxed code attempted to call invalid function: $original_name");
        }
        /** Define PHPSandbox definitions, such as functions, constants, namespaces, etc.
         *
         * You can pass a string of the $type, $name and $value, or pass an associative array of definitions types and
         * an associative array of their corresponding values
         *
         * @example $sandbox->define('functions', 'test', function(){ echo 'test'; });
         *
         * @example $sandbox->define(array('functions' => array('test' => function(){ echo 'test'; }));
         *
         * @param   string|array        $type       Associative array or string of definition type to define
         * @param   string|array|null   $name       Associative array or string of definition name to define
         * @param   mixed|null          $value      Value of definition to define
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function define($type, $name = null, $value = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($_type) && $_type && is_array($name)){
                        foreach($name as $_name => $_value){
                            $this->define($_type, (is_int($_name) ? $_value : $_name), (is_int($_name) ? $value : $_value));
                        }
                    }
                }
            } else if($type && is_array($name)){
                foreach($name as $_name => $_value){
                    $this->define($type, (is_int($_name) ? $_value : $_name), (is_int($_name) ? $value : $_value));
                }
            } else if($type && $name){
                switch($type){
                    case 'functions':
                        return $this->define_func($name, $value);
                    case 'variables':
                        return $this->define_var($name, $value);
                    case 'superglobals':
                        return $this->define_superglobal($name, $value);
                    case 'constants':
                        return $this->define_const($name, $value);
                    case 'magic_constants':
                        return $this->define_magic_const($name, $value);
                    case 'namespaces':
                        return $this->define_namespace($name);
                    case 'aliases':
                        return $this->define_alias($name, $value);
                    case 'classes':
                        return $this->define_class($name, $value);
                    case 'interfaces':
                        return $this->define_interface($name, $value);
                    case 'traits':
                        return $this->define_trait($name, $value);
                }
            }
            return $this;
        }
        /** Undefine PHPSandbox definitions, such as functions, constants, namespaces, etc.
         *
         * You can pass a string of the $type and $name to undefine, or pass an associative array of definitions types
         * and an array of key names to undefine
         *
         * @example $sandbox->undefine('functions', 'test');
         *
         * @example $sandbox->undefine(array('functions' => array('test'));
         *
         * @param   string|array    $type       Associative array or string of definition type to undefine
         * @param   string|array    $name       Associative array or string of definition name to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($_type) && $_type && is_array($name)){
                        foreach($name as $_name){
                            if(is_string($_name) && $_name){
                                $this->undefine($type, $name);
                            }
                        }
                    }
                }
            } else if(is_string($type) && $type && is_array($name)){
                foreach($name as $_name){
                    if(is_string($_name) && $_name){
                        $this->undefine($type, $name);
                    }
                }
            } else if($type && $name){
                switch($type){
                    case 'functions':
                        return $this->undefine_func($name);
                    case 'variables':
                        return $this->undefine_var($name);
                    case 'superglobals':
                        return $this->undefine_superglobal($name);
                    case 'constants':
                        return $this->undefine_const($name);
                    case 'magic_constants':
                        return $this->undefine_magic_const($name);
                    case 'namespaces':
                        return $this->undefine_namespace($name);
                    case 'aliases':
                        return $this->undefine_alias($name);
                    case 'classes':
                        return $this->undefine_class($name);
                    case 'interfaces':
                        return $this->undefine_interface($name);
                    case 'traits':
                        return $this->undefine_trait($name);
                }
            }
            return $this;
        }
        /** Define PHPSandbox function
         *
         * You can pass the function $name and $function closure or callable to define, or an associative array of
         * functions to define, which can have callable values or arrays of the function callable and $pass_sandbox flag
         *
         * @example $sandbox->define_func('test', function(){ echo 'test'; });
         *
         * @example $sandbox->define_func(array('test' => function(){ echo 'test'; }));
         *
         * @example $sandbox->define_func(array('test' => array(function(){ echo 'test'; }, true)));
         *
         * @param   string|array    $name           Associative array or string of function $name to define
         * @param   callable        $function       Callable to define $function to
         * @param   bool            $pass_sandbox   Pass PHPSandbox instance to defined function when called? Default is false
         *
         * @throws  Error           Throws exception if unnamed or uncallable $function is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_func($name, $function, $pass_sandbox = false){
            if(is_array($name)){
                return $this->define_funcs($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed function!");
            }
            if(is_array($function) && count($function)){    //so you can pass array of function names and array of function and pass_sandbox flag
                $pass_sandbox = isset($function[1]) ? $function[1] : false;
                $function = $function[0];
            }
            $original_name = $name;
            $name = $this->normalize_func($name);
            if(!is_callable($function)){
                throw new Error("Cannot define uncallable function : $original_name");
            }
            $this->definitions['functions'][$name] = array(
                'function' => $function,
                'pass_sandbox' => $pass_sandbox
            );
            return $this;
        }
        /** Define PHPSandbox functions by array
         *
         * You can pass an associative array of functions to define
         *
         * @example $sandbox->define_funcs(array('test' => function(){ echo 'test'; }));
         *
         * @param   array           $functions       Associative array of $functions to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_funcs(array $functions = array()){
            foreach($functions as $name => $function){
                $this->define_func($name, $function);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined functions
         *
         * @example $sandbox->has_defined_funcs(); //returns number of defined functions, or zero if none defined
         *
         * @return  int           Returns the number of functions this instance has defined
         */
        public function has_defined_funcs(){
            return count($this->definitions['functions']);
        }
        /** Check if PHPSandbox instance has $name function defined
         *
         * @example $sandbox->is_defined_func('test');
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined function, false otherwise
         */
        public function is_defined_func($name){
            $name = $this->normalize_func($name);
            return isset($this->definitions['functions'][$name]);
        }
        /** Undefine PHPSandbox function
         *
         * You can pass a string of function $name to undefine, or pass an array of function names to undefine
         *
         * @example $sandbox->undefine_func('test');
         *
         * @example $sandbox->undefine_func(array('test', 'test2'));
         *
         * @param   string|array          $name       String of function name or array of function names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_func($name){
            if(is_array($name)){
                return $this->undefine_funcs($name);
            }
            $name = $this->normalize_func($name);
            if(isset($this->definitions['functions'][$name])){
                unset($this->definitions['functions'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox functions by array
         *
         * You can pass an array of function names to undefine, or an empty array or null argument to undefine all functions
         *
         * @example $sandbox->undefine_funcs(array('test', 'test2'));
         *
         * @example $sandbox->undefine_funcs(); //WILL UNDEFINE ALL FUNCTIONS!
         *
         * @param   array           $functions       Array of function names to undefine. Passing an empty array or no argument will result in undefining all functions
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_funcs($functions = array()){
            if(count($functions)){
                foreach($functions as $function){
                    $this->undefine_func($function);
                }
            } else {
                $this->definitions['functions'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox variable
         *
         * You can pass the variable $name and $value to define, or an associative array of variables to define
         *
         * @example $sandbox->define_var('test', 1);
         *
         * @example $sandbox->define_var(array('test' => 1));
         *
         * @param   string|array    $name       String of variable $name or associative array to define
         * @param   mixed           $value      Value to define variable to
         *
         * @throws  Error           Throws exception if unnamed variable is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_var($name, $value){
            if(is_array($name)){
                return $this->define_vars($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed variable!");
            }
            $this->definitions['variables'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox variables by array
         *
         * You can pass an associative array of variables to define
         *
         * @example $sandbox->define_vars(array('test' => 1));
         *
         * @param   array           $variables  Associative array of $variables to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_vars(array $variables = array()){
            foreach($variables as $name => $value){
                $this->define_var($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined variables
         *
         * @example $sandbox->has_defined_vars(); //returns number of defined variables, or zero if none defined
         *
         * @return  int           Returns the number of variables this instance has defined
         */
        public function has_defined_vars(){
            return count($this->definitions['variables']);
        }
        /** Check if PHPSandbox instance has $name variable defined
         *
         * @example $sandbox->is_defined_var('test');
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined variable, false otherwise
         */
        public function is_defined_var($name){
            return isset($this->definitions['variables'][$name]);
        }
        /** Undefine PHPSandbox variable
         *
         * You can pass a string of variable $name to undefine, or an array of variable names to undefine
         *
         * @example $sandbox->undefine_var('test');
         *
         * @example $sandbox->undefine_var(array('test', 'test2'));
         *
         * @param   string|array          $name       String of variable name or an array of variable names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_var($name){
            if(is_array($name)){
                return $this->undefine_vars($name);
            }
            if(isset($this->definitions['variables'][$name])){
                unset($this->definitions['variables'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox variables by array
         *
         * You can pass an array of variable names to undefine, or an empty array or null argument to undefine all variables
         *
         * @example $sandbox->undefine_vars(array('test', 'test2'));
         *
         * @example $sandbox->undefine_vars(); //WILL UNDEFINE ALL VARIABLES!
         *
         * @param   array           $variables       Array of variable names to undefine. Passing an empty array or no argument will result in undefining all variables
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_vars(array $variables = array()){
            if(count($variables)){
                foreach($variables as $variable){
                    $this->undefine_var($variable);
                }
            } else {
                $this->definitions['variables'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox superglobal
         *
         * You can pass the superglobal $name and $value to define, or an associative array of superglobals to define, or a third variable to define the $key
         *
         * @example $sandbox->define_superglobal('_GET',  array('page' => 1));
         *
         * @example $sandbox->define_superglobal(array('_GET' => array('page' => 1)));
         *
         * @example $sandbox->define_superglobal('_GET', 'page', 1);
         *
         * @param   string|array    $name       String of superglobal $name or associative array of superglobal names to define
         * @param   mixed           $value      Value to define superglobal to, can be callable
         *
         * @throws  Error           Throws exception if unnamed superglobal is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_superglobal($name, $value){
            if(is_array($name)){
                return $this->define_superglobals($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed superglobal!");
            }
            $name = $this->normalize_superglobal($name);
            if(func_num_args() > 2){
                $key = $value;
                $value = func_get_arg(2);
                $this->definitions['superglobals'][$name][$key] = $value;
            } else {
                $this->definitions['superglobals'][$name] = $value;
            }
            return $this;
        }
        /** Define PHPSandbox superglobals by array
         *
         * You can pass an associative array of superglobals to define
         *
         * @example $sandbox->define_superglobals(array('_GET' => array('page' => 1)));
         *
         * @param   array           $superglobals  Associative array of $superglobals to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_superglobals(array $superglobals = array()){
            foreach($superglobals as $name => $value){
                $this->define_superglobal($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined superglobals, or if superglobal $name has defined keys
         *
         * @example $sandbox->has_defined_superglobals(); //returns number of defined superglobals, or zero if none defined
         *
         * @example $sandbox->has_defined_superglobals('_GET'); //returns number of defined superglobal _GET keys, or zero if none defined
         *
         * @param   string|null     $name       String of superglobal $name to check for keys
         *
         * @return  int|bool        Returns the number of superglobals or superglobal keys this instance has defined, or false if invalid superglobal name specified
         */
        public function has_defined_superglobals($name = null){
            $name = $name ? $this->normalize_superglobal($name) : null;
            return $name ? (isset($this->definitions['superglobals'][$name]) ? count($this->definitions['superglobals'][$name]) : false) : count($this->definitions['superglobals']);
        }
        /** Check if PHPSandbox instance has $name superglobal defined, or if superglobal $name key is defined
         *
         * @example $sandbox->is_defined_superglobal('_GET');
         *
         * @example $sandbox->is_defined_superglobal('_GET', 'page');
         *
         * @param   string          $name       String of superglobal $name to query
         * @param   string|null     $key        String of key to to query in superglobal
         *
         * @return  bool            Returns true if PHPSandbox instance has defined superglobal, false otherwise
         */
        public function is_defined_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->definitions['superglobals'][$name][$key]) : isset($this->definitions['superglobals'][$name]);
        }
        /** Undefine PHPSandbox superglobal or superglobal key
         *
         * You can pass a string of superglobal $name to undefine, or a superglobal $key to undefine, or an array of
         * superglobal names to undefine, or an an associative array of superglobal names and keys to undefine
         *
         * @example $sandbox->undefine_superglobal('_GET');
         *
         * @example $sandbox->undefine_superglobal('_GET', 'page');
         *
         * @example $sandbox->undefine_superglobal(array('_GET', '_POST'));
         *
         * @param   string|array          $name       String of superglobal $name, or array of superglobal names, or associative array of superglobal names and keys to undefine
         * @param   string|null           $key        String of superglobal $key to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_superglobal($name, $key = null){
            if(is_array($name)){
                return $this->undefine_superglobals($name);
            }
            $name = $this->normalize_superglobal($name);
            if($key !== null && is_array($this->definitions['superglobals'][$name])){
                if(isset($this->definitions['superglobals'][$name][$key])){
                    unset($this->definitions['superglobals'][$name][$key]);
                }
            } else if(isset($this->definitions['superglobals'][$name])){
                $this->definitions['superglobals'][$name] = array();
            }
            return $this;
        }
        /** Undefine PHPSandbox superglobals by array
         *
         * You can pass an array of superglobal names to undefine, or an associative array of superglobals names and key
         * to undefine, or an empty array or null to undefine all superglobals
         *
         * @example $sandbox->undefine_superglobals(array('_GET', '_POST'));
         *
         * @example $sandbox->undefine_superglobals(array('_GET' => 'page', '_POST' => 'page'));
         *
         * @example $sandbox->undefine_superglobals(); //WILL UNDEFINE ALL SUPERGLOBALS!
         *
         * @param   array          $superglobals       Associative array of superglobal names and keys or array of superglobal names to undefine
         *
         * @return  PHPSandbox          Returns the PHPSandbox instance for chainability
         */
        public function undefine_superglobals(array $superglobals = array()){
            if(count($superglobals)){
                foreach($superglobals as $superglobal => $name){
                    $name = $this->normalize_superglobal($name);
                    $this->undefine_superglobal(is_int($superglobal) ? $name : $superglobal, is_int($superglobal) ? null : $name);
                }
            } else {
                $this->definitions['superglobals'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox constant
         *
         * You can pass the constant $name and $value to define, or an associative array of constants to define
         *
         * @example $sandbox->define_const('TEST', 1);
         *
         * @example $sandbox->define_const(array('TEST' => 1));
         *
         * @param   string|array    $name       String of constant $name or associative array to define
         * @param   mixed           $value      Value to define constant to
         *
         * @throws  Error           Throws exception if unnamed constant is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_const($name, $value){
            if(is_array($name)){
                return $this->define_consts($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed constant!");
            }
            $this->definitions['constants'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox constants by array
         *
         * You can pass an associative array of constants to define
         *
         * @example $sandbox->define_consts(array('test' => 1));
         *
         * @param   array           $constants  Associative array of $constants to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_consts(array $constants = array()){
            foreach($constants as $name => $value){
                $this->define_const($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined constants
         *
         * @example $sandbox->has_defined_consts(); //returns number of defined constants, or zero if none defined
         *
         * @return  int           Returns the number of constants this instance has defined
         */
        public function has_defined_consts(){
            return count($this->definitions['constants']);
        }
        /** Check if PHPSandbox instance has $name constant defined
         *
         * @example $sandbox->is_defined_const('test');
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined constant, false otherwise
         */
        public function is_defined_const($name){
            return isset($this->definitions['constants'][$name]);
        }
        /** Undefine PHPSandbox constant
         *
         * You can pass a string of constant $name to undefine, or an array of constant names to undefine
         *
         * @example $sandbox->undefine_const('test');
         *
         * @example $sandbox->undefine_const(array('test', 'test2'));
         *
         * @param   string|array          $name       String of constant name or array of constant names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_const($name){
            if(is_array($name)){
                return $this->undefine_consts($name);
            }
            if(isset($this->definitions['constants'][$name])){
                unset($this->definitions['constants'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox constants by array
         *
         * You can pass an array of constant names to undefine, or an empty array or null argument to undefine all constants
         *
         * @example $sandbox->undefine_consts(array('test', 'test2'));
         *
         * @example $sandbox->undefine_consts(); //WILL UNDEFINE ALL CONSTANTS!
         *
         * @param   array           $constants       Array of constant names to undefine. Passing an empty array or no argument will result in undefining all constants
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_consts(array $constants = array()){
            if(count($constants)){
                foreach($constants as $constant){
                    $this->undefine_const($constant);
                }
            } else {
                $this->definitions['constants'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox magic constant
         *
         * You can pass the magic constant $name and $value to define, or an associative array of magic constants to define
         *
         * @example $sandbox->define_magic_const('__LINE__', 1);
         *
         * @example $sandbox->define_magic_const(array('__LINE__' => 1));
         *
         * @param   string|array    $name       String of magic constant $name or associative array to define
         * @param   mixed           $value      Value to define magic constant to, can be callable
         *
         * @throws  Error           Throws exception if unnamed magic constant is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_magic_const($name, $value){
            if(is_array($name)){
                return $this->define_magic_consts($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed magic constant!");
            }
            $name = $this->normalize_magic_const($name);
            $this->definitions['magic_constants'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox magic constants by array
         *
         * You can pass an associative array of magic constants to define
         *
         * @example $sandbox->define_magic_consts(array('__LINE__' => 1));
         *
         * @param   array           $magic_constants  Associative array of $magic_constants to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_magic_consts(array $magic_constants = array()){
            foreach($magic_constants as $name => $value){
                $this->define_magic_const($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined magic constants
         *
         * @example $sandbox->has_defined_magic_consts(); //returns number of defined magic constants, or zero if none defined
         *
         * @return  int           Returns the number of magic constants this instance has defined
         */
        public function has_defined_magic_consts(){
            return count($this->definitions['magic_constants']);
        }
        /** Check if PHPSandbox instance has $name magic constant defined
         *
         * @example $sandbox->is_defined_magic_const('__LINE__');
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined magic constant, false otherwise
         */
        public function is_defined_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->definitions['magic_constants'][$name]);
        }
        /** Undefine PHPSandbox magic constant
         *
         * You can pass an a string of magic constant $name to undefine, or array of magic constant names to undefine
         *
         * @example $sandbox->undefine_magic_const('__LINE__');
         *
         * @example $sandbox->undefine_magic_const(array('__LINE__', '__FILE__'));
         *
         * @param   string|array          $name       String of magic constant name, or array of magic constant names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_magic_const($name){
            if(is_array($name)){
                return $this->undefine_magic_consts($name);
            }
            $name = $this->normalize_magic_const($name);
            if(isset($this->definitions['magic_constants'][$name])){
                unset($this->definitions['magic_constants'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox magic constants by array
         *
         * You can pass an array of magic constant names to undefine, or an empty array or null argument to undefine all magic constants
         *
         * @example $sandbox->undefine_magic_consts(array('__LINE__', '__FILE__'));
         *
         * @example $sandbox->undefine_magic_consts(); //WILL UNDEFINE ALL MAGIC CONSTANTS!
         *
         * @param   array           $magic_constants       Array of magic constant names to undefine. Passing an empty array or no argument will result in undefining all magic constants
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_magic_consts(array $magic_constants = array()){
            if(count($magic_constants)){
                foreach($magic_constants as $magic_constant){
                    $this->undefine_magic_const($magic_constant);
                }
            } else {
                $this->definitions['magic_constants'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox namespace
         *
         * You can pass the namespace $name and $value to define, or an array of namespaces to define
         *
         * @example $sandbox->define_namespace('Foo');
         *
         * @example $sandbox->define_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array    $name       String of namespace $name, or an array of namespace names to define
         *
         * @throws  Error           Throws exception if unnamed namespace is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_namespace($name){
            if(is_array($name)){
                return $this->define_namespaces($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed namespace!");
            }
            $name = $this->normalize_namespace($name);
            $this->definitions['namespaces'][$name] = $name;
            return $this;
        }
        /** Define PHPSandbox namespaces by array
         *
         * You can pass an array of namespaces to define
         *
         * @example $sandbox->define_namespaces(array('Foo', 'Bar'));
         *
         * @param   array           $namespaces  Array of $namespaces to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_namespaces(array $namespaces = array()){
            foreach($namespaces as $name => $alias){
                $this->define_namespace($name, $alias);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined namespaces
         *
         * @example $sandbox->has_defined_namespaces(); //returns number of defined namespaces, or zero if none defined
         *
         * @return  int           Returns the number of namespaces this instance has defined
         */
        public function has_defined_namespaces(){
            return count($this->definitions['namespaces']);
        }
        /** Check if PHPSandbox instance has $name namespace defined
         *
         * @example $sandbox->is_defined_namespace('Foo');
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined namespace, false otherwise
         */
        public function is_defined_namespace($name){
            $name = $this->normalize_namespace($name);
            return isset($this->definitions['namespaces'][$name]);
        }
        /** Undefine PHPSandbox namespace
         *
         * You can pass a string of namespace $name to undefine, or an array of namespace names to undefine
         *
         * @example $sandbox->undefine_namespace('Foo');
         *
         * @example $sandbox->undefine_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array          $name       String of namespace $name, or an array of namespace names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_namespace($name){
            if(is_array($name)){
                return $this->undefine_namespaces($name);
            }
            $name = $this->normalize_namespace($name);
            if(isset($this->definitions['namespaces'][$name])){
                unset($this->definitions['namespaces'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox namespaces by array
         *
         * You can pass an array of namespace names to undefine, or an empty array or null argument to undefine all namespaces
         *
         * @example $sandbox->undefine_namespaces(array('Foo', 'Bar'));
         *
         * @example $sandbox->undefine_namespaces(); //WILL UNDEFINE ALL NAMESPACES!
         *
         * @param   array           $namespaces       Array of namespace names to undefine. Passing an empty array or no argument will result in undefining all namespaces
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_namespaces(array $namespaces = array()){
            if(count($namespaces)){
                foreach($namespaces as $namespace){
                    $this->undefine_namespace($namespace);
                }
            } else {
                $this->definitions['namespaces'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox alias
         *
         * You can pass the namespace $name and $alias to use, an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @example $sandbox->define_alias('Foo');  //use Foo;
         *
         * @example $sandbox->define_alias('Foo', 'Bar');  //use Foo as Bar;
         *
         * @example $sandbox->define_alias(array('Foo', 'Bar')); //use Foo; use Bar;
         *
         * @example $sandbox->define_alias(array('Foo' => 'Bar')); //use Foo as Bar;
         *
         * @param   string|array    $name       String of namespace $name to use, or  or an array of namespaces to use, or an associative array of namespaces and their aliases to use
         * @param   string|null     $alias      String of $alias to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_alias($name, $alias = null){
            if(is_array($name)){
                return $this->define_aliases($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed namespace alias!");
            }
            $name = $this->normalize_alias($name);
            $this->definitions['aliases'][$name] = $alias;
            return $this;
        }
        /** Define PHPSandbox aliases by array
         *
         * You can pass an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @example $sandbox->define_aliases(array('Foo', 'Bar')); //use Foo; use Bar;
         *
         * @example $sandbox->define_aliases(array('Foo' => 'Bar')); //use Foo as Bar;
         *
         * @param   array           $aliases       Array of namespaces to use, or an associative array of namespaces and their aliases to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_aliases(array $aliases = array()){
            foreach($aliases as $name => $alias){
                $this->define_alias($name, $alias);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined aliases
         *
         * @example $sandbox->has_defined_aliases(); //returns number of defined aliases, or zero if none defined
         *
         * @return  int           Returns the number of aliases this instance has defined
         */
        public function has_defined_aliases(){
            return count($this->definitions['aliases']);
        }
        /** Check if PHPSandbox instance has $name alias defined
         *
         * @example $sandbox->is_defined_alias('Foo');
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined aliases, false otherwise
         */
        public function is_defined_alias($name){
            $name = $this->normalize_alias($name);
            return isset($this->definitions['aliases'][$name]);
        }
        /** Undefine PHPSandbox alias
         *
         * You can pass a string of alias $name to undefine, or an array of alias names to undefine
         *
         * @example $sandbox->undefine_alias('Foo');
         *
         * @example $sandbox->undefine_alias(array('Foo', 'Bar'));
         *
         * @param   string|array          $name       String of alias name, or array of alias names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_alias($name){
            if(is_array($name)){
                return $this->undefine_aliases($name);
            }
            $name = $this->normalize_alias($name);
            if(isset($this->definitions['aliases'][$name])){
                unset($this->definitions['aliases'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox aliases by array
         *
         * You can pass an array of alias names to undefine, or an empty array or null argument to undefine all aliases
         *
         * @example $sandbox->undefine_aliases(array('Foo', 'Bar'));
         *
         * @example $sandbox->undefine_aliases(); //WILL UNDEFINE ALL ALIASES!
         *
         * @param   array           $aliases       Array of alias names to undefine. Passing an empty array or no argument will result in undefining all aliases
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_aliases(array $aliases = array()){
            if(count($aliases)){
                foreach($aliases as $alias){
                    $this->undefine_alias($alias);
                }
            } else {
                $this->definitions['aliases'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox use (or alias)
         *
         * @alias   define_alias();
         *
         * You can pass the namespace $name and $alias to use, an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @example $sandbox->define_use('Foo');  //use Foo;
         *
         * @example $sandbox->define_use('Foo', 'Bar');  //use Foo as Bar;
         *
         * @example $sandbox->define_use(array('Foo', 'Bar')); //use Foo; use Bar;
         *
         * @example $sandbox->define_use(array('Foo' => 'Bar')); //use Foo as Bar;
         *
         * @param   string|array    $name       String of namespace $name to use, or  or an array of namespaces to use, or an associative array of namespaces and their aliases to use
         * @param   string|null     $alias      String of $alias to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_use($name, $alias = null){
            return $this->define_alias($name, $alias);
        }
        /** Define PHPSandbox uses (or aliases) by array
         *
         * @alias   define_aliases();
         *
         * You can pass an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @example $sandbox->define_uses(array('Foo', 'Bar')); //use Foo; use Bar;
         *
         * @example $sandbox->define_uses(array('Foo' => 'Bar')); //use Foo as Bar;
         *
         * @param   array           $uses       Array of namespaces to use, or an associative array of namespaces and their aliases to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_uses(array $uses = array()){
            return $this->define_aliases($uses);
        }
        /** Query whether PHPSandbox instance has defined uses (or aliases)
         *
         * @alias   has_defined_aliases();
         *
         * @example $sandbox->has_defined_uses(); //returns number of defined uses (or aliases) or zero if none defined
         *
         * @return  int           Returns the number of uses (or aliases) this instance has defined
         */
        public function has_defined_uses(){
            return $this->has_defined_aliases();
        }
        /** Check if PHPSandbox instance has $name uses (or alias) defined
         *
         * @alias   is_defined_alias();
         *
         * @example $sandbox->is_defined_use('Foo');
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined uses (or aliases) and false otherwise
         */
        public function is_defined_use($name){
            return $this->is_defined_alias($name);
        }
        /** Undefine PHPSandbox use (or alias)
         *
         * You can pass a string of use (or alias) $name to undefine, or an array of use (or alias) names to undefine
         *
         * @example $sandbox->undefine_use('Foo');
         *
         * @example $sandbox->undefine_use(array('Foo', 'Bar'));
         *
         * @param   string|array          $name       String of use (or alias) name, or array of use (or alias) names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_use($name){
            return $this->undefine_alias($name);
        }
        /** Undefine PHPSandbox uses (or aliases) by array
         *
         * @alias   undefine_aliases();
         *
         * You can pass an array of use (or alias) names to undefine, or an empty array or null argument to undefine all uses (or aliases)
         *
         * @example $sandbox->undefine_uses(array('Foo', 'Bar'));
         *
         * @example $sandbox->undefine_uses(); //WILL UNDEFINE ALL USES (OR ALIASES!)
         *
         * @param   array           $uses       Array of use (or alias) names to undefine. Passing an empty array or no argument will result in undefining all uses (or aliases)
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_uses(array $uses = array()){
            return $this->undefine_aliases($uses);
        }
        /** Define PHPSandbox class
         *
         * You can pass the class $name and $value to define, or an associative array of classes to define
         *
         * @example $sandbox->define_class('Test', 'Test2');
         *
         * @example $sandbox->define_class(array('Test' => 'Test2'));
         *
         * @param   string|array    $name       String of class $name or associative array to define
         * @param   mixed           $value      Value to define class to
         *
         * @throws  Error           Throws exception if unnamed class is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_class($name, $value){
            if(is_array($name)){
                return $this->define_classes($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed class!");
            }
            $name = $this->normalize_class($name);
            $this->definitions['classes'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox classes by array
         *
         * You can pass an associative array of classes to define
         *
         * @example $sandbox->define_classes(array('Test' => 'Test2'));
         *
         * @param   array           $classes  Associative array of $classes to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_classes(array $classes = array()){
            foreach($classes as $name => $value){
                $this->define_class($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined classes
         *
         * @example $sandbox->has_defined_classes(); //returns number of defined classes, or zero if none defined
         *
         * @return  int           Returns the number of classes this instance has defined
         */
        public function has_defined_classes(){
            return count($this->definitions['classes']);
        }
        /** Check if PHPSandbox instance has $name class defined
         *
         * @example $sandbox->is_defined_class('Test');
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined class, false otherwise
         */
        public function is_defined_class($name){
            $name = $this->normalize_class($name);
            return isset($this->definitions['classes'][$name]);
        }
        /** Get defined class of $name
         *
         * @example $sandbox->get_defined_class('Test');
         *
         * @param   string          $name       String of class $name to get
         *
         * @throws  Error           Throws an exception if an invalid class name is requested
         *
         * @return  string          Returns string of defined class value
         */
        public function get_defined_class($name){
            $name = $this->normalize_class($name);
            if(!isset($this->definitions['classes'][$name])){
                throw new Error("Could not get undefined class: $name");
            }
            return $this->definitions['classes'][$name];
        }
        /** Undefine PHPSandbox class
         *
         * You can pass a string of class $name to undefine, or an array of class names to undefine
         *
         * @example $sandbox->undefine_class('Test');
         *
         * @example $sandbox->undefine_class(array('Test', 'Test2'));
         *
         * @param   string|array          $name       String of class name or an array of class names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_class($name){
            if(is_array($name)){
                return $this->undefine_classes($name);
            }
            $name = $this->normalize_class($name);
            if(isset($this->definitions['classes'][$name])){
                unset($this->definitions['classes'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox classes by array
         *
         * You can pass an array of class names to undefine, or an empty array or null argument to undefine all classes
         *
         * @example $sandbox->undefine_classes(array('Test', 'Test2'));
         *
         * @example $sandbox->undefine_classes(); //WILL UNDEFINE ALL CLASSES!
         *
         * @param   array           $classes       Array of class names to undefine. Passing an empty array or no argument will result in undefining all classes
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_classes(array $classes = array()){
            if(count($classes)){
                foreach($classes as $class){
                    $this->undefine_class($class);
                }
            } else {
                $this->definitions['classes'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox interface
         *
         * You can pass the interface $name and $value to define, or an associative array of interfaces to define
         *
         * @example $sandbox->define_interface('Test', 'Test2');
         *
         * @example $sandbox->define_interface(array('Test' => 'Test2'));
         *
         * @param   string|array    $name       String of interface $name or associative array to define
         * @param   mixed           $value      Value to define interface to
         *
         * @throws  Error           Throws exception if unnamed interface is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_interface($name, $value){
            if(is_array($name)){
                return $this->define_interfaces($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed interface!");
            }
            $name = $this->normalize_interface($name);
            $this->definitions['interfaces'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox interfaces by array
         *
         * You can pass an associative array of interfaces to define
         *
         * @example $sandbox->define_interfaces(array('Test' => 'Test2'));
         *
         * @param   array           $interfaces  Associative array of $interfaces to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_interfaces(array $interfaces = array()){
            foreach($interfaces as $name => $value){
                $this->define_interface($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined interfaces
         *
         * @example $sandbox->has_defined_interfaces(); //returns number of defined interfaces, or zero if none defined
         *
         * @return  int           Returns the number of interfaces this instance has defined
         */
        public function has_defined_interfaces(){
            return count($this->definitions['interfaces']);
        }
        /** Check if PHPSandbox instance has $name interface defined
         *
         * @example $sandbox->is_defined_interface('Test');
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined interface, false otherwise
         */
        public function is_defined_interface($name){
            $name = $this->normalize_interface($name);
            return isset($this->definitions['interfaces'][$name]);
        }
        /** Get defined interface of $name
         *
         * @example $sandbox->get_defined_interface('Test');
         *
         * @param   string          $name       String of interface $name to get
         *
         * @throws  Error           Throws an exception if an invalid interface name is requested
         *
         * @return  string          Returns string of defined interface value
         */
        public function get_defined_interface($name){
            $name = $this->normalize_interface($name);
            if(!isset($this->definitions['interfaces'][$name])){
                throw new Error("Could not get undefined interface: $name");
            }
            return $this->definitions['interfaces'][$name];
        }
        /** Undefine PHPSandbox interface
         *
         * You can pass a string of interface $name to undefine, or an array of interface names to undefine
         *
         * @example $sandbox->undefine_interface('Test');
         *
         * @example $sandbox->undefine_interface(array('Test', 'Test2'));
         *
         * @param   string|array          $name       String of interface name or an array of interface names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_interface($name){
            if(is_array($name)){
                return $this->undefine_interfaces($name);
            }
            $name = $this->normalize_interface($name);
            if(isset($this->definitions['interfaces'][$name])){
                unset($this->definitions['interfaces'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox interfaces by array
         *
         * You can pass an array of interface names to undefine, or an empty array or null argument to undefine all interfaces
         *
         * @example $sandbox->undefine_interfaces(array('Test', 'Test2'));
         *
         * @example $sandbox->undefine_interfaces(); //WILL UNDEFINE ALL INTERFACES!
         *
         * @param   array           $interfaces       Array of interface names to undefine. Passing an empty array or no argument will result in undefining all interfaces
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_interfaces(array $interfaces = array()){
            if(count($interfaces)){
                foreach($interfaces as $interface){
                    $this->undefine_interface($interface);
                }
            } else {
                $this->definitions['interfaces'] = array();
            }
            return $this;
        }
        /** Define PHPSandbox trait
         *
         * You can pass the trait $name and $value to define, or an associative array of traits to define
         *
         * @example $sandbox->define_trait('Test', 'Test2');
         *
         * @example $sandbox->define_trait(array('Test' => 'Test2'));
         *
         * @param   string|array    $name       String of trait $name or associative array to define
         * @param   mixed           $value      Value to define trait to
         *
         * @throws  Error           Throws exception if unnamed trait is defined
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_trait($name, $value){
            if(is_array($name)){
                return $this->define_traits($name);
            }
            if(!$name){
                throw new Error("Cannot define unnamed trait!");
            }
            $name = $this->normalize_trait($name);
            $this->definitions['traits'][$name] = $value;
            return $this;
        }
        /** Define PHPSandbox traits by array
         *
         * You can pass an associative array of traits to define
         *
         * @example $sandbox->define_traits(array('Test' => 'Test2'));
         *
         * @param   array           $traits  Associative array of $traits to define
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function define_traits(array $traits = array()){
            foreach($traits as $name => $value){
                $this->define_trait($name, $value);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has defined traits
         *
         * @example $sandbox->has_defined_traits(); //returns number of defined traits, or zero if none defined
         *
         * @return  int           Returns the number of traits this instance has defined
         */
        public function has_defined_traits(){
            return count($this->definitions['traits']);
        }
        /** Check if PHPSandbox instance has $name trait defined
         *
         * @example $sandbox->is_defined_trait('Test');
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined trait, false otherwise
         */
        public function is_defined_trait($name){
            $name = $this->normalize_trait($name);
            return isset($this->definitions['traits'][$name]);
        }
        /** Get defined trait of $name
         *
         * @example $sandbox->get_defined_trait('Test');
         *
         * @param   string          $name       String of trait $name to get
         *
         * @throws  Error           Throws an exception if an invalid trait name is requested
         *
         * @return  string          Returns string of defined trait value
         */
        public function get_defined_trait($name){
            $name = $this->normalize_trait($name);
            if(!isset($this->definitions['traits'][$name])){
                throw new Error("Could not get undefined trait: $name");
            }
            return $this->definitions['traits'][$name];
        }
        /** Undefine PHPSandbox trait
         *
         * You can pass a string of trait $name to undefine, or an array of trait names to undefine
         *
         * @example $sandbox->undefine_trait('Test');
         *
         * @example $sandbox->undefine_trait(array('Test', 'Test2'));
         *
         * @param   string|array          $name       String of trait name or an array of trait names to undefine
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_trait($name){
            if(is_array($name)){
                return $this->undefine_traits($name);
            }
            $name = $this->normalize_trait($name);
            if(isset($this->definitions['traits'][$name])){
                unset($this->definitions['traits'][$name]);
            }
            return $this;
        }
        /** Undefine PHPSandbox traits by array
         *
         * You can pass an array of trait names to undefine, or an empty array or null argument to undefine all traits
         *
         * @example $sandbox->undefine_traits(array('Test', 'Test2'));
         *
         * @example $sandbox->undefine_traits(); //WILL UNDEFINE ALL TRAITS!
         *
         * @param   array           $traits       Array of trait names to undefine. Passing an empty array or no argument will result in undefining all traits
         *
         * @return  PHPSandbox           Returns the PHPSandbox instance for chainability
         */
        public function undefine_traits(array $traits = array()){
            if(count($traits)){
                foreach($traits as $trait){
                    $this->undefine_trait($trait);
                }
            } else {
                $this->definitions['traits'] = array();
            }
            return $this;
        }
        /** Normalize function name.  This is an internal PHPSandbox function.
         *
         * @param   string|array          $name       String of the function $name, or array of strings to normalize
         *
         * @return  string|array          Returns the normalized function string or an array of normalized strings
         */
        protected function normalize_func($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_func($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize superglobal name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the superglobal $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized superglobal string or an array of normalized strings
         */
        protected function normalize_superglobal($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_superglobal($value);
                }
                return $name;
            }
            return strtoupper(ltrim($name, '_'));
        }
        /** Normalize magic constant name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the magic constant $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized magic constant string or an array of normalized strings
         */
        protected function normalize_magic_const($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_magic_const($value);
                }
                return $name;
            }
            return strtoupper(trim($name, '_'));
        }
        /** Normalize namespace name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the namespace $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized namespace string or an array of normalized strings
         */
        protected function normalize_namespace($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_namespace($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize alias name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the alias $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized alias string or an array of normalized strings
         */
        protected function normalize_alias($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_alias($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize use (or alias) name.  This is an internal PHPSandbox function.
         *
         * @alias   normalize_alias();
         *
         * @param   string|array           $name       String of the use (or alias) $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized use (or alias) string or an array of normalized strings
         */
        protected function normalize_use($name){
            return $this->normalize_alias($name);
        }
        /** Normalize class name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the class $name to normalize
         *
         * @return  string|array           Returns the normalized class string or an array of normalized strings
         */
        protected function normalize_class($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_class($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize interface name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the interface $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized interface string or an array of normalized strings
         */
        protected function normalize_interface($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_interface($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize trait name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the trait $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized trait string or an array of normalized strings
         */
        protected function normalize_trait($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_trait($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Normalize keyword name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the keyword $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized keyword string or an array of normalized strings
         */
        protected function normalize_keyword($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_keyword($value);
                }
                return $name;
            }
            $name = strtolower($name);
            switch($name){
                case 'die':
                    return 'exit';
                case 'include_once':
                case 'require':
                case 'require_once':
                    return 'include';
                case 'label':   //not a real keyword, only for defining purposes, can't use labels without goto
                    return 'goto';
                case 'print':   //for our purposes print is treated as functionally equivalent to echo
                    return 'echo';
                case 'else':    //no point in using ifs without else
                case 'elseif':  //no point in using ifs without elseif
                    return 'if';
                case 'case':
                    return 'switch';
                case 'catch':    //no point in using catch without try
                case 'finally':  //no point in using try, catch or finally without try
                    return 'try';
                case 'do':       //no point in using do without while
                    return 'while';
                case 'foreach':  //no point in using foreach without for
                    return 'for';
                case '__halt_compiler':
                    return 'halt';
                case 'alias':   //for consistency with alias and use descriptions
                    return 'use';
            }
            return $name;
        }
        /** Normalize operator name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the operator $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized operator string or an array of normalized strings
         */
        protected function normalize_operator($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_operator($value);
                }
                return $name;
            }
            $name = strtolower($name);
            if(strpos($name, '++') !== false){
                $name = (strpos($name, '++') === 0) ? '++n' : 'n++';
            } else if(strpos($name, '--') !== false){
                $name = (strpos($name, '--') === 0) ? '--n' : 'n--';
            } else if(strpos($name, '+') !== false && strlen($name) > 1){
                $name = '+n';
            } else if(strpos($name, '-') !== false && strlen($name) > 1){
                $name = '-n';
            }
            return $name;
        }
        /** Normalize primitive name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the primitive $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized primitive string or an array of normalized strings
         */
        protected function normalize_primitive($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_primitive($value);
                }
                return $name;
            }
            $name = strtolower($name);
            if($name == 'double'){
                $name = 'float';
            } else if($name == 'integer'){
                $name = 'int';
            }
            return $name;
        }
        /** Normalize type name.  This is an internal PHPSandbox function.
         *
         * @param   string|array          $name       String of the type $name, or array of strings to normalize
         *
         * @return  string|array          Returns the normalized type string or an array of normalized strings
         */
        protected function normalize_type($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalize_type($value);
                }
                return $name;
            }
            return strtolower($name);
        }
        /** Whitelist PHPSandbox definitions, such as functions, constants, classes, etc. to set
         *
         * You can pass an associative array of whitelist types and their names, or a string $type and array of $names, or pass a string of the $type and $name
         *
         * @example $sandbox->whitelist(array('functions' => array('test')));
         *
         * @example $sandbox->whitelist('functions', array('test'));
         *
         * @example $sandbox->whitelist('functions', 'test');
         *
         * @param   string|array        $type       Associative array or string of whitelist type to set
         * @param   string|array|null   $name       Array or string of whitelist name to set
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(isset($this->whitelist[$_type]) && is_string($name) && $name){
                        $this->whitelist[$_type][$name] = true;
                    } else if(isset($this->whitelist[$_type]) && is_array($name)){
                        foreach($name as $_name){
                            if(is_string($_name) && $_name){
                                $this->whitelist[$_type][$_name] = true;
                            }
                        }
                    }
                }
            } else if(isset($this->whitelist[$type]) && is_array($name)){
                foreach($name as $_name){
                    if(is_string($_name) && $_name){
                        $this->whitelist[$type][$_name] = true;
                    }
                }
            } else if(isset($this->whitelist[$type]) && is_string($name) && $name){
                $this->whitelist[$type][$name] = true;
            }
            return $this;
        }
        /** Blacklist PHPSandbox definitions, such as functions, constants, classes, etc. to set
         *
         * You can pass an associative array of blacklist types and their names, or a string $type and array of $names, or pass a string of the $type and $name
         *
         * @example $sandbox->blacklist(array('functions' => array('test')));
         *
         * @example $sandbox->blacklist('functions', array('test'));
         *
         * @example $sandbox->blacklist('functions', 'test');
         *
         * @param   string|array        $type       Associative array or string of blacklist type to set
         * @param   string|array|null   $name       Array or string of blacklist name to set
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(isset($this->blacklist[$_type]) && is_string($name) && $name){
                        $this->blacklist[$_type][$name] = true;
                    } else if(isset($this->blacklist[$_type]) && is_array($name)){
                        foreach($name as $_name){
                            if(is_string($_name) && $_name){
                                $this->blacklist[$_type][$_name] = true;
                            }
                        }
                    }
                }
            } else if(isset($this->blacklist[$type]) && is_array($name)){
                foreach($name as $_name){
                    if(is_string($_name) && $_name){
                        $this->blacklist[$type][$_name] = true;
                    }
                }
            } else if(isset($this->blacklist[$type]) && is_string($name) && $name){
                $this->blacklist[$type][$name] = true;
            }
            return $this;
        }
        /** Remove PHPSandbox definitions, such as functions, constants, classes, etc. from whitelist
         *
         * You can pass an associative array of whitelist types and their names, or a string $type and array of $names, or pass a string of the $type and $name to unset
         *
         * @example $sandbox->dewhitelist(array('functions' => array('test')));
         *
         * @example $sandbox->dewhitelist('functions', array('test'));
         *
         * @example $sandbox->dewhitelist('functions', 'test');
         *
         * @param   string|array        $type       Associative array or string of whitelist type to unset
         * @param   string|array|null   $name       Array or string of whitelist name to unset
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist($type, $name){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(isset($this->whitelist[$_type]) && is_string($name) && $name && isset($this->whitelist[$_type][$name])){
                        unset($this->whitelist[$_type][$name]);
                    } else if(isset($this->whitelist[$_type]) && is_array($name)){
                        foreach($name as $_name){
                            if(is_string($_name) && $_name && isset($this->whitelist[$_type][$_name])){
                                unset($this->whitelist[$_type][$_name]);
                            }
                        }
                    }
                }
            } else if(isset($this->whitelist[$type]) && is_string($name) && $name && isset($this->whitelist[$type][$name])){
                unset($this->whitelist[$type][$name]);
            }
            return $this;
        }
        /** Remove PHPSandbox definitions, such as functions, constants, classes, etc. from blacklist
         *
         * You can pass an associative array of blacklist types and their names, or a string $type and array of $names, or pass a string of the $type and $name to unset
         *
         * @example $sandbox->deblacklist(array('functions' => array('test')));
         *
         * @example $sandbox->deblacklist('functions', array('test'));
         *
         * @example $sandbox->deblacklist('functions', 'test');
         *
         * @param   string|array        $type       Associative array or string of blacklist type to unset
         * @param   string|array|null   $name       Array or string of blacklist name to unset
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist($type, $name){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(isset($this->blacklist[$_type]) && is_string($name) && $name && isset($this->blacklist[$_type][$name])){
                        unset($this->blacklist[$_type][$name]);
                    } else if(isset($this->blacklist[$_type]) && is_array($name)){
                        foreach($name as $_name){
                            if(is_string($_name) && $_name && isset($this->blacklist[$_type][$_name])){
                                unset($this->blacklist[$_type][$_name]);
                            }
                        }
                    }
                }
            } else if(isset($this->blacklist[$type]) && is_string($name) && $name && isset($this->blacklist[$type][$name])){
                unset($this->blacklist[$type][$name]);
            }
            return $this;
        }
        /** Query whether PHPSandbox instance has whitelist type
         *
         * @example $sandbox->has_whitelist('functions'); //returns number of whitelisted functions, or zero if none whitelisted
         *
         * @param   string        $type     The whitelist type to query
         *
         * @return  int           Returns the number of whitelists this instance has defined
         */
        public function has_whitelist($type){
            return count($this->whitelist[$type]);
        }
        /** Query whether PHPSandbox instance has blacklist type.
         *
         * @example $sandbox->has_blacklist('functions'); //returns number of blacklisted functions, or zero if none blacklisted
         *
         * @param   string        $type     The blacklist type to query
         *
         * @return  int           Returns the number of blacklists this instance has defined
         */
        public function has_blacklist($type){
            return count($this->blacklist[$type]);
        }
        /** Check if PHPSandbox instance has whitelist type and name set
         *
         * @example $sandbox->is_whitelisted('functions', 'test');
         *
         * @param   string          $type       String of whitelist $type to query
         * @param   string          $name       String of whitelist $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted $type and $name, false otherwise
         */
        public function is_whitelisted($type, $name){
            return isset($this->whitelist[$type][$name]);
        }
        /** Check if PHPSandbox instance has blacklist type and name set
         *
         * @example $sandbox->is_blacklisted('functions', 'test');
         *
         * @param   string          $type       String of blacklist $type to query
         * @param   string          $name       String of blacklist $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted $type and $name, false otherwise
         */
        public function is_blacklisted($type, $name){
            return isset($this->blacklist[$type][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted functions.
         *
         * @example $sandbox->has_whitelist_funcs(); //returns number of whitelisted functions, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted functions this instance has defined
         */
        public function has_whitelist_funcs(){
            return count($this->whitelist['functions']);
        }
        /** Query whether PHPSandbox instance has blacklisted functions.
         *
         * @example $sandbox->has_blacklist_funcs(); //returns number of blacklisted functions, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted functions this instance has defined
         */
        public function has_blacklist_funcs(){
            return count($this->blacklist['functions']);
        }
        /** Check if PHPSandbox instance has whitelisted function name set
         *
         * @example $sandbox->is_whitelisted_func('test');
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted function $name, false otherwise
         */
        public function is_whitelisted_func($name){
            $name = $this->normalize_func($name);
            return isset($this->whitelist['functions'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted function name set
         *
         * @example $sandbox->is_blacklisted_func('test');
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted function $name, false otherwise
         */
        public function is_blacklisted_func($name){
            $name = $this->normalize_func($name);
            return isset($this->blacklist['functions'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted variables.
         *
         * @example $sandbox->has_whitelist_vars(); //returns number of whitelisted variables, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted variables this instance has defined
         */
        public function has_whitelist_vars(){
            return count($this->whitelist['variables']);
        }
        /** Query whether PHPSandbox instance has blacklisted variables.
         *
         * @example $sandbox->has_blacklist_vars(); //returns number of blacklisted variables, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted variables this instance has defined
         */
        public function has_blacklist_vars(){
            return count($this->blacklist['variables']);
        }
        /** Check if PHPSandbox instance has whitelisted variable name set
         *
         * @example $sandbox->is_whitelisted_var('test');
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted variable $name, false otherwise
         */
        public function is_whitelisted_var($name){
            return isset($this->whitelist['variables'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted variable name set
         *
         * @example $sandbox->is_blacklisted_var('test');
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted variable $name, false otherwise
         */
        public function is_blacklisted_var($name){
            return isset($this->blacklist['variables'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted globals.
         *
         * @example $sandbox->has_whitelist_globals(); //returns number of whitelisted globals, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted globals this instance has defined
         */
        public function has_whitelist_globals(){
            return count($this->whitelist['globals']);
        }
        /** Query whether PHPSandbox instance has blacklisted globals.
         *
         * @example $sandbox->has_blacklist_globals(); //returns number of blacklisted globals, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted globals this instance has defined
         */
        public function has_blacklist_globals(){
            return count($this->blacklist['globals']);
        }
        /** Check if PHPSandbox instance has whitelisted global name set
         *
         * @example $sandbox->is_whitelisted_global('test');
         *
         * @param   string          $name       String of global $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted global $name, false otherwise
         */
        public function is_whitelisted_global($name){
            return isset($this->whitelist['globals'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted global name set
         *
         * @example $sandbox->is_blacklisted_global('test');
         *
         * @param   string          $name       String of global $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted global $name, false otherwise
         */
        public function is_blacklisted_global($name){
            return isset($this->blacklist['globals'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted superglobals, or superglobal keys
         *
         * @example $sandbox->has_whitelist_superglobals(); //returns number of whitelisted superglobals, or zero if none whitelisted
         *
         * @example $sandbox->has_whitelist_superglobals('_GET'); //returns number of whitelisted superglobal keys, or zero if none whitelisted
         *
         * @param   string        $name     The whitelist superglobal key to query
         *
         * @return  int           Returns the number of whitelisted superglobals or superglobal keys this instance has defined
         */
        public function has_whitelist_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            return $name !== null ? (isset($this->whitelist['superglobals'][$name]) ? count($this->whitelist['superglobals'][$name]) : 0) : count($this->whitelist['superglobals']);
        }
        /** Query whether PHPSandbox instance has blacklisted superglobals, or superglobal keys
         *
         * @example $sandbox->has_blacklist_superglobals(); //returns number of blacklisted superglobals, or zero if none blacklisted
         *
         * @example $sandbox->has_blacklist_superglobals('_GET'); //returns number of blacklisted superglobal keys, or zero if none blacklisted
         *
         * @param   string        $name     The blacklist superglobal key to query
         *
         * @return  int           Returns the number of blacklisted superglobals or superglobal keys this instance has defined
         */
        public function has_blacklist_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            return $name !== null ? (isset($this->blacklist['superglobals'][$name]) ? count($this->blacklist['superglobals'][$name]) : 0) : count($this->blacklist['superglobals']);
        }
        /** Check if PHPSandbox instance has whitelisted superglobal or superglobal key set
         *
         * @example $sandbox->is_whitelisted_superglobal('_GET');
         *
         * @example $sandbox->is_whitelisted_superglobal('_GET', 'page');
         *
         * @param   string          $name       String of whitelisted superglobal $name to query
         * @param   string          $key        String of whitelisted superglobal $key to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted superglobal key or superglobal, false otherwise
         */
        public function is_whitelisted_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->whitelist['superglobals'][$name][$key]) : isset($this->whitelist['superglobals'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted superglobal or superglobal key set
         *
         * @example $sandbox->is_blacklisted_superglobal('_GET');
         *
         * @example $sandbox->is_blacklisted_superglobal('_GET', 'page');
         *
         * @param   string          $name       String of blacklisted superglobal $name to query
         * @param   string          $key        String of blacklisted superglobal $key to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted superglobal key or superglobal, false otherwise
         */
        public function is_blacklisted_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->blacklist['superglobals'][$name][$key]) : isset($this->blacklist['superglobals'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted constants.
         *
         * @example $sandbox->has_whitelist_consts(); //returns number of whitelisted constants, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted constants this instance has defined
         */
        public function has_whitelist_consts(){
            return count($this->whitelist['constants']);
        }
        /** Query whether PHPSandbox instance has blacklisted constants.
         *
         * @example $sandbox->has_blacklist_consts(); //returns number of blacklisted constants, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted constants this instance has defined
         */
        public function has_blacklist_consts(){
            return count($this->blacklist['constants']);
        }
        /** Check if PHPSandbox instance has whitelisted constant name set
         *
         * @example $sandbox->is_whitelisted_const('TEST');
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted constant $name, false otherwise
         */
        public function is_whitelisted_const($name){
            return isset($this->whitelist['constants'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted constant name set
         *
         * @example $sandbox->is_blacklisted_const('TEST');
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted constant $name, false otherwise
         */
        public function is_blacklisted_const($name){
            return isset($this->blacklist['constants'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted magic constants.
         *
         * @example $sandbox->has_whitelist_magic_consts(); //returns number of whitelisted magic constants, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted magic constants this instance has defined
         */
        public function has_whitelist_magic_consts(){
            return count($this->whitelist['magic_constants']);
        }
        /** Query whether PHPSandbox instance has blacklisted magic constants.
         *
         * @example $sandbox->has_blacklist_magic_consts(); //returns number of blacklisted magic constants, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted magic constants this instance has defined
         */
        public function has_blacklist_magic_consts(){
            return count($this->blacklist['magic_constants']);
        }
        /** Check if PHPSandbox instance has whitelisted magic constant name set
         *
         * @example $sandbox->is_whitelisted_magic_const('__LINE__');
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted magic constant $name, false otherwise
         */
        public function is_whitelisted_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->whitelist['magic_constants'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted magic constant name set
         *
         * @example $sandbox->is_blacklisted_magic_const('__LINE__');
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted magic constant $name, false otherwise
         */
        public function is_blacklisted_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->blacklist['magic_constants'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted namespaces.
         *
         * @example $sandbox->has_whitelist_namespaces(); //returns number of whitelisted namespaces, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted namespaces this instance has defined
         */
        public function has_whitelist_namespaces(){
            return count($this->whitelist['namespaces']);
        }
        /** Query whether PHPSandbox instance has blacklisted namespaces.
         *
         * @example $sandbox->has_blacklist_namespaces(); //returns number of blacklisted namespaces, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted namespaces this instance has defined
         */
        public function has_blacklist_namespaces(){
            return count($this->blacklist['namespaces']);
        }
        /** Check if PHPSandbox instance has whitelisted namespace name set
         *
         * @example $sandbox->is_whitelisted_namespace('Test');
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted namespace $name, false otherwise
         */
        public function is_whitelisted_namespace($name){
            $name = $this->normalize_namespace($name);
            return isset($this->whitelist['namespaces'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted namespace name set
         *
         * @example $sandbox->is_blacklisted_namespace('Test');
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted namespace $name, false otherwise
         */
        public function is_blacklisted_namespace($name){
            $name = $this->normalize_namespace($name);
            return isset($this->blacklist['namespaces'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted aliases.
         *
         * @example $sandbox->has_whitelist_aliases(); //returns number of whitelisted aliases, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted aliases this instance has defined
         */
        public function has_whitelist_aliases(){
            return count($this->whitelist['aliases']);
        }
        /** Query whether PHPSandbox instance has blacklisted aliases.
         *
         * @example $sandbox->has_blacklist_aliases(); //returns number of blacklisted aliases, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted aliases this instance has defined
         */
        public function has_blacklist_aliases(){
            return count($this->blacklist['aliases']);
        }
        /** Check if PHPSandbox instance has whitelisted alias name set
         *
         * @example $sandbox->is_whitelisted_alias('Test');
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted alias $name, false otherwise
         */
        public function is_whitelisted_alias($name){
            $name = $this->normalize_alias($name);
            return isset($this->whitelist['aliases'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted alias name set
         *
         * @example $sandbox->is_blacklisted_alias('Test');
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted alias $name, false otherwise
         */
        public function is_blacklisted_alias($name){
            $name = $this->normalize_alias($name);
            return isset($this->blacklist['aliases'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted uses (or aliases.)
         *
         * @alias   has_whitelist_aliases();
         *
         * @example $sandbox->has_whitelist_uses(); //returns number of whitelisted uses (or aliases) or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted uses (or aliases) this instance has defined
         */
        public function has_whitelist_uses(){
            return $this->has_whitelist_aliases();
        }
        /** Query whether PHPSandbox instance has blacklisted uses (or aliases.)
         *
         * @alias   has_blacklist_aliases();
         *
         * @example $sandbox->has_blacklist_uses(); //returns number of blacklisted uses (or aliases) or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted uses (or aliases) this instance has defined
         */
        public function has_blacklist_uses(){
            return $this->has_blacklist_aliases();
        }
        /** Check if PHPSandbox instance has whitelisted use (or alias) name set
         *
         * @alias   is_whitelisted_alias();
         *
         * @example $sandbox->is_whitelisted_use('Test');
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted use (or alias) $name, false otherwise
         */
        public function is_whitelisted_use($name){
            return $this->is_whitelisted_alias($name);
        }
        /** Check if PHPSandbox instance has blacklisted use (or alias) name set
         *
         * @alias   is_blacklisted_alias();
         *
         * @example $sandbox->is_blacklisted_use('Test');
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted use (or alias) $name, false otherwise
         */
        public function is_blacklisted_use($name){
            return $this->is_blacklisted_alias($name);
        }
        /** Query whether PHPSandbox instance has whitelisted classes.
         *
         * @example $sandbox->has_whitelist_classes(); //returns number of whitelisted classes, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted classes this instance has defined
         */
        public function has_whitelist_classes(){
            return count($this->whitelist['classes']);
        }
        /** Query whether PHPSandbox instance has blacklisted classes.
         *
         * @example $sandbox->has_blacklist_classes(); //returns number of blacklisted classes, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted classes this instance has defined
         */
        public function has_blacklist_classes(){
            return count($this->blacklist['classes']);
        }
        /** Check if PHPSandbox instance has whitelisted class name set
         *
         * @example $sandbox->is_whitelisted_class('Test');
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted class $name, false otherwise
         */
        public function is_whitelisted_class($name){
            $name = $this->normalize_class($name);
            return isset($this->whitelist['classes'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted class name set
         *
         * @example $sandbox->is_blacklisted_class('Test');
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted class $name, false otherwise
         */
        public function is_blacklisted_class($name){
            $name = $this->normalize_class($name);
            return isset($this->blacklist['classes'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted interfaces.
         *
         * @example $sandbox->has_whitelist_interfaces(); //returns number of whitelisted interfaces, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted interfaces this instance has defined
         */
        public function has_whitelist_interfaces(){
            return count($this->whitelist['interfaces']);
        }
        /** Query whether PHPSandbox instance has blacklisted interfaces.
         *
         * @example $sandbox->has_blacklist_interfaces(); //returns number of blacklisted interfaces, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted interfaces this instance has defined
         */
        public function has_blacklist_interfaces(){
            return count($this->blacklist['interfaces']);
        }
        /** Check if PHPSandbox instance has whitelisted interface name set
         *
         * @example $sandbox->is_whitelisted_interface('Test');
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted interface $name, false otherwise
         */
        public function is_whitelisted_interface($name){
            $name = $this->normalize_interface($name);
            return isset($this->whitelist['interfaces'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted interface name set
         *
         * @example $sandbox->is_blacklisted_interface('Test');
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted interface $name, false otherwise
         */
        public function is_blacklisted_interface($name){
            $name = $this->normalize_interface($name);
            return isset($this->blacklist['interfaces'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted traits.
         *
         * @example $sandbox->has_whitelist_traits(); //returns number of whitelisted traits, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted traits this instance has defined
         */
        public function has_whitelist_traits(){
            return count($this->whitelist['traits']);
        }
        /** Query whether PHPSandbox instance has blacklisted traits.
         *
         * @example $sandbox->has_blacklist_traits(); //returns number of blacklisted traits, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted traits this instance has defined
         */
        public function has_blacklist_traits(){
            return count($this->blacklist['traits']);
        }
        /** Check if PHPSandbox instance has whitelisted trait name set
         *
         * @example $sandbox->is_whitelisted_trait('Test');
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted trait $name, false otherwise
         */
        public function is_whitelisted_trait($name){
            $name = $this->normalize_trait($name);
            return isset($this->whitelist['traits'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted trait name set
         *
         * @example $sandbox->is_blacklisted_trait('Test');
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted trait $name, false otherwise
         */
        public function is_blacklisted_trait($name){
            $name = $this->normalize_trait($name);
            return isset($this->blacklist['traits'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted keywords.
         *
         * @example $sandbox->has_whitelist_keywords(); //returns number of whitelisted keywords, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted keywords this instance has defined
         */
        public function has_whitelist_keywords(){
            return count($this->whitelist['keywords']);
        }
        /** Query whether PHPSandbox instance has blacklisted keywords.
         *
         * @example $sandbox->has_blacklist_keywords(); //returns number of blacklisted keywords, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted keywords this instance has defined
         */
        public function has_blacklist_keywords(){
            return count($this->blacklist['keywords']);
        }
        /** Check if PHPSandbox instance has whitelisted keyword name set
         *
         * @example $sandbox->is_whitelisted_keyword('echo');
         *
         * @param   string          $name       String of keyword $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted keyword $name, false otherwise
         */
        public function is_whitelisted_keyword($name){
            $name = $this->normalize_keyword($name);
            return isset($this->whitelist['keywords'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted keyword name set
         *
         * @example $sandbox->is_blacklisted_keyword('echo');
         *
         * @param   string          $name       String of keyword $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted keyword $name, false otherwise
         */
        public function is_blacklisted_keyword($name){
            $name = $this->normalize_keyword($name);
            return isset($this->blacklist['keywords'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted operators.
         *
         * @example $sandbox->has_whitelist_operators(); //returns number of whitelisted operators, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted operators this instance has defined
         */
        public function has_whitelist_operators(){
            return count($this->whitelist['operators']);
        }
        /** Query whether PHPSandbox instance has blacklisted operators.
         *
         * @example $sandbox->has_blacklist_operators(); //returns number of blacklisted operators, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted operators this instance has defined
         */
        public function has_blacklist_operators(){
            return count($this->blacklist['operators']);
        }
        /** Check if PHPSandbox instance has whitelisted operator name set
         *
         * @example $sandbox->is_whitelisted_operator('+');
         *
         * @param   string          $name       String of operator $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted operator $name, false otherwise
         */
        public function is_whitelisted_operator($name){
            $name = $this->normalize_operator($name);
            return isset($this->whitelist['operators'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted operator name set
         *
         * @example $sandbox->is_blacklisted_operator('+');
         *
         * @param   string          $name       String of operator $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted operator $name, false otherwise
         */
        public function is_blacklisted_operator($name){
            $name = $this->normalize_operator($name);
            return isset($this->blacklist['operators'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted primitives.
         *
         * @example $sandbox->has_whitelist_primitives(); //returns number of whitelisted primitives, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted primitives this instance has defined
         */
        public function has_whitelist_primitives(){
            return count($this->whitelist['primitives']);
        }
        /** Query whether PHPSandbox instance has blacklisted primitives.
         *
         * @example $sandbox->has_blacklist_primitives(); //returns number of blacklisted primitives, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted primitives this instance has defined
         */
        public function has_blacklist_primitives(){
            return count($this->blacklist['primitives']);
        }
        /** Check if PHPSandbox instance has whitelisted primitive name set
         *
         * @example $sandbox->is_whitelisted_primitive('array');
         *
         * @param   string          $name       String of primitive $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted primitive $name, false otherwise
         */
        public function is_whitelisted_primitive($name){
            $name = $this->normalize_primitive($name);
            return isset($this->whitelist['primitives'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted primitive name set
         *
         * @example $sandbox->is_blacklisted_primitive('array');
         *
         * @param   string          $name       String of primitive $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted primitive $name, false otherwise
         */
        public function is_blacklisted_primitive($name){
            $name = $this->normalize_primitive($name);
            return isset($this->blacklist['primitives'][$name]);
        }
        /** Query whether PHPSandbox instance has whitelisted types.
         *
         * @example $sandbox->has_whitelist_types(); //returns number of whitelisted types, or zero if none whitelisted
         *
         * @return  int           Returns the number of whitelisted types this instance has defined
         */
        public function has_whitelist_types(){
            return count($this->whitelist['types']);
        }
        /** Query whether PHPSandbox instance has blacklisted types.
         *
         * @example $sandbox->has_blacklist_types(); //returns number of blacklisted types, or zero if none blacklisted
         *
         * @return  int           Returns the number of blacklisted types this instance has defined
         */
        public function has_blacklist_types(){
            return count($this->blacklist['types']);
        }
        /** Check if PHPSandbox instance has whitelisted type name set
         *
         * @example $sandbox->is_whitelisted_type('array');
         *
         * @param   string          $name       String of type $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted type $name, false otherwise
         */
        public function is_whitelisted_type($name){
            $name = $this->normalize_type($name);
            return isset($this->whitelist['types'][$name]);
        }
        /** Check if PHPSandbox instance has blacklisted type name set
         *
         * @example $sandbox->is_blacklisted_type('array');
         *
         * @param   string          $name       String of type $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted type $name, false otherwise
         */
        public function is_blacklisted_type($name){
            $name = $this->normalize_type($name);
            return isset($this->blacklist['types'][$name]);
        }
        /** Whitelist function
         *
         * You can pass a string of the function name, or pass an array of function names to whitelist
         *
         * @example $sandbox->whitelist_func('var_dump');
         *
         * @example $sandbox->whitelist_func(array('var_dump', 'print_r'));
         *
         * @param   string|array        $name       String of function name, or array of function names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_func($name){
            $name = $this->normalize_func($name);
            return $this->whitelist('functions', $name);
        }
        /** Blacklist function
         *
         * You can pass a string of the function name, or pass an array of function names to blacklist
         *
         * @example $sandbox->blacklist_func('var_dump');
         *
         * @example $sandbox->blacklist_func(array('var_dump', 'print_r'));
         *
         * @param   string|array        $name       String of function name, or array of function names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_func($name){
            $name = $this->normalize_func($name);
            return $this->blacklist('functions', $name);
        }
        /** Remove function from whitelist
         *
         * You can pass a string of the function name, or pass an array of function names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_func('var_dump');
         *
         * @example $sandbox->dewhitelist_func(array('var_dump', 'print_r'));
         *
         * @param   string|array        $name       String of function name or array of function names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_func($name){
            $name = $this->normalize_func($name);
            return $this->dewhitelist('functions', $name);
        }
        /** Remove function from blacklist
         *
         * You can pass a string of the function name, or pass an array of function names to remove from blacklist
         *
         * @example $sandbox->deblacklist_func('var_dump');
         *
         * @example $sandbox->deblacklist_func(array('var_dump', 'print_r'));
         *
         * @param   string|array        $name       String of function name or array of function names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_func($name){
            $name = $this->normalize_func($name);
            return $this->deblacklist('functions', $name);
        }
        /** Whitelist variable
         *
         * You can pass a string of variable name, or pass an array of the variable names to whitelist
         *
         * @example $sandbox->whitelist_var('a');
         *
         * @example $sandbox->whitelist_var(array('a', 'b'));
         *
         * @param   string|array        $name       String of variable name or array of variable names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_var($name){
            return $this->whitelist('variables', $name);
        }
        /** Blacklist variable
         *
         * You can pass a string of variable name, or pass an array of the variable names to blacklist
         *
         * @example $sandbox->blacklist_var('a');
         *
         * @example $sandbox->blacklist_var(array('a', 'b'));
         *
         * @param   string|array        $name       String of variable name or array of variable names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_var($name){
            return $this->blacklist('variables', $name);
        }
        /** Remove variable from whitelist
         *
         * You can pass a string of variable name, or pass an array of the variable names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_var('a');
         *
         * @example $sandbox->dewhitelist_var(array('a', 'b'));
         *
         * @param   string|array        $name       String of variable name or array of variable names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_var($name){
            return $this->dewhitelist('variables', $name);
        }
        /** Remove function from blacklist
         *
         * You can pass a string of variable name, or pass an array of the variable names to remove from blacklist
         *
         * @example $sandbox->deblacklist_var('a');
         *
         * @example $sandbox->deblacklist_var(array('a', 'b'));
         *
         * @param   string|array        $name       String of variable name or array of variable names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_var($name){
            return $this->deblacklist('variables', $name);
        }
        /** Whitelist global
         *
         * You can pass a string of global name, or pass an array of the global names to whitelist
         *
         * @example $sandbox->whitelist_global('a');
         *
         * @example $sandbox->whitelist_global(array('a', 'b'));
         *
         * @param   string|array        $name       String of global name or array of global names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_global($name){
            return $this->whitelist('globals', $name);
        }
        /** Blacklist global
         *
         * You can pass a string of global name, or pass an array of the global names to blacklist
         *
         * @example $sandbox->blacklist_global('a');
         *
         * @example $sandbox->blacklist_global(array('a', 'b'));
         *
         * @param   string|array        $name       String of global name or array of global names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_global($name){
            return $this->blacklist('globals', $name);
        }
        /** Remove global from whitelist
         *
         * You can pass a string of global name, or pass an array of the global names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_global('a');
         *
         * @example $sandbox->dewhitelist_global(array('a', 'b'));
         *
         * @param   string|array        $name       String of global name or array of global names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_global($name){
            return $this->dewhitelist('globals', $name);
        }
        /** Remove global from blacklist
         *
         * You can pass a string of global name, or pass an array of the global names to remove from blacklist
         *
         * @example $sandbox->deblacklist_global('a');
         *
         * @example $sandbox->deblacklist_global(array('a', 'b'));
         *
         * @param   string|array        $name       String of global name or array of global names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_global($name){
            return $this->deblacklist('globals', $name);
        }
        /** Whitelist superglobal or superglobal key
         *
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to whitelist
         *
         * @example $sandbox->whitelist_superglobal('_GET');
         *
         * @example $sandbox->whitelist_superglobal('_GET', 'page');
         *
         * @example $sandbox->whitelist_superglobal(array('_GET', '_POST'));
         *
         * @example $sandbox->whitelist_superglobal(array('_GET' => 'page'));
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to whitelist
         * @param   string              $key        String of superglobal key to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(!isset($this->whitelist['superglobals'][$name]) && is_string($name) && $name){
                $this->whitelist['superglobals'][$name] = array();
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(is_string($key) && $key){
                            $this->whitelist['superglobals'][$key] = array();
                        }
                    } else {
                        $_name = $this->normalize_superglobal($_name);
                        if(!isset($this->whitelist['superglobals'][$_name]) && is_string($_name) && $_name){
                            $this->whitelist['superglobals'][$_name] = array();
                        }
                        if(isset($this->whitelist['superglobals'][$_name]) && is_string($key) && $key){
                            $this->whitelist['superglobals'][$_name][$key] = true;
                        } else if(isset($this->whitelist['superglobals'][$_name]) && is_array($key)){
                            foreach($key as $_key){
                                if(is_string($_key) && $_key){
                                    $this->whitelist['superglobals'][$_name][$_name] = true;
                                }
                            }
                        }
                    }
                }
            } else if(isset($this->whitelist['superglobals'][$name]) && is_array($key)){
                foreach($key as $_key){
                    if(is_string($_key) && $_key){
                        $this->whitelist['superglobals'][$name][$_key] = true;
                    }
                }
            } else if(isset($this->whitelist['superglobals'][$name]) && is_string($key) && $key){
                $this->whitelist['superglobals'][$name][$key] = true;
            }
            return $this;
        }
        /** Blacklist superglobal or superglobal key
         **
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to blacklist
         *
         * @example $sandbox->blacklist_superglobal('_GET');
         *
         * @example $sandbox->blacklist_superglobal('_GET', 'page');
         *
         * @example $sandbox->blacklist_superglobal(array('_GET', '_POST'));
         *
         * @example $sandbox->blacklist_superglobal(array('_GET' => 'page'));
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to blacklist
         * @param   string              $key        String of superglobal key to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(!isset($this->blacklist['superglobals'][$name]) && is_string($name) && $name){
                $this->blacklist['superglobals'][$name] = array();
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(is_string($key) && $key){
                            $this->blacklist['superglobals'][$key] = array();
                        }
                    } else {
                        $_name = $this->normalize_superglobal($_name);
                        if(!isset($this->blacklist['superglobals'][$_name]) && is_string($_name) && $_name){
                            $this->blacklist['superglobals'][$_name] = array();
                        }
                        if(isset($this->blacklist['superglobals'][$_name]) && is_string($key) && $key){
                            $this->blacklist['superglobals'][$_name][$key] = true;
                        } else if(isset($this->blacklist['superglobals'][$_name]) && is_array($key)){
                            foreach($key as $_key){
                                if(is_string($_key) && $_key){
                                    $this->blacklist['superglobals'][$_name][$_name] = true;
                                }
                            }
                        }
                    }
                }
            } else if(isset($this->blacklist['superglobals'][$name]) && is_array($key)){
                foreach($key as $_key){
                    if(is_string($_key) && $_key){
                        $this->blacklist['superglobals'][$name][$_key] = true;
                    }
                }
            } else if(isset($this->blacklist['superglobals'][$name]) && is_string($key) && $key){
                $this->blacklist['superglobals'][$name][$key] = true;
            }
            return $this;
        }
        /** Remove superglobal or superglobal key from whitelist
         **
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to remove from whitelist
         *
         * @example $sandbox->dewhitelist_superglobal('_GET');
         *
         * @example $sandbox->dewhitelist_superglobal('_GET', 'page');
         *
         * @example $sandbox->dewhitelist_superglobal(array('_GET', '_POST'));
         *
         * @example $sandbox->dewhitelist_superglobal(array('_GET' => 'page'));
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to remove from whitelist
         * @param   string              $key        String of superglobal key to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(isset($this->whitelist['superglobals'][$key])){
                            $this->whitelist['superglobals'][$key] = array();
                        }
                    } else if(isset($this->whitelist['superglobals'][$_name]) && is_string($key) && $key && isset($this->whitelist['superglobals'][$_name][$key])){
                        unset($this->whitelist['superglobals'][$_name][$key]);
                    } else if(isset($this->whitelist['superglobals'][$_name]) && is_array($key)){
                        foreach($key as $_key){
                            if(is_string($_key) && $_key && isset($this->whitelist['superglobals'][$_name][$_key])){
                                unset($this->whitelist['superglobals'][$_name][$_key]);
                            }
                        }
                    }
                }
            } else if(isset($this->whitelist['superglobals'][$name]) && is_string($key) && $key && isset($this->whitelist['superglobals'][$name][$key])){
                unset($this->whitelist['superglobals'][$name][$key]);
            } else if(isset($this->whitelist['superglobals'][$name]) && is_array($key)){
                foreach($key as $_key){
                    if(is_string($_key) && $_key && isset($this->whitelist['superglobals'][$name][$_key])){
                        unset($this->whitelist['superglobals'][$name][$_key]);
                    }
                }
            } else if(isset($this->whitelist['superglobals'][$name])){
                unset($this->whitelist['superglobals'][$name]);
            }
            return $this;
        }
        /** Remove superglobal or superglobal key from blacklist
         **
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to remove from blacklist
         *
         * @example $sandbox->deblacklist_superglobal('_GET');
         *
         * @example $sandbox->deblacklist_superglobal('_GET', 'page');
         *
         * @example $sandbox->deblacklist_superglobal(array('_GET', '_POST'));
         *
         * @example $sandbox->deblacklist_superglobal(array('_GET' => 'page'));
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to remove from blacklist
         * @param   string              $key        String of superglobal key to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(isset($this->blacklist['superglobals'][$key])){
                            $this->blacklist['superglobals'][$key] = array();
                        }
                    } else if(isset($this->blacklist['superglobals'][$_name]) && is_string($key) && $key && isset($this->blacklist['superglobals'][$_name][$key])){
                        unset($this->blacklist['superglobals'][$_name][$key]);
                    } else if(isset($this->blacklist['superglobals'][$_name]) && is_array($key)){
                        foreach($key as $_key){
                            if(is_string($_key) && $_key && isset($this->blacklist['superglobals'][$_name][$_key])){
                                unset($this->blacklist['superglobals'][$_name][$_key]);
                            }
                        }
                    }
                }
            } else if(isset($this->blacklist['superglobals'][$name]) && is_string($key) && $key && isset($this->blacklist['superglobals'][$name][$key])){
                unset($this->blacklist['superglobals'][$name][$key]);
            } else if(isset($this->blacklist['superglobals'][$name]) && is_array($key)){
                foreach($key as $_key){
                    if(is_string($_key) && $_key && isset($this->blacklist['superglobals'][$name][$_key])){
                        unset($this->blacklist['superglobals'][$name][$_key]);
                    }
                }
            } else if(isset($this->blacklist['superglobals'][$name])){
                unset($this->blacklist['superglobals'][$name]);
            }
            return $this;
        }
        /** Whitelist constant
         *
         * You can pass a string of constant name, or pass an array of the constant names to whitelist
         *
         * @example $sandbox->whitelist_const('FOO');
         *
         * @example $sandbox->whitelist_const(array('FOO', 'BAR'));
         *
         * @param   string|array        $name       String of constant name or array of constant names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_const($name){
            return $this->whitelist('constants', $name);
        }
        /** Blacklist constant
         *
         * You can pass a string of constant name, or pass an array of the constant names to blacklist
         *
         * @example $sandbox->blacklist_const('FOO');
         *
         * @example $sandbox->blacklist_const(array('FOO', 'BAR'));
         *
         * @param   string|array        $name       String of constant name or array of constant names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_const($name){
            return $this->blacklist('constants', $name);
        }
        /** Remove constant from whitelist
         *
         * You can pass a string of constant name, or pass an array of the constant names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_const('FOO');
         *
         * @example $sandbox->dewhitelist_const(array('FOO', 'BAR'));
         *
         * @param   string|array        $name       String of constant name or array of constant names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_const($name){
            return $this->dewhitelist('constants', $name);
        }
        /** Remove constant from blacklist
         *
         * You can pass a string of constant name, or pass an array of the constant names to remove from blacklist
         *
         * @example $sandbox->deblacklist_const('FOO');
         *
         * @example $sandbox->deblacklist_const(array('FOO', 'BAR'));
         *
         * @param   string|array        $name       String of constant name or array of constant names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_const($name){
            return $this->deblacklist('constants', $name);
        }
        /** Whitelist magic constant
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to whitelist
         *
         * @example $sandbox->whitelist_magic_const('__LINE__');
         *
         * @example $sandbox->whitelist_magic_const(array('__LINE__', '__FILE__'));
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->whitelist('magic_constants', $name);
        }
        /** Blacklist magic constant
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to blacklist
         *
         * @example $sandbox->blacklist_magic_const('__LINE__');
         *
         * @example $sandbox->blacklist_magic_const(array('__LINE__', '__FILE__'));
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->blacklist('magic_constants', $name);
        }
        /** Remove magic constant from whitelist
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_magic_const('__LINE__');
         *
         * @example $sandbox->dewhitelist_magic_const(array('__LINE__', '__FILE__'));
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->dewhitelist('magic_constants', $name);
        }
        /** Remove magic constant from blacklist
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to remove from blacklist
         *
         * @example $sandbox->deblacklist_magic_const('__LINE__');
         *
         * @example $sandbox->deblacklist_magic_const(array('__LINE__', '__FILE__'));
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->deblacklist('magic_constants', $name);
        }
        /** Whitelist namespace
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to whitelist
         *
         * @example $sandbox->whitelist_namespace('Foo');
         *
         * @example $sandbox->whitelist_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->whitelist('namespaces', $name);
        }
        /** Blacklist namespace
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to blacklist
         *
         * @example $sandbox->blacklist_namespace('Foo');
         *
         * @example $sandbox->blacklist_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->blacklist('namespaces', $name);
        }
        /** Remove namespace from whitelist
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_namespace('Foo');
         *
         * @example $sandbox->dewhitelist_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->dewhitelist('namespaces', $name);
        }
        /** Remove namespace from blacklist
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to remove from blacklist
         *
         * @example $sandbox->deblacklist_namespace('Foo');
         *
         * @example $sandbox->deblacklist_namespace(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->deblacklist('namespaces', $name);
        }
        /** Whitelist alias
         *
         * You can pass a string of alias name, or pass an array of the alias names to whitelist
         *
         * @example $sandbox->whitelist_alias('Foo');
         *
         * @example $sandbox->whitelist_alias(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of alias names  or array of alias names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->whitelist('aliases', $name);
        }
        /** Blacklist alias
         *
         * You can pass a string of alias name, or pass an array of the alias names to blacklist
         *
         * @example $sandbox->blacklist_alias('Foo');
         *
         * @example $sandbox->blacklist_alias(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of alias name or array of alias names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->blacklist('aliases', $name);
        }
        /** Remove alias from whitelist
         *
         * You can pass a string of alias name, or pass an array of the alias names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_alias('Foo');
         *
         * @example $sandbox->dewhitelist_alias(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of alias name or array of alias names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->dewhitelist('aliases', $name);
        }
        /** Remove alias from blacklist
         *
         * You can pass a string of alias name, or pass an array of the alias names to remove from blacklist
         *
         * @example $sandbox->deblacklist_alias('Foo');
         *
         * @example $sandbox->deblacklist_alias(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of alias name or array of alias names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->deblacklist('aliases', $name);
        }
        /** Whitelist use (or alias)
         *
         * You can pass a string of use (or alias) name, or pass an array of the use (or alias) names to whitelist
         *
         * @alias   whitelist_alias();
         *
         * @example $sandbox->whitelist_use('Foo');
         *
         * @example $sandbox->whitelist_use(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_use($name){
            return $this->whitelist_alias($name);
        }
        /** Blacklist use (or alias)
         *
         * You can pass a string of use (or alias) name, or pass an array of the use (or alias) names to blacklist
         *
         * @alias   blacklist_alias();
         *
         * @example $sandbox->blacklist_use('Foo');
         *
         * @example $sandbox->blacklist_use(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_use($name){
            return $this->blacklist_alias($name);
        }
        /** Remove use (or alias) from whitelist
         *
         * You can pass a string of use (or alias name, or pass an array of the use (or alias) names to remove from whitelist
         *
         * @alias   dewhitelist_alias();
         *
         * @example $sandbox->dewhitelist_use('Foo');
         *
         * @example $sandbox->dewhitelist_use(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_use($name){
            return $this->dewhitelist_alias($name);
        }
        /** Remove use (or alias) from blacklist
         *
         * You can pass a string of use (or alias name, or pass an array of the use (or alias) names to remove from blacklist
         *
         * @alias   deblacklist_alias();
         *
         * @example $sandbox->deblacklist_use('Foo');
         *
         * @example $sandbox->deblacklist_use(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_use($name){
            return $this->deblacklist_alias($name);
        }
        /** Whitelist class
         *
         * You can pass a string of class name, or pass an array of the class names to whitelist
         *
         * @example $sandbox->whitelist_class('Foo');
         *
         * @example $sandbox->whitelist_class(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of class name or array of class names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_class($name){
            $name = $this->normalize_class($name);
            return $this->whitelist('classes', $name);
        }
        /** Blacklist class
         *
         * You can pass a string of class name, or pass an array of the class names to blacklist
         *
         * @example $sandbox->blacklist_class('Foo');
         *
         * @example $sandbox->blacklist_class(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of class name or array of class names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_class($name){
            $name = $this->normalize_class($name);
            return $this->blacklist('classes', $name);
        }
        /** Remove class from whitelist
         *
         * You can pass a string of class name, or pass an array of the class names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_class('Foo');
         *
         * @example $sandbox->dewhitelist_class(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of class name or array of class names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_class($name){
            $name = $this->normalize_class($name);
            return $this->dewhitelist('classes', $name);
        }
        /** Remove class from blacklist
         *
         * You can pass a string of class name, or pass an array of the class names to remove from blacklist
         *
         * @example $sandbox->deblacklist_class('Foo');
         *
         * @example $sandbox->deblacklist_class(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of class name or array of class names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_class($name){
            $name = $this->normalize_class($name);
            return $this->deblacklist('classes', $name);
        }
        /** Whitelist interface
         *
         * You can pass a string of interface name, or pass an array of the interface names to whitelist
         *
         * @example $sandbox->whitelist_interface('Foo');
         *
         * @example $sandbox->whitelist_interface(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of interface name or array of interface names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->whitelist('interfaces', $name);
        }
        /** Blacklist interface
         *
         * You can pass a string of interface name, or pass an array of the interface names to blacklist
         *
         * @example $sandbox->blacklist_interface('Foo');
         *
         * @example $sandbox->blacklist_interface(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of interface name or array of interface names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->blacklist('interfaces', $name);
        }
        /** Remove interface from whitelist
         *
         * You can pass a string of interface name, or pass an array of the interface names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_interface('Foo');
         *
         * @example $sandbox->dewhitelist_interface(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of interface name or array of interface names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->dewhitelist('interfaces', $name);
        }
        /** Remove interface from blacklist
         *
         * You can pass a string of interface name, or pass an array of the interface names to remove from blacklist
         *
         * @example $sandbox->deblacklist_interface('Foo');
         *
         * @example $sandbox->deblacklist_interface(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of interface name or array of interface names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->deblacklist('interfaces', $name);
        }
        /** Whitelist trait
         *
         * You can pass a string of trait name, or pass an array of the trait names to whitelist
         *
         * @example $sandbox->whitelist_trait('Foo');
         *
         * @example $sandbox->whitelist_trait(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of trait name or array of trait names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->whitelist('traits', $name);
        }
        /** Blacklist trait
         *
         * You can pass a string of trait name, or pass an array of the trait names to blacklist
         *
         * @example $sandbox->blacklist_trait('Foo');
         *
         * @example $sandbox->blacklist_trait(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of trait name or array of trait names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->blacklist('traits', $name);
        }
        /** Remove trait from whitelist
         *
         * You can pass a string of trait name, or pass an array of the trait names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_trait('Foo');
         *
         * @example $sandbox->dewhitelist_trait(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of trait name or array of trait names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->dewhitelist('traits', $name);
        }
        /** Remove trait from blacklist
         *
         * You can pass a string of trait name, or pass an array of the trait names to remove from blacklist
         *
         * @example $sandbox->deblacklist_trait('Foo');
         *
         * @example $sandbox->deblacklist_trait(array('Foo', 'Bar'));
         *
         * @param   string|array        $name       String of trait name or array of trait names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->deblacklist('traits', $name);
        }
        /** Whitelist keyword
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to whitelist
         *
         * @example $sandbox->whitelist_keyword('echo');
         *
         * @example $sandbox->whitelist_keyword(array('echo', 'eval'));
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->whitelist('keywords', $name);
        }
        /** Blacklist keyword
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to blacklist
         *
         * @example $sandbox->blacklist_keyword('echo');
         *
         * @example $sandbox->blacklist_keyword(array('echo', 'eval'));
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->blacklist('keywords', $name);
        }
        /** Remove keyword from whitelist
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_keyword('echo');
         *
         * @example $sandbox->dewhitelist_keyword(array('echo', 'eval'));
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->dewhitelist('keywords', $name);
        }
        /** Remove keyword from blacklist
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to remove from blacklist
         *
         * @example $sandbox->deblacklist_keyword('echo');
         *
         * @example $sandbox->deblacklist_keyword(array('echo', 'eval'));
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->deblacklist('keywords', $name);
        }
        /** Whitelist operator
         *
         * You can pass a string of operator name, or pass an array of the operator names to whitelist
         *
         * @example $sandbox->whitelist_operator('+');
         *
         * @example $sandbox->whitelist_operator(array('+', '-'));
         *
         * @param   string|array        $name       String of operator name or array of operator names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_operator($name){
            $name = $this->normalize_operator($name);
            return $this->whitelist('operators', $name);
        }
        /** Blacklist operator
         *
         * You can pass a string of operator name, or pass an array of the operator names to blacklist
         *
         * @example $sandbox->blacklist_operator('+');
         *
         * @example $sandbox->blacklist_operator(array('+', '-'));
         *
         * @param   string|array        $name       String of operator name or array of operator names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_operator($name){
            $name = $this->normalize_operator($name);
            return $this->blacklist('operators', $name);
        }
        /** Remove operator from whitelist
         *
         * You can pass a string of operator name, or pass an array of the operator names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_operator('+');
         *
         * @example $sandbox->dewhitelist_operator(array('+', '-'));
         *
         * @param   string|array        $name       String of operator name or array of operator names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_operator($name){
            $name = $this->normalize_operator($name);
            return $this->dewhitelist('operators', $name);
        }
        /** Remove operator from blacklist
         *
         * You can pass a string of operator name, or pass an array of the operator names to remove from blacklist
         *
         * @example $sandbox->deblacklist_operator('+');
         *
         * @example $sandbox->deblacklist_operator(array('+', '-'));
         *
         * @param   string|array        $name       String of operator name or array of operator names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_operator($name){
            $name = $this->normalize_operator($name);
            return $this->deblacklist('operators', $name);
        }
        /** Whitelist primitive
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to whitelist
         *
         * @example $sandbox->whitelist_primitive('int');
         *
         * @example $sandbox->whitelist_primitive(array('int', 'float'));
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->whitelist('primitives', $name);
        }
        /** Blacklist primitive
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to blacklist
         *
         * @example $sandbox->blacklist_primitive('int');
         *
         * @example $sandbox->blacklist_primitive(array('int', 'float'));
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->blacklist('primitives', $name);
        }
        /** Remove primitive from whitelist
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_primitive('int');
         *
         * @example $sandbox->dewhitelist_primitive(array('int', 'float'));
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->dewhitelist('primitives', $name);
        }
        /** Remove primitive from blacklist
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to remove from blacklist
         *
         * @example $sandbox->deblacklist_primitive('int');
         *
         * @example $sandbox->deblacklist_primitive(array('int', 'float'));
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->deblacklist('primitives', $name);
        }
        /** Whitelist type
         *
         * You can pass a string of type name, or pass an array of the type names to whitelist
         *
         * @example $sandbox->whitelist_type('PHPSandbox');
         *
         * @example $sandbox->whitelist_type(array('PHPSandbox', 'PHPParser'));
         *
         * @param   string|array        $name       String of type name or array of type names to whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function whitelist_type($name){
            $name = $this->normalize_type($name);
            return $this->whitelist('types', $name);
        }
        /** Blacklist type
         *
         * You can pass a string of type name, or pass an array of the type names to blacklist
         *
         * @example $sandbox->blacklist_type('PHPSandbox');
         *
         * @example $sandbox->blacklist_type(array('PHPSandbox', 'PHPParser'));
         *
         * @param   string|array        $name       String of type name or array of type names to blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function blacklist_type($name){
            $name = $this->normalize_type($name);
            return $this->blacklist('types', $name);
        }
        /** Remove type from whitelist
         *
         * You can pass a string of type name, or pass an array of the type names to remove from whitelist
         *
         * @example $sandbox->dewhitelist_type('PHPSandbox');
         *
         * @example $sandbox->dewhitelist_type(array('PHPSandbox', 'PHPParser'));
         *
         * @param   string|array        $name       String of type name or array of type names to remove from whitelist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function dewhitelist_type($name){
            $name = $this->normalize_type($name);
            return $this->dewhitelist('types', $name);
        }
        /** Remove type from blacklist
         *
         * You can pass a string of type name, or pass an array of the type names to remove from blacklist
         *
         * @example $sandbox->deblacklist_type('PHPSandbox');
         *
         * @example $sandbox->deblacklist_type(array('PHPSandbox', 'PHPParser'));
         *
         * @param   string|array        $name       String of type name or array of type names to remove from blacklist
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function deblacklist_type($name){
            $name = $this->normalize_type($name);
            return $this->deblacklist('types', $name);
        }
        /** Check function name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the function name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if function is valid, this is used for testing closures
         */
        public function check_func($name){
            $original_name = $name;
            if($name instanceof \Closure){
                if(!$this->allow_closures){
                    throw new Error("Sandboxed code attempted to call closure!");
                }
                return true;
            }
            if(!$name || !is_string($name)){
                throw new Error("Sandboxed code attempted to call unnamed function!");
            }
            $name = $this->normalize_func($name);
            if(!isset($this->definitions['functions'][$name]) || !is_callable($this->definitions['functions'][$name]['function'])){
                if(count($this->whitelist['functions'])){
                    if(!isset($this->whitelist['functions'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted function: $original_name");
                    }
                } else if(count($this->blacklist['functions'])){
                    if(isset($this->blacklist['functions'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted function: $original_name");
                    }
                } else {
                   throw new Error("Sandboxed code attempted to call invalid function: $original_name");
                }
            }
            return true;
        }
        /** Check variable name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the variable name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_var($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed variable!");
            }
            if(!isset($this->definitions['variables'][$name])){
                if(count($this->whitelist['variables'])){
                    if(!isset($this->whitelist['variables'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted variable: $original_name");
                    }
                } else if(count($this->blacklist['variables'])){
                    if(isset($this->blacklist['variables'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted variable: $original_name");
                    }
                } else if(!$this->allow_variables){
                    throw new Error("Sandboxed code attempted to call invalid variable: $original_name");
                }
            }
        }
        /** Check global name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the global name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_global($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed global!");
            }
            if(count($this->whitelist['globals'])){
                if(!isset($this->whitelist['globals'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted global: $original_name");
                }
            } else if(count($this->blacklist['globals'])){
                if(isset($this->blacklist['globals'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted global: $original_name");
                }
            } else {
                throw new Error("Sandboxed code attempted to call invalid global: $original_name");
            }
        }
        /** Check superglobal name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the superglobal name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_superglobal($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed superglobal!");
            }
            $name = $this->normalize_superglobal($name);
            if(!isset($this->definitions['superglobals'][$name])){
                if(count($this->whitelist['superglobals'])){
                    if(!isset($this->whitelist['superglobals'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted superglobal: $original_name");
                    }
                } else if(count($this->blacklist['superglobals'])){
                    if(isset($this->blacklist['superglobals'][$name]) && !count($this->blacklist['superglobals'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted superglobal: $original_name");
                    }
                } else if(!$this->overwrite_superglobals){
                    throw new Error("Sandboxed code attempted to call invalid superglobal: $original_name");
                }
            }
        }
        /** Check constant name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the constant name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_const($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed constant!");
            }
            if(strtolower($name) == 'true' || strtolower($name) == 'false'){
                $this->check_primitive('bool');
                return;
            }
            if(strtolower($name) == 'null'){
                $this->check_primitive('null');
                return;
            }
            if(!isset($this->definitions['constants'][$name])){
                if(count($this->whitelist['constants'])){
                    if(!isset($this->whitelist['constants'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted constant: $original_name");
                    }
                } else if(count($this->blacklist['constants'])){
                    if(isset($this->blacklist['constants'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted constant: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to call invalid constant: $original_name");
                }
            }
        }
        /** Check magic constant name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the magic constant name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_magic_const($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed magic constant!");
            }
            $name = $this->normalize_magic_const($name);
            if(!isset($this->definitions['magic_constants'][$name])){
                if(count($this->whitelist['magic_constants'])){
                    if(!isset($this->whitelist['magic_constants'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted magic constant: $original_name");
                    }
                } else if(count($this->blacklist['magic_constants'])){
                    if(isset($this->blacklist['magic_constants'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted magic constant: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to call invalid magic constant: $original_name");
                }
            }
        }
        /** Check namespace name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the namespace name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_namespace($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed namespace!");
            }
            $name = $this->normalize_namespace($name);
            if(count($this->whitelist['namespaces'])){
                if(!isset($this->whitelist['namespaces'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted namespace: $original_name");
                }
            } else if(count($this->blacklist['namespaces'])){
                if(isset($this->blacklist['namespaces'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted namespace: $original_name");
                }
            } else if(!$this->allow_namespaces){
                throw new Error("Sandboxed code attempted to call invalid namespace: $original_name");
            }
        }
        /** Check alias name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the alias name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_alias($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed alias!");
            }
            $name = $this->normalize_alias($name);
            if(count($this->whitelist['aliases'])){
                if(!isset($this->whitelist['aliases'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted alias: $original_name");
                }
            } else if(count($this->blacklist['aliases'])){
                if(isset($this->blacklist['aliases'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted alias: $original_name");
                }
            } else if(!$this->allow_aliases){
                throw new Error("Sandboxed code attempted to call invalid alias: $original_name");
            }
        }
        /** Check use (or alias) name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         *
         * @alias check_alias();
         *
         * @param   string   $name      String of the use (or alias) name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_use($name){
            $this->check_alias($name);
        }
        /** Check class name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the class name to check
         * @param   bool     $extends   Flag whether this is an extended class
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_class($name, $extends = false){
            $original_name = $name;
            $action = $extends ? 'extend' : 'call';
            if(!$name){
                throw new Error("Sandboxed code attempted to $action unnamed class!");
            }
            $name = $this->normalize_class($name);
            if(!isset($this->definitions['classes'][$name])){
                if(count($this->whitelist['classes'])){
                    if(!isset($this->whitelist['classes'][$name])){
                        throw new Error("Sandboxed code attempted to $action non-whitelisted class: $original_name");
                    }
                } else if(count($this->blacklist['classes'])){
                    if(isset($this->blacklist['classes'][$name])){
                        throw new Error("Sandboxed code attempted to $action blacklisted class: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to $action invalid class: $original_name");
                }
            }
        }
        /** Check interface name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the interface name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_interface($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed interface!");
            }
            $name = $this->normalize_interface($name);
            if(!isset($this->definitions['interfaces'][$name])){
                if(count($this->whitelist['interfaces'])){
                    if(!isset($this->whitelist['interfaces'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted interface: $original_name");
                    }
                } else if(count($this->blacklist['interfaces'])){
                    if(isset($this->blacklist['interfaces'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted interface: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to call invalidnterface: $original_name");
                }
            }
        }
        /** Check trait name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the trait name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_trait($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed trait!");
            }
            $name = $this->normalize_trait($name);
            if(!isset($this->definitions['traits'][$name])){
                if(count($this->whitelist['traits'])){
                    if(!isset($this->whitelist['traits'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted trait: $original_name");
                    }
                } else if(count($this->blacklist['traits'])){
                    if(isset($this->blacklist['traits'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted trait: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to call invalid trait: $original_name");
                }
            }
        }
        /** Check keyword name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the keyword name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_keyword($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed keyword!");
            }
            $name = $this->normalize_keyword($name);
            if(count($this->whitelist['keywords'])){
                if(!isset($this->whitelist['keywords'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted keyword: $original_name");
                }
            } else if(count($this->blacklist['keywords'])){
                if(isset($this->blacklist['keywords'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted keyword: $original_name");
                }
            }
        }
        /** Check operator name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the type operator to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_operator($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed operator!");
            }
            $name = $this->normalize_operator($name);
            if(count($this->whitelist['operators'])){
                if(!isset($this->whitelist['operators'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted operator: $original_name");
                }
            } else if(count($this->blacklist['operators'])){
                if(isset($this->blacklist['operators'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted operator: $original_name");
                }
            }
        }
        /** Check primitive name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the primitive name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_primitive($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed primitive!");
            }
            $name = $this->normalize_primitive($name);
            if(count($this->whitelist['primitives'])){
                if(!isset($this->whitelist['primitives'][$name])){
                    throw new Error("Sandboxed code attempted to call non-whitelisted primitive: $original_name");
                }
            } else if(count($this->blacklist['primitives'])){
                if(isset($this->blacklist['primitives'][$name])){
                    throw new Error("Sandboxed code attempted to call blacklisted primitive: $original_name");
                }
            }
        }
        /** Check type name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the type name to check
         * @throws  Error    Throws exception if validation error occurs
         */
        public function check_type($name){
            $original_name = $name;
            if(!$name){
                throw new Error("Sandboxed code attempted to call unnamed type!");
            }
            $name = $this->normalize_type($name);
            if(!isset($this->definitions['classes'][$name])){
                if(count($this->whitelist['types'])){
                    if(!isset($this->whitelist['types'][$name])){
                        throw new Error("Sandboxed code attempted to call non-whitelisted type: $original_name");
                    }
                } else if(count($this->blacklist['types'])){
                    if(isset($this->blacklist['types'][$name])){
                        throw new Error("Sandboxed code attempted to call blacklisted type: $original_name");
                    }
                } else {
                    throw new Error("Sandboxed code attempted to call invalid type: $original_name");
                }
            }
        }
        /** Prepare defined variables for execution
         */
        protected function prepare_vars(){
            $output = array();
            foreach($this->definitions['variables'] as $name => $value){
                if(is_scalar($value) || is_null($value)){
                    if(is_bool($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? 'true' : 'false');
                    } else if(is_int($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? $value : '0');
                    } else if(is_float($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? $value : '0.0');
                    } else if(is_string($value)){
                        $output[] = '$' . $name . " = '" . addcslashes($value, "'") . "'";
                    } else {
                        $output[] = '$' . $name . " = null";
                    }
                } else {
                    throw new Error("Sandboxed code attempted to pass non-scalar default variable value: $name");
                }
            }
            return count($output) ? ', ' . implode(', ', $output) : '';
        }
        /** Prepare defined constants for execution
         */
        protected function prepare_consts(){
            $output = array();
            foreach($this->definitions['constants'] as $name => $value){
                if(is_scalar($value) || is_null($value)){
                    if(is_bool($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? 'true' : 'false') . ');';
                    } else if(is_int($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? $value : '0') . ');';
                    } else if(is_float($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? $value : '0.0') . ');';
                    } else if(is_string($value)){
                        $output[] = '\define(' . "'" . $name . "', '" . addcslashes($value, "'") . "');";
                    } else {
                        $output[] = '\define(' . "'" . $name . "', null);";
                    }
                } else {
                    throw new Error("Sandboxed code attempted to define non-scalar constant value: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }
        /** Prepare defined namespaces for execution
         */
        protected function prepare_namespaces(){
            $output = array();
            foreach($this->definitions['namespaces'] as $name){
                if(is_string($name) && $name){
                    $output[] = 'namespace ' . $name . ';';
                } else {
                    throw new Error("Sandboxed code attempted to create invalid namespace: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }
        /** Prepare defined aliases for execution
         */
        protected function prepare_aliases(){
            $output = array();
            foreach($this->definitions['aliases'] as $name => $alias){
                if(is_string($name) && $name){
                    $output[] = 'use ' . $name . ((is_string($alias) && $alias) ? ' as ' . $alias : '') . ';';
                } else {
                    throw new Error("Sandboxed code attempted to use invalid namespace alias: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }
        /** Prepare defined uses (or aliases) for execution
         * @alias   prepare_aliases();
         */
        protected function prepare_uses(){
            return $this->prepare_aliases();
        }
        /** Disassemble callable to string
         *
         * @param   callable    $closure                The callable to disassemble
         *
         * @throws  Error       Throw exception if callable is passed and FunctionParser library is missing
         *
         * @return  string      Return the disassembled code string
         */
        protected function disassemble($closure){
            if(!class_exists('\FunctionParser\FunctionParser', true) && is_callable($closure)){
                throw new Error("Cannot disassemble callable code because the FunctionParser library could not be found!");
            }
            if(is_string($closure) && !is_callable($closure)){
                return strpos($closure, '<?') === 0 ? $closure : '<?php ' . $closure;
            }
            $disassembled_closure = FunctionParser::fromCallable($closure);
            if($this->auto_define_vars){
                $this->auto_define($disassembled_closure);
            }
            return '<?php ' . $disassembled_closure->getBody();
        }
        /** Automatically whitelisted trusted code
         *
         * @param   string    $code         String of trusted $code to automatically whitelist
         * @param   bool      $appended     Flag if this code ir prended or appended (true = appended)
         *
         * @throws  Error     Throw exception if code cannot be parsed for whitelisting
         */
        protected function auto_whitelist($code, $appended = false){
            $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
            try {
                $statements = $parser->parse($code);
            } catch (\PHPParser_Error $error) {
                throw new Error('Error parsing ' . ($appended ? 'appended' : 'prepended') . ' sandboxed code for auto-whitelisting!');
            }
            $traverser = new \PHPParser_NodeTraverser;
            $whitelister = new WhitelistVisitor($this);
            $traverser->addVisitor($whitelister);
            $traverser->traverse($statements);
        }
        /** Automatically define variables passed to disassembled closure
         * @param FunctionParser    $disassembled_closure
         */
        protected function auto_define(FunctionParser $disassembled_closure){
            $parameters = $disassembled_closure->getReflection()->getParameters();
            foreach($parameters as $param){
                /**
                 * @var \ReflectionParameter $param
                 */
                $this->define_var($param->getName(), $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }
        /** Prepend trusted code
         * @param   string|callable     $code         String or callable of trusted $code to prepend to generated code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function prepend($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code, false);
            if($this->auto_whitelist_trusted_code){
                $this->auto_whitelist($code);
            }
            $this->prepended_code .= substr($code, 6) . "\r\n"; //remove opening php tag
            return $this;
        }
        /** Append trusted code
         * @param   string|callable     $code         String or callable of trusted $code to append to generated code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function append($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code, false);
            if($this->auto_whitelist_trusted_code){
                $this->auto_whitelist($code, true);
            }
            $this->appended_code .= "\r\n" . substr($code, 6) . "\r\n"; //remove opening php tag
            return $this;
        }
        /** Clear all trusted and sandboxed code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function clear(){
            $this->prepended_code = '';
            $this->generated_closure = null;
            $this->appended_code = '';
        }
        /** Clear all trusted code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function clear_trusted_code(){
            $this->prepended_code = '';
            $this->appended_code = '';
            return $this;
        }
        /** Clear all prepended trusted code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function clear_prepend(){
            $this->prepended_code = '';
            return $this;
        }
        /** Clear all appended trusted code
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function clear_append(){
            $this->appended_code = '';
            return $this;
        }
        /** Clear generated closure
         *
         * @return  PHPSandbox               Returns the PHPSandbox instance for chainability
         */
        public function clear_closure(){
            $this->generated_closure = null;
            return $this;
        }
        /** Return the amount of time the sandbox spent preparing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @example $sandbox->get_prepared_time();
         *
         * @example $sandbox->get_prepared_time(3);
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to prepare the sandboxed code
         */
        public function get_prepared_time($round = null){
            return $round ? round($this->prepare_time, $round) : $this->prepare_time;
        }
        /** Return the amount of time the sandbox spent executing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @example $sandbox->get_execution_time();
         *
         * @example $sandbox->get_execution_time(3);
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to execute the sandboxed code
         */
        public function get_execution_time($round = null){
            return $round ? round($this->execution_time, $round) : $this->execution_time;
        }
        /** Return the amount of time the sandbox spent preparing and executing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @example $sandbox->get_time();
         *
         * @example $sandbox->get_time(3);
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to prepare and execute the sandboxed code
         */
        public function get_time($round = null){
            return $round ? round($this->prepare_time + $this->execution_time, $round) : ($this->prepare_time + $this->execution_time);
        }
        /** Prepare passed callable for execution
         *
         * This function validates your code and automatically whitelists it according to your specified configuration
         *
         * @example $sandbox->prepare(function(){ var_dump('Hello world!'); });
         *
         * @param   callable    $code       The callable to prepare for execution
         *
         * @throws  Error       Throws exception if error occurs in parsing, validation or whitelisting
         *
         * @return  \Closure    The prepared closure (this can also be accessed via $sandbox->generated_closure)
         */
        public function prepare($code){
            $this->prepare_time = microtime(true);
            if($this->allow_constants && !$this->is_defined_func('define') && ($this->has_whitelist_funcs() || !$this->has_blacklist_funcs())){
                $this->whitelist_func('define');    //makes no sense to allow constants if you can't define them!
            }

            $this->preparsed_code = $this->disassemble($code);

            $parser = new \PHPParser_Parser(new \PHPParser_Lexer_Emulative);

            try {
                $this->parsed_ast = $parser->parse($this->preparsed_code);
            } catch (\PHPParser_Error $error) {
                throw new Error($error);
            }

            $traverser = new \PHPParser_NodeTraverser;

            $prettyPrinter = new \PHPParser_PrettyPrinter_Default;

            if(($this->allow_functions && $this->auto_whitelist_functions) ||
                ($this->allow_constants && $this->auto_whitelist_constants) ||
                ($this->allow_classes && $this->auto_whitelist_classes) ||
                ($this->allow_interfaces && $this->auto_whitelist_interfaces) ||
                ($this->allow_traits && $this->auto_whitelist_traits) ||
                ($this->allow_globals && $this->auto_whitelist_globals)){
                $whitelister = new SandboxWhitelistVisitor($this);
                $traverser->addVisitor($whitelister);
            }

            $validator = new ValidatorVisitor($this);

            $traverser->addVisitor($validator);

            $this->prepared_ast = $traverser->traverse($this->parsed_ast);

            $this->prepared_code = $prettyPrinter->prettyPrint($this->prepared_ast);

            $this->generated_code = $this->prepare_namespaces() .
                $this->prepare_aliases() .
                $this->prepare_consts() .
                '$this->generated_closure = function($' . $this->name . $this->prepare_vars() . "){\r\n" .
                $this->prepended_code .
                $this->prepared_code .
                $this->appended_code .
                "\r\n};";
            @eval($this->generated_code);
            usleep(1); //guarantee at least some time passes
            $this->prepare_time = (microtime(true) - $this->prepare_time);
            return $this->generated_closure;
        }
        /** Prepare and execute callable and return output
         *
         * This function validates your code and automatically whitelists it according to your specified configuration, then executes it.
         * You can also pass an unlimited number of arguments to override variables configured in the function. If the first argument is
         * not callable and a valid generated closure exists, then all arguments are passed to the executed code
         *
         * @example $sandbox->execute(function(){ var_dump('Hello world!'); });
         *
         * @example $sandbox->execute(function($test){ var_dump($test); }, 'Hello world!'); //Hello world!
         *
         * @throws  Error       Throws exception if error occurs in parsing, validation or whitelisting or if generated closure is invalid
         *
         * @return  mixed       The output from the executed sandboxed code
         */
        public function execute(){
            $arguments = func_get_args();
            if(count($arguments) && !($this->generated_closure && is_callable($this->generated_closure))){
                $this->prepare(array_shift($arguments));
            }

            if(is_callable($this->generated_closure)){
                if($this->error_level !== null){
                    error_reporting($this->error_level);
                }
                array_unshift($arguments, $this);
                $this->execution_time = microtime(true);
                $result = call_user_func_array($this->generated_closure, $arguments);
                usleep(1); //guarantee at least some time passes
                $this->execution_time = (microtime(true) - $this->execution_time);
                return $result;
            }
            throw new Error("Error generating sandboxed code!");
		}
	}