<?php
    /** PHPSandbox class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    use FunctionParser\FunctionParser,
        PhpParser\Node,
        PhpParser\NodeTraverser,
        PhpParser\ParserFactory,
        PhpParser\PrettyPrinter\Standard,
        PhpParser\Error as ParserError;

    /**
     * PHPSandbox class for PHP Sandboxes.
     *
     * This class encapsulates the entire functionality of a PHPSandbox so that an end user
     * only has to create a PHPSandbox instance, configure its options, and run their code
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 2.0
     *
     * @method define_func()
     * @method define_funcs()
     * @method has_defined_funcs()
     * @method is_defined_func()
     * @method undefine_func()
     * @method undefine_funcs()
     * @method define_var()
     * @method define_vars()
     * @method has_defined_vars()
     * @method is_defined_var()
     * @method undefine_var()
     * @method undefine_vars()
     * @method define_superglobal()
     * @method define_superglobals()
     * @method has_defined_superglobals()
     * @method is_defined_superglobal()
     * @method undefine_superglobal()
     * @method undefine_superglobals()
     * @method define_const()
     * @method define_consts()
     * @method has_defined_consts()
     * @method is_defined_const()
     * @method undefine_const()
     * @method undefine_consts()
     * @method define_magic_const()
     * @method define_magic_consts()
     * @method has_defined_magic_consts()
     * @method is_defined_magic_const()
     * @method undefine_magic_const()
     * @method undefine_magic_consts()
     * @method define_namespace()
     * @method define_namespaces()
     * @method has_defined_namespaces()
     * @method is_defined_namespace()
     * @method get_defined_namespace()
     * @method undefine_namespace()
     * @method undefine_namespaces()
     * @method define_alias()
     * @method define_aliases()
     * @method has_defined_aliases()
     * @method is_defined_alias()
     * @method undefine_alias()
     * @method undefine_aliases()
     * @method define_use()
     * @method define_uses()
     * @method has_defined_uses()
     * @method is_defined_use()
     * @method undefine_use()
     * @method undefine_uses()
     * @method define_class()
     * @method define_classes()
     * @method has_defined_classes()
     * @method is_defined_class()
     * @method get_defined_class()
     * @method undefine_class()
     * @method undefine_classes()
     * @method define_interface()
     * @method define_interfaces()
     * @method has_defined_interfaces()
     * @method is_defined_interface()
     * @method get_defined_interface()
     * @method undefine_interface()
     * @method undefine_interfaces()
     * @method define_trait()
     * @method define_traits()
     * @method has_defined_traits()
     * @method is_defined_trait()
     * @method get_defined_trait()
     * @method undefine_trait()
     * @method undefine_traits()
     * @method has_whitelist()
     * @method has_blacklist()
     * @method is_whitelisted()
     * @method is_blacklisted()
     * @method has_whitelist_funcs()
     * @method has_blacklist_funcs()
     * @method is_whitelisted_func()
     * @method is_blacklisted_func()
     * @method has_whitelist_vars()
     * @method has_blacklist_vars()
     * @method is_whitelisted_var()
     * @method is_blacklisted_var()
     * @method has_whitelist_globals()
     * @method has_blacklist_globals()
     * @method is_whitelisted_global()
     * @method is_blacklisted_global()
     * @method has_whitelist_superglobals()
     * @method has_blacklist_superglobals()
     * @method is_whitelisted_superglobal()
     * @method is_blacklisted_superglobal()
     * @method has_whitelist_consts()
     * @method has_blacklist_consts()
     * @method is_whitelisted_const()
     * @method is_blacklisted_const()
     * @method has_whitelist_magic_consts()
     * @method has_blacklist_magic_consts()
     * @method is_whitelisted_magic_const()
     * @method is_blacklisted_magic_const()
     * @method has_whitelist_namespaces()
     * @method has_blacklist_namespaces()
     * @method is_whitelisted_namespace()
     * @method is_blacklisted_namespace()
     * @method has_whitelist_aliases()
     * @method has_blacklist_aliases()
     * @method is_whitelisted_alias()
     * @method is_blacklisted_alias()
     * @method has_whitelist_uses()
     * @method has_blacklist_uses()
     * @method is_whitelisted_use()
     * @method is_blacklisted_use()
     * @method has_whitelist_classes()
     * @method has_blacklist_classes()
     * @method is_whitelisted_class()
     * @method is_blacklisted_class()
     * @method has_whitelist_interfaces()
     * @method has_blacklist_interfaces()
     * @method is_whitelisted_interface()
     * @method is_blacklisted_interface()
     * @method has_whitelist_traits()
     * @method has_blacklist_traits()
     * @method is_whitelisted_trait()
     * @method is_blacklisted_trait()
     * @method has_whitelist_keywords()
     * @method has_blacklist_keywords()
     * @method is_whitelisted_keyword()
     * @method is_blacklisted_keyword()
     * @method has_whitelist_operators()
     * @method has_blacklist_operators()
     * @method is_whitelisted_operator()
     * @method is_blacklisted_operator()
     * @method has_whitelist_primitives()
     * @method has_blacklist_primitives()
     * @method is_whitelisted_primitive()
     * @method is_blacklisted_primitive()
     * @method has_whitelist_types()
     * @method has_blacklist_types()
     * @method is_whitelisted_type()
     * @method is_blacklisted_type()
     * @method whitelist_func()
     * @method blacklist_func()
     * @method dewhitelist_func()
     * @method deblacklist_func()
     * @method whitelist_var()
     * @method blacklist_var()
     * @method dewhitelist_var()
     * @method deblacklist_var()
     * @method whitelist_global()
     * @method blacklist_global()
     * @method dewhitelist_global()
     * @method deblacklist_global()
     * @method whitelist_superglobal()
     * @method blacklist_superglobal()
     * @method dewhitelist_superglobal()
     * @method deblacklist_superglobal()
     * @method whitelist_const()
     * @method blacklist_const()
     * @method dewhitelist_const()
     * @method deblacklist_const()
     * @method whitelist_magic_const()
     * @method blacklist_magic_const()
     * @method dewhitelist_magic_const()
     * @method deblacklist_magic_const()
     * @method whitelist_namespace()
     * @method blacklist_namespace()
     * @method dewhitelist_namespace()
     * @method deblacklist_namespace()
     * @method whitelist_alias()
     * @method blacklist_alias()
     * @method dewhitelist_alias()
     * @method deblacklist_alias()
     * @method whitelist_use()
     * @method blacklist_use()
     * @method dewhitelist_use()
     * @method deblacklist_use()
     * @method whitelist_class()
     * @method blacklist_class()
     * @method dewhitelist_class()
     * @method deblacklist_class()
     * @method whitelist_interface()
     * @method blacklist_interface()
     * @method dewhitelist_interface()
     * @method deblacklist_interface()
     * @method whitelist_trait()
     * @method blacklist_trait()
     * @method dewhitelist_trait()
     * @method deblacklist_trait()
     * @method whitelist_keyword()
     * @method blacklist_keyword()
     * @method dewhitelist_keyword()
     * @method deblacklist_keyword()
     * @method whitelist_operator()
     * @method blacklist_operator()
     * @method dewhitelist_operator()
     * @method deblacklist_operator()
     * @method whitelist_primitive()
     * @method blacklist_primitive()
     * @method dewhitelist_primitive()
     * @method deblacklist_primitive()
     * @method whitelist_type()
     * @method blacklist_type()
     * @method dewhitelist_type()
     * @method deblacklist_type()
     * @method check_func()
     * @method check_var()
     * @method check_global()
     * @method check_superglobal()
     * @method check_const()
     * @method check_magic_const()
     * @method check_namespace()
     * @method check_alias()
     * @method check_use()
     * @method check_class()
     * @method check_interface()
     * @method check_trait()
     * @method check_keyword()
     * @method check_operator()
     * @method check_primitive()
     * @method check_type()
     * @method clear_trusted_code()
     * @method clear_prepend()
     * @method clear_append()
     * @method clear_code()
     * @method get_prepared_time()
     * @method get_execution_time()
     * @method get_time()
     * @method set_error_handler()
     * @method get_error_handler()
     * @method unset_error_handler()
     * @method get_last_error()
     * @method set_exception_handler()
     * @method get_exception_handler()
     * @method unset_exception_handler()
     * @method get_last_exception()
     * @method set_validation_error_handler()
     * @method get_validation_error_handler()
     * @method unset_validation_error_handler()
     * @method get_last_validation_error()
     * @method validation_error()
     * @method normalize_func()
     * @method normalize_superglobal()
     * @method normalize_magic_const()
     * @method normalize_namespace()
     * @method normalize_alias()
     * @method normalize_use()
     * @method normalize_class()
     * @method normalize_interface()
     * @method normalize_trait()
     * @method normalize_keyword()
     * @method normalize_operator()
     * @method normalize_primitive()
     * @method normalize_type()
     * @method prepare_vars()
     * @method prepare_consts()
     * @method prepare_namespaces()
     * @method prepare_aliases()
     * @method prepare_uses()
     * @method auto_whitelist()
     * @method auto_define()
     */
    class PHPSandbox implements \IteratorAggregate {
        /**
         * @const    string      The prefix given to the obfuscated sandbox key passed to the generated code
         */
        const SANDBOX_PREFIX = '__PHPSandbox_';
        /**
         * @const    int           A bit flag for the import() method, signifies to import all data from a template
         */
        const IMPORT_ALL = 0;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only options from a template
         */
        const IMPORT_OPTIONS = 1;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only definitions from a template
         */
        const IMPORT_DEFINITIONS = 2;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only whitelists from a template
         */
        const IMPORT_WHITELIST = 4;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only blacklists from a template
         */
        const IMPORT_BLACKLIST = 8;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only trusted code from a template
         */
        const IMPORT_TRUSTED_CODE = 16;
        /**
         * @const    int           A bit flag for the import() method, signifies to import only sandboxed code from a template
         */
        const IMPORT_CODE = 32;
        /**
         * @static
         * @var    array         A static array of superglobal names used for redefining superglobal values
         */
        public static $superglobals = [
            '_GET',
            '_POST',
            '_COOKIE',
            '_FILES',
            '_ENV',
            '_REQUEST',
            '_SERVER',
            '_SESSION',
            'GLOBALS'
        ];
        /**
         * @static
         * @var    array        A static array of magic constant names used for redefining magic constant values
         */
        public static $magic_constants = [
            '__LINE__',
            '__FILE__',
            '__DIR__',
            '__FUNCTION__',
            '__CLASS__',
            '__TRAIT__',
            '__METHOD__',
            '__NAMESPACE__'
        ];
        /**
         * @static
         * @var    array          A static array of defined_* and declared_* functions names used for redefining defined_* and declared_* values
         */
        public static $defined_funcs = [
            'get_define_functions',
            'get_defined_vars',
            'get_defined_constants',
            'get_declared_classes',
            'get_declared_interfaces',
            'get_declared_traits',
            'get_included_files'
        ];
        /**
         * @static
         * @var    array          A static array of func_get_args, func_get_arg, and func_num_args used for redefining those functions
         */
        public static $arg_funcs = [
            'func_get_args',
            'func_get_arg',
            'func_num_args'
        ];
        /**
         * @static
         * @var    array          A static array of var_dump, print_r and var_export, intval, floatval, is_string, is_object,
         *                          is_scalar and is_callable for redefining those functions
         */
        public static $sandboxed_string_funcs = [
            'var_dump',
            'print_r',
            'var_export',
            'intval',
            'floatval',
            'boolval',
            'is_string',
            'is_object',
            'is_scalar',
            'is_callable'
        ];
        /**
         * @var    string       The randomly generated name of the PHPSandbox variable passed to the generated closure
         */
        public $name = '';
        /**
         * @var    array       Array of defined functions, superglobals, etc. If an array type contains elements, then it overwrites its external counterpart
         */
        protected $definitions = [
            'functions' => [],
            'variables' => [],
            'superglobals' => [],
            'constants' => [],
            'magic_constants' => [],
            'namespaces' => [],
            'aliases' => [],
            'classes' => [],
            'interfaces' => [],
            'traits' => []
        ];
        /**
         * @var    array       Array of whitelisted functions, classes, etc. If an array type contains elements, then it overrides its blacklist counterpart
         */
        protected $whitelist = [
            'functions' => [],
            'variables' => [],
            'globals' => [],
            'superglobals' => [],
            'constants' => [],
            'magic_constants' => [],
            'namespaces' => [],
            'aliases' => [],
            'classes' => [],
            'interfaces' => [],
            'traits' => [],
            'keywords' => [],
            'operators' => [],
            'primitives' => [],
            'types' => []
        ];
        /**
         * @var    array       Array of blacklisted functions, classes, etc. Any whitelisted array types override their counterpart in this array
         */
        protected $blacklist = [
            'functions' => [],
            'variables' => [],
            'globals' => [],
            'superglobals' => [],
            'constants' => [],
            'magic_constants' => [],
            'namespaces' => [],
            'aliases' => [],
            'classes' => [],
            'interfaces' => [],
            'traits' => [],
            'keywords' => [
                'declare' => true,
                'eval' => true,
                'exit' => true,
                'halt' => true
            ],
            'operators' => [],
            'primitives' => [],
            'types' => []
        ];
        /**
         * @var     array       Array of custom validation functions
         */
        protected $validation = [
            'function' => null,
            'variable' => null,
            'global' => null,
            'superglobal' => null,
            'constant' => null,
            'magic_constant' => null,
            'namespace' => null,
            'alias' => null,
            'class' => null,
            'interface' => null,
            'trait' => null,
            'keyword' => null,
            'operator' => null,
            'primitive' => null,
            'type' => null
        ];
        /**
         * @var     array       Array of sandboxed included files
         */
        protected $includes = [];
        /**
         * @var     PHPSandbox[]       Array of PHPSandboxes
         */
        protected static $sandboxes = [];
        /* CONFIGURATION OPTION FLAGS */
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate functions
         * @default true
         */
        public $validate_functions          = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate variables
         * @default true
         */
        public $validate_variables          = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate globals
         * @default true
         */
        public $validate_globals            = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate superglobals
         * @default true
         */
        public $validate_superglobals       = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate constants
         * @default true
         */
        public $validate_constants          = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate magic constants
         * @default true
         */
        public $validate_magic_constants    = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate namespaces
         * @default true
         */
        public $validate_namespaces         = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate aliases (aka use)
         * @default true
         */
        public $validate_aliases            = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate classes
         * @default true
         */
        public $validate_classes            = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate interfaces
         * @default true
         */
        public $validate_interfaces         = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate traits
         * @default true
         */
        public $validate_traits             = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate keywords
         * @default true
         */
        public $validate_keywords           = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate operators
         * @default true
         */
        public $validate_operators          = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate primitives
         * @default true
         */
        public $validate_primitives         = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should validate types
         * @default true
         */
        public $validate_types              = true;
        /**
         * @var    int        The error_reporting level to set the PHPSandbox scope to when executing the generated closure, if set to null it will use parent scope error level.
         * @default true
         */
        public $error_level                 = null;
        /**
         * @var    int        Integer value of maximum number of seconds the sandbox should be allowed to execute
         * @default 0
         */
        public $time_limit                 = 0;
        /**
         * @var    bool       Flag to indicate whether the sandbox should allow included files
         * @default false
         */
        public $allow_includes            = false;
        /**
         * @var    bool       Flag to indicate whether the sandbox should automatically sandbox included files
         * @default true
         */
        public $sandbox_includes            = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should return error_reporting to its previous level after execution
         * @default true
         */
        public $restore_error_level         = true;
        /**
         * @var    bool       Flag to indicate whether the sandbox should convert errors to exceptions
         * @default false
         */
        public $convert_errors              = false;
        /**
         * @var    bool       Flag whether to return output via an output buffer
         * @default false
         */
        public $capture_output              = false;
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
         * @var    bool       Should PHPSandbox overwrite get_define_functions, get_defined_vars, get_defined_constants, get_declared_classes, get_declared_interfaces and get_declared_traits?
         * @default true
         */
        public $overwrite_defined_funcs     = true;
        /**
         * @var    bool       Should PHPSandbox overwrite func_get_args, func_get_arg and func_num_args?
         * @default true
         */
        public $overwrite_func_get_args     = true;
        /**
         * @var    bool       Should PHPSandbox overwrite functions to help hide SandboxedStrings?
         * @default true
         */
        public $overwrite_sandboxed_string_funcs         = true;
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
         * @var    bool       Should PHPSandbox allow sandboxed code to declare namespaces (utilizing the defineNamespace function?)
         * @default false
         */
        public $allow_namespaces            = false;
        /**
         * @var    bool       Should PHPSandbox allow sandboxed code to use namespaces and declare namespace aliases (utilizing the defineAlias function?)
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
        protected $prepended_code = '';
        /**
         * @var    string     String of appended code, will be automagically whitelisted for functions, variables, globals, constants, classes, interfaces and traits if $auto_whitelist_trusted_code is true
         */
        protected $appended_code = '';
        /* OUTPUT */
        /**
         * @var float         Float of the number of microseconds it took to prepare the sandbox
         */
        protected $prepare_time = 0.0;
        /**
         * @var float         Float of the number of microseconds it took to execute the sandbox
         */
        protected $execution_time = 0.0;
        /**
         * @var int           Int of the number of bytes the sandbox allocates during execution
         */
        protected $memory_usage = 0;
        /**
         * @var    string     String of preparsed code, for debugging and serialization purposes
         */
        protected $preparsed_code = '';
        /**
         * @var    array      Array of parsed code broken down into AST tokens, for debugging and serialization purposes
         */
        protected $parsed_ast = [];
        /**
         * @var    string     String of prepared code, for debugging and serialization purposes
         */
        protected $prepared_code = '';
        /**
         * @var    array      Array of prepared code broken down into AST tokens, for debugging and serialization purposes
         */
        protected $prepared_ast = [];
        /**
         * @var    string     String of generated code, for debugging and serialization purposes
         */
        protected $generated_code = '';
        /**
         * @var    null|callable       Callable that handles any errors when set
         */
        protected $error_handler;
        /**
         * @var    int                 Integer value of the error types to handle (default is E_ALL)
         */
        protected $error_handler_types = E_ALL;
        /**
         * @var    array               The last error thrown by the sandbox
         */
        protected $last_error;
        /**
         * @var    null|callable       Callable that handles any thrown exceptions when set
         */
        protected $exception_handler;
        /**
         * @var    \Exception          The last exception thrown by the sandbox
         */
        protected $last_exception;
        /**
         * @var    null|callable       Callable that handles any thrown validation errors when set
         */
        protected $validation_error_handler;
        /**
         * @var    \Exception|Error    The last validation error thrown by the sandbox
         */
        protected $last_validation_error;
        /**
         * @var string         The current file being executed
         */
        protected $executing_file;

        /** PHPSandbox class constructor
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
         */
        public function __construct(array $options = [],
                                    array $functions = [],
                                    array $variables = [],
                                    array $constants = [],
                                    array $namespaces = [],
                                    array $aliases = [],
                                    array $superglobals = [],
                                    array $magic_constants = [],
                                    array $classes = [],
                                    array $interfaces = [],
                                    array $traits = []){
            $this->name = static::SANDBOX_PREFIX . md5(uniqid());
            $this->setOptions($options)
                ->defineFuncs($functions)
                ->defineVars($variables)
                ->defineConsts($constants)
                ->defineNamespaces($namespaces)
                ->defineAliases($aliases)
                ->defineSuperglobals($superglobals)
                ->defineMagicConsts($magic_constants)
                ->defineClasses($classes)
                ->defineInterfaces($interfaces)
                ->defineTraits($traits);
        }

        /** PHPSandbox static factory method
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
         *
         * @return  $this                  The returned PHPSandbox variable
         */
        public static function create(array $options = [],
                                      array $functions = [],
                                      array $variables = [],
                                      array $constants = [],
                                      array $namespaces = [],
                                      array $aliases = [],
                                      array $superglobals = [],
                                      array $magic_constants = [],
                                      array $classes = [],
                                      array $interfaces = [],
                                      array $traits = []){
            return new static($options, $functions, $variables, $constants, $namespaces, $aliases, $superglobals, $magic_constants, $classes, $interfaces, $traits);
        }

        /** PHPSandbox __invoke magic method
         *
         * Besides the code or closure to be executed, you can also pass additional arguments that will overwrite the default values of their respective arguments defined in the code
         *
         * @param   \Closure|callable|string   $code          The closure, callable or string of code to execute
         *
         * @return  mixed                      The output of the executed sandboxed code
         */
        public function __invoke($code){
            return call_user_func([$this, 'execute'], $code);
        }

        /** PHPSandbox __sleep magic method
         *
         * @return  array                      An array of property keys to be serialized
         */
        public function __sleep(){
            return array_keys(get_object_vars($this));
        }

        /** PHPSandbox __wakeup magic method
         *
         ** Import JSON template into sandbox
         *
         *
         * @param   array|string    $template          The JSON array or string template to import
         * @param   int             $import_flag       Binary flags signifying which parts of the JSON template to import
         *
         * @throws  Error           Throws exception if JSON template could not be imported
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function import($template, $import_flag = 0){
            if(is_string($template)){
                $template = json_decode($template);
            }
            if(!is_array($template)){
                $this->validationError("Sandbox could not import malformed JSON template!", Error::IMPORT_ERROR, null, $template);
            }
            if(isset($template['options']) && is_array($template['options']) && (!$import_flag || ($import_flag & static::IMPORT_OPTIONS))){
                $this->setOptions($template['options']);
            }
            if(isset($template['definitions']) && is_array($template['definitions']) && (!$import_flag || ($import_flag & static::IMPORT_DEFINITIONS))){
                foreach($template['definitions'] as $type => $data){
                    $method = 'define' . str_replace('_', '', ucwords($type, '_'));
                    if(method_exists($this, $method)){
                        switch($type){
                            case 'func':
                                foreach($data as $key => $value){
                                    $function = function(){};
                                    @eval('$function = ' . $value["fullcode"] .';');
                                    if(!is_callable($function)){
                                        $this->validationError("Could not import function $key! Please check your code for errors!", Error::IMPORT_ERROR, null, $function);
                                    }
                                    $this->defineFunc($key, $function, $value["pass"]);
                                }
                                break;
                            case 'superglobal':
                                foreach($data as $key => $value){
                                    $this->defineSuperglobal($key, $value["key"], $value["value"]);
                                }
                                break;
                            case 'namespace':
                                foreach($data as $key => $value){
                                    $this->defineNamespace($key);
                                }
                                break;
                            case 'alias':
                                foreach($data as $key => $value){
                                    $this->defineAlias($key, $value ? $value : null);
                                }
                                break;
                            case 'class':
                                foreach($data as $key => $value){
                                    $this->defineClass($key, $value);
                                }
                                break;
                            case 'interface':
                                foreach($data as $key => $value){
                                    $this->defineInterface($key, $value);
                                }
                                break;
                            case 'trait':
                                foreach($data as $key => $value){
                                    $this->defineTrait($key, $value);
                                }
                                break;

                            default:
                                foreach($data as $key => $value){
                                    call_user_func_array([$this, $method], [$key, $value["value"]]);
                                }
                                break;
                        }
                    }
                }
            }
            if(isset($template['whitelist']) && is_array($template['whitelist']) && (!$import_flag || ($import_flag & static::IMPORT_WHITELIST))){
                foreach($template['whitelist'] as $type => $data){
                    $method = 'whitelist' . str_replace('_', '', ucwords($type, '_'));
                    if(method_exists($this, $method)){
                        call_user_func_array([$this, $method], [$data]);
                    }
                }
            }
            if(isset($template['blacklist']) && is_array($template['blacklist']) && (!$import_flag || ($import_flag & static::IMPORT_BLACKLIST))){
                foreach($template['blacklist'] as $type => $data){
                    $method = 'blacklist' . str_replace('_', '', ucwords($type, '_'));
                    if(method_exists($this, $method)){
                        call_user_func_array([$this, $method], [$data]);
                    }
                }
            }
            if(!$import_flag || ($import_flag & static::IMPORT_TRUSTED_CODE)){
                $this->clearTrustedCode();
                if(isset($template['prepend_code']) && $template['prepend_code']){
                    $this->prepend($template['prepend_code']);
                }
                if(isset($template['append_code']) && $template['append_code']){
                    $this->append($template['append_code']);
                }
            }
            if(!$import_flag || ($import_flag & static::IMPORT_CODE)){
                $this->clearCode();
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
         * @param   array|string    $template          The JSON array or string template to import
         * @param   int             $import_flag       Binary flags signifying which parts of the JSON template to import
         *
         * @throws  Error           Throws exception if JSON template could not be imported
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function importJSON($template, $import_flag = 0){
            return $this->import($template, $import_flag);
        }

        /** Get name of PHPSandbox variable
         * @return  string                     The name of the PHPSandbox variable
         */
        public function getName(){
            return $this->name;
        }

        /** Set PHPSandbox option
         *
         * You can pass an $option name to set to $value, an array of $option names to set to $value, or an associative array of $option names and their values to set.
         *
         *
         *
         * @param   string|array    $option     String or array of strings or associative array of keys of option names to set $value to
         * @param   bool|int|null   $value      Boolean, integer or null $value to set $option to (optional)
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function setOption($option, $value = null){
            if(is_array($option)){
                return $this->setOptions($option, $value);
            }
            $option = strtolower($option); //normalize option names
            switch($option){
                case 'error_level':
                    $this->error_level = is_numeric($value) ? intval($value) : null;
                    break;
                case 'time_limit':
                    $this->time_limit = is_numeric($value) ? intval($value) : null;
                    break;
                case 'validate_functions':
                case 'validate_variables':
                case 'validate_globals':
                case 'validate_superglobals':
                case 'validate_constants':
                case 'validate_magic_constants':
                case 'validate_namespaces':
                case 'validate_aliases':
                case 'validate_classes':
                case 'validate_interfaces':
                case 'validate_traits':
                case 'validate_keywords':
                case 'validate_operators':
                case 'validate_primitives':
                case 'validate_types':
                case 'sandbox_includes':
                case 'restore_error_level':
                case 'convert_errors':
                case 'capture_output':
                case 'auto_whitelist_trusted_code':
                case 'auto_whitelist_functions':
                case 'auto_whitelist_constants':
                case 'auto_whitelist_globals':
                case 'auto_whitelist_classes':
                case 'auto_whitelist_interfaces':
                case 'auto_whitelist_traits':
                case 'auto_define_vars':
                case 'overwrite_defined_funcs':
                case 'overwrite_sandboxed_string_funcs':
                case 'overwrite_func_get_args':
                case 'overwrite_superglobals':
                case 'allow_functions':
                case 'allow_closures':
                case 'allow_variables':
                case 'allow_static_variables':
                case 'allow_objects':
                case 'allow_constants':
                case 'allow_globals':
                case 'allow_namespaces':
                case 'allow_aliases':
                case 'allow_classes':
                case 'allow_interfaces':
                case 'allow_traits':
                case 'allow_generators':
                case 'allow_escaping':
                case 'allow_casting':
                case 'allow_error_suppressing':
                case 'allow_references':
                case 'allow_backticks':
                case 'allow_halting':
                    $this->{$option} = !!$value;
                    break;
            }
            return $this;
        }

        /** Set PHPSandbox options by array
         *
         * You can pass an array of option names to set to $value, or an associative array of option names and their values to set.
         *
         * @param   array|string    $options    Array of strings or associative array of keys of option names to set $value to, or JSON array or string template to import
         * @param   bool|int|null   $value      Boolean, integer or null $value to set $option to (optional)
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function setOptions($options, $value = null){
            if(is_string($options) || (is_array($options) && isset($options["options"]))){
                return $this->import($options);
            }
            foreach($options as $name => $_value){
                $this->setOption(is_int($name) ? $_value : $name, is_int($name) ? $value : $_value);
            }
            return $this;
        }

        /** Reset PHPSandbox options to their default values
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function resetOptions(){
            foreach(get_class_vars(__CLASS__) as $option => $value){
                if($option == 'error_level' || is_bool($value)){
                    $this->setOption($option, $value);
                }
            }
            return $this;
        }

        /** Get PHPSandbox option
         *
         * You pass a string $option name to get its associated value
         *
         * @param   string          $option     String of $option name to get
         *
         * @return  boolean|int|null            Returns the value of the requested option
         */
        public function getOption($option){
            $option = strtolower($option);  //normalize option names
            switch($option){
                case 'validate_functions':
                case 'validate_variables':
                case 'validate_globals':
                case 'validate_superglobals':
                case 'validate_constants':
                case 'validate_magic_constants':
                case 'validate_namespaces':
                case 'validate_aliases':
                case 'validate_classes':
                case 'validate_interfaces':
                case 'validate_traits':
                case 'validate_keywords':
                case 'validate_operators':
                case 'validate_primitives':
                case 'validate_types':
                case 'error_level':
                case 'time_limit':
                case 'sandbox_includes':
                case 'restore_error_level':
                case 'convert_errors':
                case 'capture_output':
                case 'auto_whitelist_trusted_code':
                case 'auto_whitelist_functions':
                case 'auto_whitelist_constants':
                case 'auto_whitelist_globals':
                case 'auto_whitelist_classes':
                case 'auto_whitelist_interfaces':
                case 'auto_whitelist_traits':
                case 'auto_define_vars':
                case 'overwrite_defined_funcs':
                case 'overwrite_sandboxed_string_funcs':
                case 'overwrite_func_get_args':
                case 'overwrite_superglobals':
                case 'allow_functions':
                case 'allow_closures':
                case 'allow_variables':
                case 'allow_static_variables':
                case 'allow_objects':
                case 'allow_constants':
                case 'allow_globals':
                case 'allow_namespaces':
                case 'allow_aliases':
                case 'allow_classes':
                case 'allow_interfaces':
                case 'allow_traits':
                case 'allow_generators':
                case 'allow_escaping':
                case 'allow_casting':
                case 'allow_error_suppressing':
                case 'allow_references':
                case 'allow_backticks':
                case 'allow_halting':
                    return $this->{$option};
            }
            return null;
        }

        /** Set validation callable for specified $type
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   string          $type       String of $type name to set validator for
         * @param   callable        $callable   Callable that validates the passed element
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setValidator($type, $callable){
            $type = strtolower($type);  //normalize type
            if(array_key_exists($type, $this->validation)){
                $this->validation[$type] = $callable;
            }
            return $this;
        }

        /** Get validation callable for specified $type
         *
         * @param   string          $type       String of $type to return
         *
         * @return  callable|null
         */
        public function getValidator($type){
            $type = strtolower($type);  //normalize type
            return isset($this->validation[$type]) ? $this->validation[$type] : null;
        }

        /** Unset validation callable for specified $type
         *
         * @param   string          $type       String of $type to unset
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetValidator($type){
            $type = strtolower($type);  //normalize type
            if(isset($this->validation[$type])){
                $this->validation[$type] = null;
            }
            return $this;
        }

        /** Set validation callable for functions
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized function names include the namespace and are lowercase!
         *
         * @param   callable        $callable   Callable that validates the normalized passed function name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setFuncValidator($callable){
            $this->validation['function'] = $callable;
            return $this;
        }

        /** Get validation for functions
         *
         * @return  callable|null
         */
        public function getFuncValidator(){
            return isset($this->validation['function']) ? $this->validation['function'] : null;
        }

        /** Unset validation callable for functions
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetFuncValidator(){
            $this->validation['function'] = null;
            return $this;
        }

        /** Set validation callable for variables
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed variable name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setVarValidator($callable){
            $this->validation['variable'] = $callable;
            return $this;
        }

        /** Get validation callable for variables
         *
         * @return  callable|null
         */
        public function getVarValidator(){
            return isset($this->validation['variable']) ? $this->validation['variable'] : null;
        }

        /** Unset validation callable for variables
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetVarValidator(){
            $this->validation['variable'] = null;
            return $this;
        }

        /** Set validation callable for globals
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed global name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setGlobalValidator($callable){
            $this->validation['global'] = $callable;
            return $this;
        }

        /** Get validation callable for globals
         *
         * @return  callable|null
         */
        public function getGlobalValidator(){
            return isset($this->validation['global']) ? $this->validation['global'] : null;
        }

        /** Unset validation callable for globals
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetGlobalValidator(){
            $this->validation['global'] = null;
            return $this;
        }

        /** Set validation callable for superglobals
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized superglobal names are uppercase and without a leading _
         *
         * @param   callable        $callable   Callable that validates the passed superglobal name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setSuperglobalValidator($callable){
            $this->validation['superglobal'] = $callable;
            return $this;
        }

        /** Get validation callable for superglobals
         *
         * @return  callable|null
         */
        public function getSuperglobalValidator(){
            return isset($this->validation['superglobal']) ? $this->validation['superglobal'] : null;
        }

        /** Unset validation callable for superglobals
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetSuperglobalValidator(){
            $this->validation['superglobal'] = null;
            return $this;
        }

        /** Set validation callable for constants
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed constant name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setConstValidator($callable){
            $this->validation['constant'] = $callable;
            return $this;
        }

        /** Get validation callable for constants
         *
         * @return  callable|null
         */
        public function getConstValidator(){
            return isset($this->validation['constant']) ? $this->validation['constant'] : null;
        }

        /** Unset validation callable for constants
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetConstValidator(){
            $this->validation['constant'] = null;
            return $this;
        }

        /** Set validation callable for magic constants
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized magic constant names are upper case and trimmed of __
         *
         * @param   callable        $callable   Callable that validates the passed magic constant name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setMagicConstValidator($callable){
            $this->validation['magic_constant'] = $callable;
            return $this;
        }

        /** Get validation callable for magic constants
         *
         * @return  callable|null
         */
        public function getMagicConstValidator(){
            return isset($this->validation['magic_constant']) ? $this->validation['magic_constant'] : null;
        }

        /** Unset validation callable for magic constants
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetMagicConstValidator(){
            $this->validation['magic_constant'] = null;
            return $this;
        }

        /** Set validation callable for namespaces
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed namespace name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setNamespaceValidator($callable){
            $this->validation['namespace'] = $callable;
            return $this;
        }

        /** Get validation callable for namespaces
         *
         * @return  callable|null
         */
        public function getNamespaceValidator(){
            return isset($this->validation['namespace']) ? $this->validation['namespace'] : null;
        }

        /** Unset validation callable for namespaces
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetNamespaceValidator(){
            $this->validation['namespace'] = null;
            return $this;
        }

        /** Set validation callable for aliases
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed alias name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setAliasValidator($callable){
            $this->validation['alias'] = $callable;
            return $this;
        }

        /** Get validation callable for aliases
         *
         * @return  callable|null
         */
        public function getAliasValidator(){
            return isset($this->validation['alias']) ? $this->validation['alias'] : null;
        }

        /** Unset validation callable for aliases
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetAliasValidator(){
            $this->validation['alias'] = null;
            return $this;
        }

        /** Set validation callable for uses (aka aliases)
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @alias setAliasValidator();
         *
         * @param   callable        $callable   Callable that validates the passed use (aka alias) name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setUseValidator($callable){
            return $this->setAliasValidator($callable);
        }

        /** Get validation callable for uses (aka aliases)
         *
         * @alias getAliasValidator();
         *
         * @return  callable|null
         */
        public function getUseValidator(){
            return $this->getAliasValidator();
        }

        /** Unset validation callable for uses (aka aliases)
         *
         * @alias unsetAliasValidator();
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetUseValidator(){
            return $this->unsetAliasValidator();
        }

        /** Set validation callable for classes
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized class names are lowercase
         *
         * @param   callable        $callable   Callable that validates the passed class name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setClassValidator($callable){
            $this->validation['class'] = $callable;
            return $this;
        }

        /** Get validation callable for classes
         *
         * @return  callable|null
         */
        public function getClassValidator(){
            return isset($this->validation['class']) ? $this->validation['class'] : null;
        }

        /** Unset validation callable for classes
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetClassValidator(){
            $this->validation['class'] = null;
            return $this;
        }

        /** Set validation callable for interfaces
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized interface names are lowercase
         *
         * @param   callable        $callable   Callable that validates the passed interface name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setInterfaceValidator($callable){
            $this->validation['interface'] = $callable;
            return $this;
        }

        /** Get validation callable for interfaces
         *
         * @return  callable|null
         */
        public function getInterfaceValidator(){
            return isset($this->validation['interface']) ? $this->validation['interface'] : null;
        }

        /** Unset validation callable for interfaces
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetInterfaceValidator(){
            $this->validation['interface'] = null;
            return $this;
        }

        /** Set validation callable for traits
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance. NOTE: Normalized trait names are lowercase
         *
         * @param   callable        $callable   Callable that validates the passed trait name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setTraitValidator($callable){
            $this->validation['trait'] = $callable;
            return $this;
        }

        /** Get validation callable for traits
         *
         * @return  callable|null
         */
        public function getTraitValidator(){
            return isset($this->validation['trait']) ? $this->validation['trait'] : null;
        }

        /** Unset validation callable for traits
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetTraitValidator(){
            $this->validation['trait'] = null;
            return $this;
        }

        /** Set validation callable for keywords
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed keyword name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setKeywordValidator($callable){
            $this->validation['keyword'] = $callable;
            return $this;
        }

        /** Get validation callable for keywords
         *
         * @return  callable|null
         */
        public function getKeywordValidator(){
            return isset($this->validation['keyword']) ? $this->validation['keyword'] : null;
        }

        /** Unset validation callable for keywords
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetKeywordValidator(){
            $this->validation['keyword'] = null;
            return $this;
        }

        /** Set validation callable for operators
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed operator name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setOperatorValidator($callable){
            $this->validation['operator'] = $callable;
            return $this;
        }

        /** Get validation callable for operators
         *
         * @return  callable|null
         */
        public function getOperatorValidator(){
            return isset($this->validation['operator']) ? $this->validation['operator'] : null;
        }

        /** Unset validation callable for operators
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetOperatorValidator(){
            $this->validation['operator'] = null;
            return $this;
        }

        /** Set validation callable for primitives
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed primitive name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setPrimitiveValidator($callable){
            $this->validation['primitive'] = $callable;
            return $this;
        }

        /** Get validation callable for primitives
         *
         * @return  callable|null
         */
        public function getPrimitiveValidator(){
            return isset($this->validation['primitive']) ? $this->validation['primitive'] : null;
        }

        /** Unset validation callable for primitives
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function unsetPrimitiveValidator(){
            $this->validation['primitive'] = null;
            return $this;
        }

        /** Set validation callable for types
         *
         * Validator callable must accept two parameters: a string of the normalized name of the checked element,
         * and the PHPSandbox instance
         *
         * @param   callable        $callable   Callable that validates the passed type name
         *
         * @return PHPSandbox           Returns the PHPSandbox instance for fluent querying
         */
        public function setTypeValidator($callable){
            $this->validation['type'] = $callable;
            return $this;
        }

        /** Get validation callable for types
         *
         * @return  callable|null
         */
        public function getTypeValidator(){
            return isset($this->validation['type']) ? $this->validation['type'] : null;
        }

        /** Unset validation callable for types
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function unsetTypeValidator(){
            $this->validation['type'] = null;
            return $this;
        }

        /** Set PHPSandbox prepended code
         *
         * @param   string         $prepended_code      Sets a string of the prepended code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setPrependedCode($prepended_code = ''){
            $this->prepended_code = $prepended_code;
            return $this;
        }

        /** Set PHPSandbox appended code
         *
         * @param   string         $appended_code       Sets a string of the appended code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setAppendedCode($appended_code = ''){
            $this->appended_code = $appended_code;
            return $this;
        }

        /** Set PHPSandbox preparsed code
         *
         * @param   string         $preparsed_code       Sets a string of the preparsed code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setPreparsedCode($preparsed_code = ''){
            $this->preparsed_code = $preparsed_code;
            return $this;
        }

        /** Set PHPSandbox parsed AST array
         *
         * @param   array          $parsed_ast          Sets an array of the parsed AST code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setParsedAST(array $parsed_ast = []){
            $this->parsed_ast = $parsed_ast;
            return $this;
        }

        /** Set PHPSandbox prepared code
         *
         * @param   string         $prepared_code       Sets a string of the prepared code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setPreparedCode($prepared_code = ''){
            $this->prepared_code = $prepared_code;
            return $this;
        }

        /** Set PHPSandbox prepared AST array
         *
         * @param   array          $prepared_ast        Sets an array of the prepared AST code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setPreparedAST(array $prepared_ast = []){
            $this->prepared_ast = $prepared_ast;
            return $this;
        }

        /** Set PHPSandbox generated code
         *
         * @param   string         $generated_code      Sets a string of the generated code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setGeneratedCode($generated_code = ''){
            $this->generated_code = $generated_code;
            return $this;
        }

        /** Set PHPSandbox generated code
         *
         * @alias   setGeneratedCode();
         *
         * @param  string          $generated_code      Sets a string of the generated code
         *
         * @return  $this     Returns the PHPSandbox instance for fluent querying
         */
        public function setCode($generated_code = ''){
            $this->generated_code = $generated_code;
            return $this;
        }

        /** Get PHPSandbox prepended code
         * @return  string          Returns a string of the prepended code
         */
        public function getPrependedCode(){
            return $this->prepended_code;
        }

        /** Get PHPSandbox appended code
         * @return  string          Returns a string of the appended code
         */
        public function getAppendedCode(){
            return $this->appended_code;
        }

        /** Get PHPSandbox preparsed code
         * @return  string          Returns a string of the preparsed code
         */
        public function getPreparsedCode(){
            return $this->preparsed_code;
        }

        /** Get PHPSandbox parsed AST array
         * @return  array           Returns an array of the parsed AST code
         */
        public function getParsedAST(){
            return $this->parsed_ast;
        }

        /** Get PHPSandbox prepared code
         * @return  string          Returns a string of the prepared code
         */
        public function getPreparedCode(){
            return $this->prepared_code;
        }

        /** Get PHPSandbox prepared AST array
         * @return  array           Returns an array of the prepared AST code
         */
        public function getPreparedAST(){
            return $this->prepared_ast;
        }

        /** Get PHPSandbox generated code
         * @return  string          Returns a string of the generated code
         */
        public function getGeneratedCode(){
            return $this->generated_code;
        }

        /** Get PHPSandbox generated code
         * @alias   getGeneratedCode();
         * @return  string          Returns a string of the generated code
         */
        public function getCode(){
            return $this->generated_code;
        }

        /** Get PHPSandbox redefined functions in place of get_defined_functions(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $functions      Array result from get_defined_functions() is passed here
         *
         * @return  array           Returns the redefined functions array
         */
        public function _get_defined_functions(array $functions = []){
            if(count($this->whitelist['functions'])){
                $functions = [];
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
            return [];
        }

        /** Get PHPSandbox redefined variables in place of get_defined_vars(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $variables      Array result from get_defined_vars() is passed here
         *
         * @return  array           Returns the redefined variables array
         */
        public function _get_defined_vars(array $variables = []){
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
            $name = $this->normalizeSuperglobal($name);
            if(isset($this->definitions['superglobals'][$name])){
                $superglobal = $this->definitions['superglobals'][$name];
                if(is_callable($superglobal)){
                    return call_user_func_array($superglobal, [$this]);
                }
                return $superglobal;
            } else if(isset($this->whitelist['superglobals'][$name])){
                if(count($this->whitelist['superglobals'][$name])){
                    if(isset($GLOBALS[$original_name])){
                        $whitelisted_superglobal = [];
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
            return [];
        }

        /** Get PHPSandbox redefined magic constant. This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   string          $name      Requested magic constant name (e.g. __FILE__, __LINE__, etc.)
         *
         * @return  array           Returns the redefined magic constant
         */
        public function _get_magic_const($name){
            $name = $this->normalizeMagicConst($name);
            if(isset($this->definitions['magic_constants'][$name])){
                $magic_constant = $this->definitions['magic_constants'][$name];
                if(is_callable($magic_constant)){
                    return call_user_func_array($magic_constant, [$this]);
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
        public function _get_defined_constants(array $constants = []){
            if(count($this->whitelist['constants'])){
                $constants = [];
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
            return [];
        }

        /** Get PHPSandbox redefined classes in place of get_declared_classes(). This is an internal PHPSandbox function but requires public access to work.
         *
         * @param   array           $classes      Array result from get_declared_classes() is passed here
         *
         * @return  array           Returns the redefined classes
         */
        public function _get_declared_classes(array $classes = []){
            if(count($this->whitelist['classes'])){
                $classes = [];
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
                $valid_classes = [];
                foreach($classes as $class){
                    $valid_classes[$this->normalizeClass($class)] = $class;
                }
                foreach($this->definitions['classes'] as $name => $value){
                    if(class_exists($value)){
                        $valid_classes[$this->normalizeClass($name)] = $value;
                    }
                }
                foreach($valid_classes as $index => $name){
                    if(isset($this->blacklist['classes'][$this->normalizeClass($name)])){
                        unset($valid_classes[$index]);
                    }
                }
                return array_values($classes);
            }
            $classes = [];
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
        public function _get_declared_interfaces(array $interfaces = []){
            if(count($this->whitelist['interfaces'])){
                $interfaces = [];
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
                $valid_interfaces = [];
                foreach($interfaces as $interface){
                    $valid_interfaces[$this->normalizeInterface($interface)] = $interface;
                }
                foreach($this->definitions['interfaces'] as $name => $value){
                    if(interface_exists($value)){
                        $valid_interfaces[$this->normalizeInterface($name)] = $value;
                    }
                }
                foreach($valid_interfaces as $index => $name){
                    if(isset($this->blacklist['interfaces'][$this->normalizeInterface($name)])){
                        unset($valid_interfaces[$index]);
                    }
                }
                return array_values($interfaces);
            }
            $interfaces = [];
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
        public function _get_declared_traits(array $traits = []){
            if(count($this->whitelist['traits'])){
                $traits = [];
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
                $valid_traits = [];
                foreach($traits as $trait){
                    $valid_traits[$this->normalizeTrait($trait)] = $trait;
                }
                foreach($this->definitions['traits'] as $name => $value){
                    if(trait_exists($value)){
                        $valid_traits[$this->normalizeTrait($name)] = $value;
                    }
                }
                foreach($valid_traits as $index => $name){
                    if(isset($this->blacklist['traits'][$this->normalizeTrait($name)])){
                        unset($valid_traits[$index]);
                    }
                }
                return array_values($traits);
            }
            $traits = [];
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
        public function _func_get_args(array $arguments = []){
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
        public function _func_get_arg(array $arguments = [], $index = 0){
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
        public function _func_num_args(array $arguments = []){
            $count = count($arguments);
            foreach($arguments as $argument){
                if($argument instanceof self){
                    $count--;
                }
            }
            return $count > 0 ? $count : 0;
        }

        /** Get PHPSandbox redefined var_dump
         *
         * @return  array           Returns the redefined var_dump
         */
        public function _var_dump(){
            $arguments = func_get_args();
            foreach($arguments as $index => $value){
                if($value instanceof self){
                    unset($arguments[$index]); //hide PHPSandbox variable
                } else if($value instanceof SandboxedString){
                    $arguments[$index] = strval($value);
                }
            }
            return call_user_func_array('var_dump', $arguments);
        }

        /** Get PHPSandbox redefined print_r
         *
         * @return  array           Returns the redefined print_r
         */
        public function _print_r(){
            $arguments = func_get_args();
            foreach($arguments as $index => $value){
                if($value instanceof self){
                    unset($arguments[$index]); //hide PHPSandbox variable
                } else if($value instanceof SandboxedString){
                    $arguments[$index] = strval($value);
                }
            }
            return call_user_func_array('print_r', $arguments);
        }

        /** Get PHPSandbox redefined var_export
         *
         * @return  array           Returns the redefined var_export
         */
        public function _var_export(){
            $arguments = func_get_args();
            foreach($arguments as $index => $value){
                if($value instanceof self){
                    unset($arguments[$index]); //hide PHPSandbox variable
                } else if($value instanceof SandboxedString){
                    $arguments[$index] = strval($value);
                }
            }
            return call_user_func_array('var_export', $arguments);
        }

        /** Return integer value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to return as integer
         *
         * @return  int             Returns the integer value
         */
        public function _intval($value){
            return intval($value instanceof SandboxedString ? strval($value) : $value);
        }

        /** Return float value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to return as float
         *
         * @return  float           Returns the float value
         */
        public function _floatval($value){
            return floatval($value instanceof SandboxedString ? strval($value) : $value);
        }

        /** Return boolean value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to return as boolean
         *
         * @return  boolean           Returns the boolean value
         */
        public function _boolval($value){
            if($value instanceof SandboxedString){
                return (bool)strval($value);
            }
            return is_bool($value) ? $value : (bool)$value;
        }

        /** Return array value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to return as array
         *
         * @return  array           Returns the array value
         */
        public function _arrayval($value){
            if($value instanceof SandboxedString){
                return (array)strval($value);
            }
            return is_array($value) ? $value : (array)$value;
        }

        /** Return object value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to return as object
         *
         * @return  object          Returns the object value
         */
        public function _objectval($value){
            if($value instanceof SandboxedString){
                return (object)strval($value);
            }
            return is_object($value) ? $value : (object)$value;
        }

        /** Return is_string value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to check if is_string
         *
         * @return  bool            Returns the is_string value
         */
        public function _is_string($value){
            return ($value instanceof SandboxedString) ? true : is_string($value);
        }

        /** Return is_object value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to check if is_object
         *
         * @return  bool            Returns the is_object value
         */
        public function _is_object($value){
            return ($value instanceof SandboxedString) ? false : is_object($value);
        }

        /** Return is_scalar value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to check if is_scalar
         *
         * @return  bool            Returns the is_scalar value
         */
        public function _is_scalar($value){
            return ($value instanceof SandboxedString) ? true : is_scalar($value);
        }

        /** Return is_callable value of SandboxedString or mixed value
         *
         * @param   mixed           $value      Value to check if is_callable
         *
         * @return  bool            Returns the is_callable value
         */
        public function _is_callable($value){
            if($value instanceof SandboxedString){
                $value = strval($value);
            }
            return is_callable($value);
        }

        /** Return get_included_files() and sandboxed included files
         *
         * @return  array           Returns array of get_included_files() and sandboxed included files
         */
        public function _get_included_files(){
            return array_merge(get_included_files(), $this->includes);
        }

        /** Sandbox included file
         *
         * @param   string          $file      Included file to sandbox
         *
         * @return  mixed           Returns value passed from included file
         */
        public function _include($file){
            if($file instanceof SandboxedString){
                $file = strval($file);
            }
            $code = @file_get_contents($file, true);
            if($code === false){
                trigger_error("include('" . $file . "') [function.include]: failed to open stream. No such file or directory", E_USER_WARNING);
                return false;
            }
            if(!in_array($file, $this->_get_included_files())){
                $this->includes[] = $file;
            }
            return $this->execute($code, false, $file);
        }

        /** Sandbox included once file
         *
         * @param   string          $file      Included once file to sandbox
         *
         * @return  mixed           Returns value passed from included once file
         */
        public function _include_once($file){
            if($file instanceof SandboxedString){
                $file = strval($file);
            }
            if(!in_array($file, $this->_get_included_files())){
                $code = @file_get_contents($file, true);
                if($code === false){
                    trigger_error("include_once('" . $file . "') [function.include-once]: failed to open stream. No such file or directory", E_USER_WARNING);
                    return false;
                }
                $this->includes[] = $file;
                return $this->execute($code, false, $file);
            }
            return null;
        }

        /** Sandbox required file
         *
         * @param   string          $file      Required file to sandbox
         *
         * @return  mixed           Returns value passed from required file
         */
        public function _require($file){
            if($file instanceof SandboxedString){
                $file = strval($file);
            }
            $code = @file_get_contents($file, true);
            if($code === false){
                trigger_error("require('" . $file . "') [function.require]: failed to open stream. No such file or directory", E_USER_WARNING);
                trigger_error("Failed opening required '" . $file . "' (include_path='" . get_include_path() . "')", E_USER_ERROR);
                return false;
            }
            if(!in_array($file, $this->_get_included_files())){
                $this->includes[] = $file;
            }
            return $this->execute($code, false, $file);
        }

        /** Sandbox required once file
         *
         * @param   string          $file      Required once file to sandbox
         *
         * @return  mixed           Returns value passed from required once file
         */
        public function _require_once($file){
            if($file instanceof SandboxedString){
                $file = strval($file);
            }
            if(!in_array($file,  $this->_get_included_files())){
                $code = @file_get_contents($file, true);
                if($code === false){
                    trigger_error("require_once('" . $file . "') [function.require-once]: failed to open stream. No such file or directory", E_USER_WARNING);
                    trigger_error("Failed opening required '" . $file . "' (include_path='" . get_include_path() . "')", E_USER_ERROR);
                    return false;
                }
                $this->includes[] = $file;
                return $this->execute($code, false, $file);
            }
            return null;
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
            $name = $this->normalizeFunc($name);
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
            return $this->validationError("Sandboxed code attempted to call invalid function: $original_name", Error::VALID_FUNC_ERROR, null, $original_name);
        }

        /** Define PHPSandbox definitions, such as functions, constants, namespaces, etc.
         *
         * You can pass a string of the $type, $name and $value, or pass an associative array of definitions types and
         * an associative array of their corresponding values
         *
         * @param   string|array        $type       Associative array or string of definition type to define
         * @param   string|array|null   $name       Associative array or string of definition name to define
         * @param   mixed|null          $value      Value of definition to define
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
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
                        return $this->defineFunc($name, $value);
                    case 'variables':
                        return $this->defineVar($name, $value);
                    case 'superglobals':
                        return $this->defineSuperglobal($name, $value);
                    case 'constants':
                        return $this->defineConst($name, $value);
                    case 'magic_constants':
                        return $this->defineMagicConst($name, $value);
                    case 'namespaces':
                        return $this->defineNamespace($name);
                    case 'aliases':
                        return $this->defineAlias($name, $value);
                    case 'classes':
                        return $this->defineClass($name, $value);
                    case 'interfaces':
                        return $this->defineInterface($name, $value);
                    case 'traits':
                        return $this->defineTrait($name, $value);
                }
            }
            return $this;
        }
        /** Undefine PHPSandbox definitions, such as functions, constants, namespaces, etc.
         *
         * You can pass a string of the $type and $name to undefine, or pass an associative array of definitions types
         * and an array of key names to undefine
         *
         * @param   string|array    $type       Associative array or string of definition type to undefine
         * @param   string|array    $name       Associative array or string of definition name to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
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
                        return $this->undefineFunc($name);
                    case 'variables':
                        return $this->undefineVar($name);
                    case 'superglobals':
                        return $this->undefineSuperglobal($name);
                    case 'constants':
                        return $this->undefineConst($name);
                    case 'magic_constants':
                        return $this->undefineMagicConst($name);
                    case 'namespaces':
                        return $this->undefineNamespace($name);
                    case 'aliases':
                        return $this->undefineAlias($name);
                    case 'classes':
                        return $this->undefineClass($name);
                    case 'interfaces':
                        return $this->undefineInterface($name);
                    case 'traits':
                        return $this->undefineTrait($name);
                }
            }
            return $this;
        }

        /** Define PHPSandbox function
         *
         * You can pass the function $name and $function closure or callable to define, or an associative array of
         * functions to define, which can have callable values or arrays of the function callable and $pass_sandbox flag
         *
         * @param   string|array    $name           Associative array or string of function $name to define
         * @param   callable        $function       Callable to define $function to
         * @param   bool            $pass_sandbox   Pass PHPSandbox instance to defined function when called? Default is false
         *
         * @throws  Error           Throws exception if unnamed or uncallable $function is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineFunc($name, $function, $pass_sandbox = false){
            if(is_array($name)){
                return $this->defineFuncs($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed function!", Error::DEFINE_FUNC_ERROR, null, '');
            }
            if(is_array($function) && count($function)){    //so you can pass array of function names and array of function and pass_sandbox flag
                $pass_sandbox = isset($function[1]) ? $function[1] : false;
                $function = $function[0];
            }
            $original_name = $name;
            $name = $this->normalizeFunc($name);
            if(!is_callable($function)){
                $this->validationError("Cannot define uncallable function : $original_name", Error::DEFINE_FUNC_ERROR, null, $original_name);
            }
            $this->definitions['functions'][$name] = [
                'function' => $function,
                'pass_sandbox' => $pass_sandbox
            ];
            return $this;
        }

        /** Define PHPSandbox functions by array
         *
         * You can pass an associative array of functions to define
         *
         * @param   array           $functions       Associative array of $functions to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineFuncs(array $functions = []){
            foreach($functions as $name => $function){
                $this->defineFunc($name, $function);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined functions
         *
         * @return  int           Returns the number of functions this instance has defined
         */
        public function hasDefinedFuncs(){
            return count($this->definitions['functions']);
        }

        /** Check if PHPSandbox instance has $name function defined
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined function, false otherwise
         */
        public function isDefinedFunc($name){
            $name = $this->normalizeFunc($name);
            return isset($this->definitions['functions'][$name]);
        }

        /** Undefine PHPSandbox function
         *
         * You can pass a string of function $name to undefine, or pass an array of function names to undefine
         *
         * @param   string|array          $name       String of function name or array of function names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineFunc($name){
            if(is_array($name)){
                return $this->undefineFuncs($name);
            }
            $name = $this->normalizeFunc($name);
            if(isset($this->definitions['functions'][$name])){
                unset($this->definitions['functions'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox functions by array
         *
         * You can pass an array of function names to undefine, or an empty array or null argument to undefine all functions
         *
         * @param   array           $functions       Array of function names to undefine. Passing an empty array or no argument will result in undefining all functions
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineFuncs($functions = []){
            if(count($functions)){
                foreach($functions as $function){
                    $this->undefineFunc($function);
                }
            } else {
                $this->definitions['functions'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox variable
         *
         * You can pass the variable $name and $value to define, or an associative array of variables to define
         *
         * @param   string|array    $name       String of variable $name or associative array to define
         * @param   mixed           $value      Value to define variable to
         *
         * @throws  Error           Throws exception if unnamed variable is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineVar($name, $value){
            if(is_array($name)){
                return $this->defineVars($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed variable!", Error::DEFINE_VAR_ERROR, null, '');
            }
            $this->definitions['variables'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox variables by array
         *
         * You can pass an associative array of variables to define
         *
         * @param   array           $variables  Associative array of $variables to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineVars(array $variables = []){
            foreach($variables as $name => $value){
                $this->defineVar($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined variables
         *
         * @return  int           Returns the number of variables this instance has defined
         */
        public function hasDefinedVars(){
            return count($this->definitions['variables']);
        }

        /** Check if PHPSandbox instance has $name variable defined
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined variable, false otherwise
         */
        public function isDefinedVar($name){
            return isset($this->definitions['variables'][$name]);
        }

        /** Undefine PHPSandbox variable
         *
         * You can pass a string of variable $name to undefine, or an array of variable names to undefine
         *
         * @param   string|array          $name       String of variable name or an array of variable names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineVar($name){
            if(is_array($name)){
                return $this->undefineVars($name);
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
         * @param   array           $variables       Array of variable names to undefine. Passing an empty array or no argument will result in undefining all variables
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineVars(array $variables = []){
            if(count($variables)){
                foreach($variables as $variable){
                    $this->undefineVar($variable);
                }
            } else {
                $this->definitions['variables'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox superglobal
         *
         * You can pass the superglobal $name and $value to define, or an associative array of superglobals to define, or a third variable to define the $key
         *
         * @param   string|array    $name       String of superglobal $name or associative array of superglobal names to define
         * @param   mixed           $value      Value to define superglobal to, can be callable
         *
         * @throws  Error           Throws exception if unnamed superglobal is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineSuperglobal($name, $value){
            if(is_array($name)){
                return $this->defineSuperglobals($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed superglobal!", Error::DEFINE_SUPERGLOBAL_ERROR, null, '');
            }
            $name = $this->normalizeSuperglobal($name);
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
         * @param   array           $superglobals  Associative array of $superglobals to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineSuperglobals(array $superglobals = []){
            foreach($superglobals as $name => $value){
                $this->defineSuperglobal($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined superglobals, or if superglobal $name has defined keys
         *
         * @param   string|null     $name       String of superglobal $name to check for keys
         *
         * @return  int|bool        Returns the number of superglobals or superglobal keys this instance has defined, or false if invalid superglobal name specified
         */
        public function hasDefinedSuperglobals($name = null){
            $name = $name ? $this->normalizeSuperglobal($name) : null;
            return $name ? (isset($this->definitions['superglobals'][$name]) ? count($this->definitions['superglobals'][$name]) : false) : count($this->definitions['superglobals']);
        }

        /** Check if PHPSandbox instance has $name superglobal defined, or if superglobal $name key is defined
         *
         * @param   string          $name       String of superglobal $name to query
         * @param   string|null     $key        String of key to to query in superglobal
         *
         * @return  bool            Returns true if PHPSandbox instance has defined superglobal, false otherwise
         */
        public function isDefinedSuperglobal($name, $key = null){
            $name = $this->normalizeSuperglobal($name);
            return $key !== null ? isset($this->definitions['superglobals'][$name][$key]) : isset($this->definitions['superglobals'][$name]);
        }

        /** Undefine PHPSandbox superglobal or superglobal key
         *
         * You can pass a string of superglobal $name to undefine, or a superglobal $key to undefine, or an array of
         * superglobal names to undefine, or an an associative array of superglobal names and keys to undefine
         *
         * @param   string|array          $name       String of superglobal $name, or array of superglobal names, or associative array of superglobal names and keys to undefine
         * @param   string|null           $key        String of superglobal $key to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineSuperglobal($name, $key = null){
            if(is_array($name)){
                return $this->undefineSuperglobals($name);
            }
            $name = $this->normalizeSuperglobal($name);
            if($key !== null && is_array($this->definitions['superglobals'][$name])){
                if(isset($this->definitions['superglobals'][$name][$key])){
                    unset($this->definitions['superglobals'][$name][$key]);
                }
            } else if(isset($this->definitions['superglobals'][$name])){
                $this->definitions['superglobals'][$name] = [];
            }
            return $this;
        }

        /** Undefine PHPSandbox superglobals by array
         *
         * You can pass an array of superglobal names to undefine, or an associative array of superglobals names and key
         * to undefine, or an empty array or null to undefine all superglobals
         *
         * @param   array          $superglobals       Associative array of superglobal names and keys or array of superglobal names to undefine
         *
         * @return  $this          Returns the PHPSandbox instance for fluent querying
         */
        public function undefineSuperglobals(array $superglobals = []){
            if(count($superglobals)){
                foreach($superglobals as $superglobal => $name){
                    $name = $this->normalizeSuperglobal($name);
                    $this->undefineSuperglobal(is_int($superglobal) ? $name : $superglobal, is_int($superglobal) || !is_string($name) ? null : $name);
                }
            } else {
                $this->definitions['superglobals'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox constant
         *
         * You can pass the constant $name and $value to define, or an associative array of constants to define
         *
         * @param   string|array    $name       String of constant $name or associative array to define
         * @param   mixed           $value      Value to define constant to
         *
         * @throws  Error           Throws exception if unnamed constant is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineConst($name, $value){
            if(is_array($name)){
                return $this->defineConsts($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed constant!", Error::DEFINE_CONST_ERROR, null, '');
            }
            $this->definitions['constants'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox constants by array
         *
         * You can pass an associative array of constants to define
         *
         * @param   array           $constants  Associative array of $constants to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineConsts(array $constants = []){
            foreach($constants as $name => $value){
                $this->defineConst($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined constants
         *
         * @return  int           Returns the number of constants this instance has defined
         */
        public function hasDefinedConsts(){
            return count($this->definitions['constants']);
        }

        /** Check if PHPSandbox instance has $name constant defined
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined constant, false otherwise
         */
        public function isDefinedConst($name){
            return isset($this->definitions['constants'][$name]);
        }

        /** Undefine PHPSandbox constant
         *
         * You can pass a string of constant $name to undefine, or an array of constant names to undefine
         *
         * @param   string|array          $name       String of constant name or array of constant names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineConst($name){
            if(is_array($name)){
                return $this->undefineConsts($name);
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
         * @param   array           $constants       Array of constant names to undefine. Passing an empty array or no argument will result in undefining all constants
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineConsts(array $constants = []){
            if(count($constants)){
                foreach($constants as $constant){
                    $this->undefineConst($constant);
                }
            } else {
                $this->definitions['constants'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox magic constant
         *
         * You can pass the magic constant $name and $value to define, or an associative array of magic constants to define
         *
         * @param   string|array    $name       String of magic constant $name or associative array to define
         * @param   mixed           $value      Value to define magic constant to, can be callable
         *
         * @throws  Error           Throws exception if unnamed magic constant is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineMagicConst($name, $value){
            if(is_array($name)){
                return $this->defineMagicConsts($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed magic constant!", Error::DEFINE_MAGIC_CONST_ERROR, null, '');
            }
            $name = $this->normalizeMagicConst($name);
            $this->definitions['magic_constants'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox magic constants by array
         *
         * You can pass an associative array of magic constants to define
         *
         * @param   array           $magic_constants  Associative array of $magic_constants to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineMagicConsts(array $magic_constants = []){
            foreach($magic_constants as $name => $value){
                $this->defineMagicConst($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined magic constants
         *
         * @return  int           Returns the number of magic constants this instance has defined
         */
        public function hasDefinedMagicConsts(){
            return count($this->definitions['magic_constants']);
        }

        /** Check if PHPSandbox instance has $name magic constant defined
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined magic constant, false otherwise
         */
        public function isDefinedMagicConst($name){
            $name = $this->normalizeMagicConst($name);
            return isset($this->definitions['magic_constants'][$name]);
        }

        /** Undefine PHPSandbox magic constant
         *
         * You can pass an a string of magic constant $name to undefine, or array of magic constant names to undefine
         *
         * @param   string|array          $name       String of magic constant name, or array of magic constant names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineMagicConst($name){
            if(is_array($name)){
                return $this->undefineMagicConsts($name);
            }
            $name = $this->normalizeMagicConst($name);
            if(isset($this->definitions['magic_constants'][$name])){
                unset($this->definitions['magic_constants'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox magic constants by array
         *
         * You can pass an array of magic constant names to undefine, or an empty array or null argument to undefine all magic constants
         *
         * @param   array           $magic_constants       Array of magic constant names to undefine. Passing an empty array or no argument will result in undefining all magic constants
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineMagicConsts(array $magic_constants = []){
            if(count($magic_constants)){
                foreach($magic_constants as $magic_constant){
                    $this->undefineMagicConst($magic_constant);
                }
            } else {
                $this->definitions['magic_constants'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox namespace
         *
         * You can pass the namespace $name and $value to define, or an array of namespaces to define
         *
         * @param   string|array    $name       String of namespace $name, or an array of namespace names to define
         *
         * @throws  Error           Throws exception if unnamed namespace is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineNamespace($name){
            if(is_array($name)){
                return $this->defineNamespaces($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed namespace!", Error::DEFINE_NAMESPACE_ERROR, null, '');
            }
            $normalized_name = $this->normalizeNamespace($name);
            $this->definitions['namespaces'][$normalized_name] = $name;
            return $this;
        }

        /** Define PHPSandbox namespaces by array
         *
         * You can pass an array of namespaces to define
         *
         * @param   array           $namespaces  Array of $namespaces to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineNamespaces(array $namespaces = []){
            foreach($namespaces as $name){
                $this->defineNamespace($name);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined namespaces
         *
         * @return  int           Returns the number of namespaces this instance has defined
         */
        public function hasDefinedNamespaces(){
            return count($this->definitions['namespaces']);
        }

        /** Check if PHPSandbox instance has $name namespace defined
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined namespace, false otherwise
         */
        public function isDefinedNamespace($name){
            $name = $this->normalizeNamespace($name);
            return isset($this->definitions['namespaces'][$name]);
        }

        /** Get defined namespace of $name
         *
         * @param   string          $name       String of namespace $name to get
         *
         * @throws  Error           Throws an exception if an invalid namespace name is requested
         *
         * @return  string          Returns string of defined namespace value
         */
        public function getDefinedNamespace($name){
            $name = $this->normalizeNamespace($name);
            if(!isset($this->definitions['namespaces'][$name])){
                $this->validationError("Could not get undefined namespace: $name", Error::VALID_NAMESPACE_ERROR, null, $name);
            }
            return $this->definitions['namespaces'][$name];
        }

        /** Undefine PHPSandbox namespace
         *
         * You can pass a string of namespace $name to undefine, or an array of namespace names to undefine
         *
         * @param   string|array          $name       String of namespace $name, or an array of namespace names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineNamespace($name){
            if(is_array($name)){
                return $this->undefineNamespaces($name);
            }
            $name = $this->normalizeNamespace($name);
            if(isset($this->definitions['namespaces'][$name])){
                unset($this->definitions['namespaces'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox namespaces by array
         *
         * You can pass an array of namespace names to undefine, or an empty array or null argument to undefine all namespaces
         *
         * @param   array           $namespaces       Array of namespace names to undefine. Passing an empty array or no argument will result in undefining all namespaces
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineNamespaces(array $namespaces = []){
            if(count($namespaces)){
                foreach($namespaces as $namespace){
                    $this->undefineNamespace($namespace);
                }
            } else {
                $this->definitions['namespaces'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox alias
         *
         * You can pass the namespace $name and $alias to use, an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @param   string|array    $name       String of namespace $name to use, or  or an array of namespaces to use, or an associative array of namespaces and their aliases to use
         * @param   string|null     $alias      String of $alias to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineAlias($name, $alias = null){
            if(is_array($name)){
                return $this->defineAliases($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed namespace alias!", Error::DEFINE_ALIAS_ERROR, null, '');
            }
            $original_name = $name;
            $name = $this->normalizeAlias($name);
            $this->definitions['aliases'][$name] = ['original' => $original_name, 'alias' => $alias];
            return $this;
        }

        /** Define PHPSandbox aliases by array
         *
         * You can pass an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @param   array           $aliases       Array of namespaces to use, or an associative array of namespaces and their aliases to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineAliases(array $aliases = []){
            foreach($aliases as $name => $alias){
                $this->defineAlias($name, $alias);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined aliases
         *
         * @return  int           Returns the number of aliases this instance has defined
         */
        public function hasDefinedAliases(){
            return count($this->definitions['aliases']);
        }

        /** Check if PHPSandbox instance has $name alias defined
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined aliases, false otherwise
         */
        public function isDefinedAlias($name){
            $name = $this->normalizeAlias($name);
            return isset($this->definitions['aliases'][$name]);
        }

        /** Undefine PHPSandbox alias
         *
         * You can pass a string of alias $name to undefine, or an array of alias names to undefine
         *
         * @param   string|array          $name       String of alias name, or array of alias names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineAlias($name){
            if(is_array($name)){
                return $this->undefineAliases($name);
            }
            $name = $this->normalizeAlias($name);
            if(isset($this->definitions['aliases'][$name])){
                unset($this->definitions['aliases'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox aliases by array
         *
         * You can pass an array of alias names to undefine, or an empty array or null argument to undefine all aliases
         *
         * @param   array           $aliases       Array of alias names to undefine. Passing an empty array or no argument will result in undefining all aliases
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineAliases(array $aliases = []){
            if(count($aliases)){
                foreach($aliases as $alias){
                    $this->undefineAlias($alias);
                }
            } else {
                $this->definitions['aliases'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox use (or alias)
         *
         * @alias   defineAlias();
         *
         * You can pass the namespace $name and $alias to use, an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @param   string|array    $name       String of namespace $name to use, or  or an array of namespaces to use, or an associative array of namespaces and their aliases to use
         * @param   string|null     $alias      String of $alias to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineUse($name, $alias = null){
            return $this->defineAlias($name, $alias);
        }

        /** Define PHPSandbox uses (or aliases) by array
         *
         * @alias   defineAliases();
         *
         * You can pass an array of namespaces to use, or an associative array of namespaces to use and their aliases
         *
         * @param   array           $uses       Array of namespaces to use, or an associative array of namespaces and their aliases to use
         *
         * @throws  Error           Throws exception if unnamed namespace is used
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineUses(array $uses = []){
            return $this->defineAliases($uses);
        }

        /** Query whether PHPSandbox instance has defined uses (or aliases)
         *
         * @alias   hasDefinedAliases();
         *
         * @return  int           Returns the number of uses (or aliases) this instance has defined
         */
        public function hasDefinedUses(){
            return $this->hasDefinedAliases();
        }

        /** Check if PHPSandbox instance has $name uses (or alias) defined
         *
         * @alias   isDefinedAlias();
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined uses (or aliases) and false otherwise
         */
        public function isDefinedUse($name){
            return $this->isDefinedAlias($name);
        }

        /** Undefine PHPSandbox use (or alias)
         *
         * You can pass a string of use (or alias) $name to undefine, or an array of use (or alias) names to undefine
         *
         * @param   string|array          $name       String of use (or alias) name, or array of use (or alias) names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineUse($name){
            return $this->undefineAlias($name);
        }

        /** Undefine PHPSandbox uses (or aliases) by array
         *
         * @alias   undefineAliases();
         *
         * You can pass an array of use (or alias) names to undefine, or an empty array or null argument to undefine all uses (or aliases)
         *
         * @param   array           $uses       Array of use (or alias) names to undefine. Passing an empty array or no argument will result in undefining all uses (or aliases)
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineUses(array $uses = []){
            return $this->undefineAliases($uses);
        }

        /** Define PHPSandbox class
         *
         * You can pass the class $name and $value to define, or an associative array of classes to define
         *
         * @param   string|array    $name       String of class $name or associative array to define
         * @param   mixed           $value      Value to define class to
         *
         * @throws  Error           Throws exception if unnamed class is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineClass($name, $value){
            if(is_array($name)){
                return $this->defineClasses($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed class!", Error::DEFINE_CLASS_ERROR, null, '');
            }
            $name = $this->normalizeClass($name);
            $this->definitions['classes'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox classes by array
         *
         * You can pass an associative array of classes to define
         *
         * @param   array           $classes  Associative array of $classes to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineClasses(array $classes = []){
            foreach($classes as $name => $value){
                $this->defineClass($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined classes
         *
         * @return  int           Returns the number of classes this instance has defined
         */
        public function hasDefinedClasses(){
            return count($this->definitions['classes']);
        }
        /** Check if PHPSandbox instance has $name class defined
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined class, false otherwise
         */
        public function isDefinedClass($name){
            $name = $this->normalizeClass($name);
            return isset($this->definitions['classes'][$name]);
        }

        /** Get defined class of $name
         *
         * @param   string          $name       String of class $name to get
         *
         * @throws  Error           Throws an exception if an invalid class name is requested
         *
         * @return  string          Returns string of defined class value
         */
        public function getDefinedClass($name){
            $name = $this->normalizeClass($name);
            if(!isset($this->definitions['classes'][$name])){
                $this->validationError("Could not get undefined class: $name", Error::VALID_CLASS_ERROR, null, $name);
            }
            return $this->definitions['classes'][$name];
        }

        /** Undefine PHPSandbox class
         *
         * You can pass a string of class $name to undefine, or an array of class names to undefine
         *
         * @param   string|array          $name       String of class name or an array of class names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineClass($name){
            if(is_array($name)){
                return $this->undefineClasses($name);
            }
            $name = $this->normalizeClass($name);
            if(isset($this->definitions['classes'][$name])){
                unset($this->definitions['classes'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox classes by array
         *
         * You can pass an array of class names to undefine, or an empty array or null argument to undefine all classes
         *
         * @param   array           $classes       Array of class names to undefine. Passing an empty array or no argument will result in undefining all classes
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineClasses(array $classes = []){
            if(count($classes)){
                foreach($classes as $class){
                    $this->undefineClass($class);
                }
            } else {
                $this->definitions['classes'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox interface
         *
         * You can pass the interface $name and $value to define, or an associative array of interfaces to define
         *
         * @param   string|array    $name       String of interface $name or associative array to define
         * @param   mixed           $value      Value to define interface to
         *
         * @throws  Error           Throws exception if unnamed interface is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineInterface($name, $value){
            if(is_array($name)){
                return $this->defineInterfaces($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed interface!", Error::DEFINE_INTERFACE_ERROR, null, '');
            }
            $name = $this->normalizeInterface($name);
            $this->definitions['interfaces'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox interfaces by array
         *
         * You can pass an associative array of interfaces to define
         *
         * @param   array           $interfaces  Associative array of $interfaces to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineInterfaces(array $interfaces = []){
            foreach($interfaces as $name => $value){
                $this->defineInterface($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined interfaces
         *
         * @return  int           Returns the number of interfaces this instance has defined
         */
        public function hasDefinedInterfaces(){
            return count($this->definitions['interfaces']);
        }

        /** Check if PHPSandbox instance has $name interface defined
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined interface, false otherwise
         */
        public function isDefinedInterface($name){
            $name = $this->normalizeInterface($name);
            return isset($this->definitions['interfaces'][$name]);
        }

        /** Get defined interface of $name
         *
         * @param   string          $name       String of interface $name to get
         *
         * @throws  Error           Throws an exception if an invalid interface name is requested
         *
         * @return  string          Returns string of defined interface value
         */
        public function getDefinedInterface($name){
            $name = $this->normalizeInterface($name);
            if(!isset($this->definitions['interfaces'][$name])){
                $this->validationError("Could not get undefined interface: $name", Error::VALID_INTERFACE_ERROR, null, $name);
            }
            return $this->definitions['interfaces'][$name];
        }

        /** Undefine PHPSandbox interface
         *
         * You can pass a string of interface $name to undefine, or an array of interface names to undefine
         *
         * @param   string|array          $name       String of interface name or an array of interface names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineInterface($name){
            if(is_array($name)){
                return $this->undefineInterfaces($name);
            }
            $name = $this->normalizeInterface($name);
            if(isset($this->definitions['interfaces'][$name])){
                unset($this->definitions['interfaces'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox interfaces by array
         *
         * You can pass an array of interface names to undefine, or an empty array or null argument to undefine all interfaces
         *
         * @param   array           $interfaces       Array of interface names to undefine. Passing an empty array or no argument will result in undefining all interfaces
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineInterfaces(array $interfaces = []){
            if(count($interfaces)){
                foreach($interfaces as $interface){
                    $this->undefineInterface($interface);
                }
            } else {
                $this->definitions['interfaces'] = [];
            }
            return $this;
        }

        /** Define PHPSandbox trait
         *
         * You can pass the trait $name and $value to define, or an associative array of traits to define
         *
         * @param   string|array    $name       String of trait $name or associative array to define
         * @param   mixed           $value      Value to define trait to
         *
         * @throws  Error           Throws exception if unnamed trait is defined
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineTrait($name, $value){
            if(is_array($name)){
                return $this->defineTraits($name);
            }
            if(!$name){
                $this->validationError("Cannot define unnamed trait!", Error::DEFINE_TRAIT_ERROR, null, '');
            }
            $name = $this->normalizeTrait($name);
            $this->definitions['traits'][$name] = $value;
            return $this;
        }

        /** Define PHPSandbox traits by array
         *
         * You can pass an associative array of traits to define
         *
         * @param   array           $traits  Associative array of $traits to define
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function defineTraits(array $traits = []){
            foreach($traits as $name => $value){
                $this->defineTrait($name, $value);
            }
            return $this;
        }

        /** Query whether PHPSandbox instance has defined traits
         *
         * @return  int           Returns the number of traits this instance has defined
         */
        public function hasDefinedTraits(){
            return count($this->definitions['traits']);
        }

        /** Check if PHPSandbox instance has $name trait defined
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has defined trait, false otherwise
         */
        public function isDefinedTrait($name){
            $name = $this->normalizeTrait($name);
            return isset($this->definitions['traits'][$name]);
        }

        /** Get defined trait of $name
         *
         * @param   string          $name       String of trait $name to get
         *
         * @throws  Error           Throws an exception if an invalid trait name is requested
         *
         * @return  string          Returns string of defined trait value
         */
        public function getDefinedTrait($name){
            $name = $this->normalizeTrait($name);
            if(!isset($this->definitions['traits'][$name])){
                $this->validationError("Could not get undefined trait: $name", Error::VALID_TRAIT_ERROR, null, $name);
            }
            return $this->definitions['traits'][$name];
        }

        /** Undefine PHPSandbox trait
         *
         * You can pass a string of trait $name to undefine, or an array of trait names to undefine
         *
         * @param   string|array          $name       String of trait name or an array of trait names to undefine
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineTrait($name){
            if(is_array($name)){
                return $this->undefineTraits($name);
            }
            $name = $this->normalizeTrait($name);
            if(isset($this->definitions['traits'][$name])){
                unset($this->definitions['traits'][$name]);
            }
            return $this;
        }

        /** Undefine PHPSandbox traits by array
         *
         * You can pass an array of trait names to undefine, or an empty array or null argument to undefine all traits
         *
         * @param   array           $traits       Array of trait names to undefine. Passing an empty array or no argument will result in undefining all traits
         *
         * @return  $this           Returns the PHPSandbox instance for fluent querying
         */
        public function undefineTraits(array $traits = []){
            if(count($traits)){
                foreach($traits as $trait){
                    $this->undefineTrait($trait);
                }
            } else {
                $this->definitions['traits'] = [];
            }
            return $this;
        }

        /** Normalize function name.  This is an internal PHPSandbox function.
         *
         * @param   string|array          $name       String of the function $name, or array of strings to normalize
         *
         * @return  string|array          Returns the normalized function string or an array of normalized strings
         */
        protected function normalizeFunc($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeFunc($value);
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
        protected function normalizeSuperglobal($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeSuperglobal($value);
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
        protected function normalizeMagicConst($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeMagicConst($value);
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
        protected function normalizeNamespace($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeNamespace($value);
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
        protected function normalizeAlias($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeAlias($value);
                }
                return $name;
            }
            return strtolower($name);
        }

        /** Normalize use (or alias) name.  This is an internal PHPSandbox function.
         *
         * @alias   normalizeAlias();
         *
         * @param   string|array           $name       String of the use (or alias) $name, or array of strings to normalize
         *
         * @return  string|array           Returns the normalized use (or alias) string or an array of normalized strings
         */
        protected function normalizeUse($name){
            return $this->normalizeAlias($name);
        }

        /** Normalize class name.  This is an internal PHPSandbox function.
         *
         * @param   string|array           $name       String of the class $name to normalize
         *
         * @return  string|array           Returns the normalized class string or an array of normalized strings
         */
        protected function normalizeClass($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeClass($value);
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
        protected function normalizeInterface($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeInterface($value);
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
        protected function normalizeTrait($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeTrait($value);
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
        protected function normalizeKeyword($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeKeyword($value);
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
        protected function normalizeOperator($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeOperator($value);
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
        protected function normalizePrimitive($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizePrimitive($value);
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
        protected function normalizeType($name){
            if(is_array($name)){
                foreach($name as &$value){
                    $value = $this->normalizeType($value);
                }
                return $name;
            }
            return strtolower($name);
        }

        /** Whitelist PHPSandbox definitions, such as functions, constants, classes, etc. to set
         *
         * You can pass an associative array of whitelist types and their names, or a string $type and array of $names, or pass a string of the $type and $name
         *
         * @param   string|array        $type       Associative array or string of whitelist type to set
         * @param   string|array|null   $name       Array or string of whitelist name to set
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelist($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($name) && $name && isset($this->whitelist[$_type])){
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
            } else if(is_string($name) && $name && isset($this->whitelist[$type])){
                $this->whitelist[$type][$name] = true;
            }
            return $this;
        }

        /** Blacklist PHPSandbox definitions, such as functions, constants, classes, etc. to set
         *
         * You can pass an associative array of blacklist types and their names, or a string $type and array of $names, or pass a string of the $type and $name
         *
         * @param   string|array        $type       Associative array or string of blacklist type to set
         * @param   string|array|null   $name       Array or string of blacklist name to set
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklist($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($name) && $name && isset($this->blacklist[$_type])){
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
            } else if(is_string($name) && $name && isset($this->blacklist[$type])){
                $this->blacklist[$type][$name] = true;
            }
            return $this;
        }

        /** Remove PHPSandbox definitions, such as functions, constants, classes, etc. from whitelist
         *
         * You can pass an associative array of whitelist types and their names, or a string $type and array of $names, or pass a string of the $type and $name to unset
         *
         * @param   string|array        $type       Associative array or string of whitelist type to unset
         * @param   string|array|null   $name       Array or string of whitelist name to unset
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
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
         * @param   string|array        $type       Associative array or string of blacklist type to unset
         * @param   string|array|null   $name       Array or string of blacklist name to unset
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
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
         * @param   string        $type     The whitelist type to query
         *
         * @return  int           Returns the number of whitelists this instance has defined
         */
        public function hasWhitelist($type){
            return count($this->whitelist[$type]);
        }

        /** Query whether PHPSandbox instance has blacklist type.
         *
         * @param   string        $type     The blacklist type to query
         *
         * @return  int           Returns the number of blacklists this instance has defined
         */
        public function hasBlacklist($type){
            return count($this->blacklist[$type]);
        }

        /** Check if PHPSandbox instance has whitelist type and name set
         *
         * @param   string          $type       String of whitelist $type to query
         * @param   string          $name       String of whitelist $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted $type and $name, false otherwise
         */
        public function isWhitelisted($type, $name){
            return isset($this->whitelist[$type][$name]);
        }

        /** Check if PHPSandbox instance has blacklist type and name set
         *
         * @param   string          $type       String of blacklist $type to query
         * @param   string          $name       String of blacklist $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted $type and $name, false otherwise
         */
        public function isBlacklisted($type, $name){
            return isset($this->blacklist[$type][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted functions.
         *
         * @return  int           Returns the number of whitelisted functions this instance has defined
         */
        public function hasWhitelistedFuncs(){
            return count($this->whitelist['functions']);
        }

        /** Query whether PHPSandbox instance has blacklisted functions.
         *
         * @return  int           Returns the number of blacklisted functions this instance has defined
         */
        public function hasBlacklistedFuncs(){
            return count($this->blacklist['functions']);
        }

        /** Check if PHPSandbox instance has whitelisted function name set
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted function $name, false otherwise
         */
        public function isWhitelistedFunc($name){
            $name = $this->normalizeFunc($name);
            return isset($this->whitelist['functions'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted function name set
         *
         * @param   string          $name       String of function $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted function $name, false otherwise
         */
        public function isBlacklistedFunc($name){
            $name = $this->normalizeFunc($name);
            return isset($this->blacklist['functions'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted variables.
         *
         * @return  int           Returns the number of whitelisted variables this instance has defined
         */
        public function hasWhitelistedVars(){
            return count($this->whitelist['variables']);
        }

        /** Query whether PHPSandbox instance has blacklisted variables.
         *
         * @return  int           Returns the number of blacklisted variables this instance has defined
         */
        public function hasBlacklistedVars(){
            return count($this->blacklist['variables']);
        }

        /** Check if PHPSandbox instance has whitelisted variable name set
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted variable $name, false otherwise
         */
        public function isWhitelistedVar($name){
            return isset($this->whitelist['variables'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted variable name set
         *
         * @param   string          $name       String of variable $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted variable $name, false otherwise
         */
        public function isBlacklistedVar($name){
            return isset($this->blacklist['variables'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted globals.
         *
         * @return  int           Returns the number of whitelisted globals this instance has defined
         */
        public function hasWhitelistedGlobals(){
            return count($this->whitelist['globals']);
        }

        /** Query whether PHPSandbox instance has blacklisted globals.
         *
         * @return  int           Returns the number of blacklisted globals this instance has defined
         */
        public function hasBlacklistedGlobals(){
            return count($this->blacklist['globals']);
        }

        /** Check if PHPSandbox instance has whitelisted global name set
         *
         * @param   string          $name       String of global $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted global $name, false otherwise
         */
        public function isWhitelistedGlobal($name){
            return isset($this->whitelist['globals'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted global name set
         *
         * @param   string          $name       String of global $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted global $name, false otherwise
         */
        public function isBlacklistedGlobal($name){
            return isset($this->blacklist['globals'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted superglobals, or superglobal keys
         *
         * @param   string        $name     The whitelist superglobal key to query
         *
         * @return  int           Returns the number of whitelisted superglobals or superglobal keys this instance has defined
         */
        public function hasWhitelistedSuperglobals($name = null){
            $name = $this->normalizeSuperglobal($name);
            return $name !== null ? (isset($this->whitelist['superglobals'][$name]) ? count($this->whitelist['superglobals'][$name]) : 0) : count($this->whitelist['superglobals']);
        }

        /** Query whether PHPSandbox instance has blacklisted superglobals, or superglobal keys
         *
         * @param   string        $name     The blacklist superglobal key to query
         *
         * @return  int           Returns the number of blacklisted superglobals or superglobal keys this instance has defined
         */
        public function hasBlacklistedSuperglobals($name = null){
            $name = $this->normalizeSuperglobal($name);
            return $name !== null ? (isset($this->blacklist['superglobals'][$name]) ? count($this->blacklist['superglobals'][$name]) : 0) : count($this->blacklist['superglobals']);
        }

        /** Check if PHPSandbox instance has whitelisted superglobal or superglobal key set
         *
         * @param   string          $name       String of whitelisted superglobal $name to query
         * @param   string          $key        String of whitelisted superglobal $key to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted superglobal key or superglobal, false otherwise
         */
        public function isWhitelistedSuperglobal($name, $key = null){
            $name = $this->normalizeSuperglobal($name);
            return $key !== null ? isset($this->whitelist['superglobals'][$name][$key]) : isset($this->whitelist['superglobals'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted superglobal or superglobal key set
         *
         * @param   string          $name       String of blacklisted superglobal $name to query
         * @param   string          $key        String of blacklisted superglobal $key to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted superglobal key or superglobal, false otherwise
         */
        public function isBlacklistedSuperglobal($name, $key = null){
            $name = $this->normalizeSuperglobal($name);
            return $key !== null ? isset($this->blacklist['superglobals'][$name][$key]) : isset($this->blacklist['superglobals'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted constants.
         *
         * @return  int           Returns the number of whitelisted constants this instance has defined
         */
        public function hasWhitelistedConsts(){
            return count($this->whitelist['constants']);
        }

        /** Query whether PHPSandbox instance has blacklisted constants.
         *
         * @return  int           Returns the number of blacklisted constants this instance has defined
         */
        public function hasBlacklistedConsts(){
            return count($this->blacklist['constants']);
        }

        /** Check if PHPSandbox instance has whitelisted constant name set
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted constant $name, false otherwise
         */
        public function isWhitelistedConst($name){
            return isset($this->whitelist['constants'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted constant name set
         *
         * @param   string          $name       String of constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted constant $name, false otherwise
         */
        public function isBlacklistedConst($name){
            return isset($this->blacklist['constants'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted magic constants.
         *
         * @return  int           Returns the number of whitelisted magic constants this instance has defined
         */
        public function hasWhitelistedMagicConsts(){
            return count($this->whitelist['magic_constants']);
        }

        /** Query whether PHPSandbox instance has blacklisted magic constants.
         *
         * @return  int           Returns the number of blacklisted magic constants this instance has defined
         */
        public function hasBlacklistedMagicConsts(){
            return count($this->blacklist['magic_constants']);
        }

        /** Check if PHPSandbox instance has whitelisted magic constant name set
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted magic constant $name, false otherwise
         */
        public function isWhitelistedMagicConst($name){
            $name = $this->normalizeMagicConst($name);
            return isset($this->whitelist['magic_constants'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted magic constant name set
         *
         * @param   string          $name       String of magic constant $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted magic constant $name, false otherwise
         */
        public function isBlacklistedMagicConst($name){
            $name = $this->normalizeMagicConst($name);
            return isset($this->blacklist['magic_constants'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted namespaces.
         *
         * @return  int           Returns the number of whitelisted namespaces this instance has defined
         */
        public function hasWhitelistedNamespaces(){
            return count($this->whitelist['namespaces']);
        }

        /** Query whether PHPSandbox instance has blacklisted namespaces.
         *
         * @return  int           Returns the number of blacklisted namespaces this instance has defined
         */
        public function hasBlacklistedNamespaces(){
            return count($this->blacklist['namespaces']);
        }

        /** Check if PHPSandbox instance has whitelisted namespace name set
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted namespace $name, false otherwise
         */
        public function isWhitelistedNamespace($name){
            $name = $this->normalizeNamespace($name);
            return isset($this->whitelist['namespaces'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted namespace name set
         *
         * @param   string          $name       String of namespace $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted namespace $name, false otherwise
         */
        public function isBlacklistedNamespace($name){
            $name = $this->normalizeNamespace($name);
            return isset($this->blacklist['namespaces'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted aliases.
         *
         * @return  int           Returns the number of whitelisted aliases this instance has defined
         */
        public function hasWhitelistedAliases(){
            return count($this->whitelist['aliases']);
        }

        /** Query whether PHPSandbox instance has blacklisted aliases.
         *
         * @return  int           Returns the number of blacklisted aliases this instance has defined
         */
        public function hasBlacklistedAliases(){
            return count($this->blacklist['aliases']);
        }

        /** Check if PHPSandbox instance has whitelisted alias name set
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted alias $name, false otherwise
         */
        public function isWhitelistedAlias($name){
            $name = $this->normalizeAlias($name);
            return isset($this->whitelist['aliases'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted alias name set
         *
         * @param   string          $name       String of alias $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted alias $name, false otherwise
         */
        public function isBlacklistedAlias($name){
            $name = $this->normalizeAlias($name);
            return isset($this->blacklist['aliases'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted uses (or aliases.)
         *
         * @alias   hasWhitelistedAliases();
         *
         * @return  int           Returns the number of whitelisted uses (or aliases) this instance has defined
         */
        public function hasWhitelistedUses(){
            return $this->hasWhitelistedAliases();
        }

        /** Query whether PHPSandbox instance has blacklisted uses (or aliases.)
         *
         * @alias   hasBlacklistedAliases();
         *
         * @return  int           Returns the number of blacklisted uses (or aliases) this instance has defined
         */
        public function hasBlacklistedUses(){
            return $this->hasBlacklistedAliases();
        }

        /** Check if PHPSandbox instance has whitelisted use (or alias) name set
         *
         * @alias   isWhitelistedAlias();
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted use (or alias) $name, false otherwise
         */
        public function isWhitelistedUse($name){
            return $this->isWhitelistedAlias($name);
        }

        /** Check if PHPSandbox instance has blacklisted use (or alias) name set
         *
         * @alias   isBlacklistedAlias();
         *
         * @param   string          $name       String of use (or alias) $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted use (or alias) $name, false otherwise
         */
        public function isBlacklistedUse($name){
            return $this->isBlacklistedAlias($name);
        }

        /** Query whether PHPSandbox instance has whitelisted classes.
         *
         * @return  int           Returns the number of whitelisted classes this instance has defined
         */
        public function hasWhitelistedClasses(){
            return count($this->whitelist['classes']);
        }

        /** Query whether PHPSandbox instance has blacklisted classes.
         *
         * @return  int           Returns the number of blacklisted classes this instance has defined
         */
        public function hasBlacklistedClasses(){
            return count($this->blacklist['classes']);
        }

        /** Check if PHPSandbox instance has whitelisted class name set
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted class $name, false otherwise
         */
        public function isWhitelistedClass($name){
            $name = $this->normalizeClass($name);
            return isset($this->whitelist['classes'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted class name set
         *
         * @param   string          $name       String of class $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted class $name, false otherwise
         */
        public function isBlacklistedClass($name){
            $name = $this->normalizeClass($name);
            return isset($this->blacklist['classes'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted interfaces.
         *
         * @return  int           Returns the number of whitelisted interfaces this instance has defined
         */
        public function hasWhitelistedInterfaces(){
            return count($this->whitelist['interfaces']);
        }

        /** Query whether PHPSandbox instance has blacklisted interfaces.
         *
         * @return  int           Returns the number of blacklisted interfaces this instance has defined
         */
        public function hasBlacklistedInterfaces(){
            return count($this->blacklist['interfaces']);
        }

        /** Check if PHPSandbox instance has whitelisted interface name set
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted interface $name, false otherwise
         */
        public function isWhitelistedInterface($name){
            $name = $this->normalizeInterface($name);
            return isset($this->whitelist['interfaces'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted interface name set
         *
         * @param   string          $name       String of interface $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted interface $name, false otherwise
         */
        public function isBlacklistedInterface($name){
            $name = $this->normalizeInterface($name);
            return isset($this->blacklist['interfaces'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted traits.
         *
         * @return  int           Returns the number of whitelisted traits this instance has defined
         */
        public function hasWhitelistedTraits(){
            return count($this->whitelist['traits']);
        }

        /** Query whether PHPSandbox instance has blacklisted traits.
         *
         * @return  int           Returns the number of blacklisted traits this instance has defined
         */
        public function hasBlacklistedTraits(){
            return count($this->blacklist['traits']);
        }

        /** Check if PHPSandbox instance has whitelisted trait name set
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted trait $name, false otherwise
         */
        public function isWhitelistedTrait($name){
            $name = $this->normalizeTrait($name);
            return isset($this->whitelist['traits'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted trait name set
         *
         * @param   string          $name       String of trait $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted trait $name, false otherwise
         */
        public function isBlacklistedTrait($name){
            $name = $this->normalizeTrait($name);
            return isset($this->blacklist['traits'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted keywords.
         *
         * @return  int           Returns the number of whitelisted keywords this instance has defined
         */
        public function hasWhitelistKeywords(){
            return count($this->whitelist['keywords']);
        }

        /** Query whether PHPSandbox instance has blacklisted keywords.
         *
         * @return  int           Returns the number of blacklisted keywords this instance has defined
         */
        public function hasBlacklistedKeywords(){
            return count($this->blacklist['keywords']);
        }

        /** Check if PHPSandbox instance has whitelisted keyword name set
         *
         * @param   string          $name       String of keyword $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted keyword $name, false otherwise
         */
        public function isWhitelistedKeyword($name){
            $name = $this->normalizeKeyword($name);
            return isset($this->whitelist['keywords'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted keyword name set
         *
         * @param   string          $name       String of keyword $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted keyword $name, false otherwise
         */
        public function isBlacklistedKeyword($name){
            $name = $this->normalizeKeyword($name);
            return isset($this->blacklist['keywords'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted operators.
         *
         * @return  int           Returns the number of whitelisted operators this instance has defined
         */
        public function hasWhitelistedOperators(){
            return count($this->whitelist['operators']);
        }

        /** Query whether PHPSandbox instance has blacklisted operators.
         *
         * @return  int           Returns the number of blacklisted operators this instance has defined
         */
        public function hasBlacklistedOperators(){
            return count($this->blacklist['operators']);
        }

        /** Check if PHPSandbox instance has whitelisted operator name set
         *
         * @param   string          $name       String of operator $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted operator $name, false otherwise
         */
        public function isWhitelistedOperator($name){
            $name = $this->normalizeOperator($name);
            return isset($this->whitelist['operators'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted operator name set
         *
         * @param   string          $name       String of operator $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted operator $name, false otherwise
         */
        public function isBlacklistedOperator($name){
            $name = $this->normalizeOperator($name);
            return isset($this->blacklist['operators'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted primitives.
         *
         * @return  int           Returns the number of whitelisted primitives this instance has defined
         */
        public function hasWhitelistedPrimitives(){
            return count($this->whitelist['primitives']);
        }

        /** Query whether PHPSandbox instance has blacklisted primitives.
         *
         * @return  int           Returns the number of blacklisted primitives this instance has defined
         */
        public function hasBlacklistedPrimitives(){
            return count($this->blacklist['primitives']);
        }

        /** Check if PHPSandbox instance has whitelisted primitive name set
         *
         * @param   string          $name       String of primitive $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted primitive $name, false otherwise
         */
        public function isWhitelistedPrimitive($name){
            $name = $this->normalizePrimitive($name);
            return isset($this->whitelist['primitives'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted primitive name set
         *
         * @param   string          $name       String of primitive $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted primitive $name, false otherwise
         */
        public function isBlacklistedPrimitive($name){
            $name = $this->normalizePrimitive($name);
            return isset($this->blacklist['primitives'][$name]);
        }

        /** Query whether PHPSandbox instance has whitelisted types.
         *
         * @return  int           Returns the number of whitelisted types this instance has defined
         */
        public function hasWhitelistedTypes(){
            return count($this->whitelist['types']);
        }

        /** Query whether PHPSandbox instance has blacklisted types.
         *
         * @return  int           Returns the number of blacklisted types this instance has defined
         */
        public function hasBlacklistedTypes(){
            return count($this->blacklist['types']);
        }

        /** Check if PHPSandbox instance has whitelisted type name set
         *
         * @param   string          $name       String of type $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has whitelisted type $name, false otherwise
         */
        public function isWhitelistedType($name){
            $name = $this->normalizeType($name);
            return isset($this->whitelist['types'][$name]);
        }

        /** Check if PHPSandbox instance has blacklisted type name set
         *
         * @param   string          $name       String of type $name to query
         *
         * @return  bool            Returns true if PHPSandbox instance has blacklisted type $name, false otherwise
         */
        public function isBlacklistedType($name){
            $name = $this->normalizeType($name);
            return isset($this->blacklist['types'][$name]);
        }

        /** Whitelist function
         *
         * You can pass a string of the function name, or pass an array of function names to whitelist
         *
         * @param   string|array        $name       String of function name, or array of function names to whitelist
         *
         * @return  $this          Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistFunc($name){
            if(func_num_args() > 1){
                return $this->whitelistFunc(func_get_args());
            }
            $name = $this->normalizeFunc($name);
            return $this->whitelist('functions', $name);
        }

        /** Blacklist function
         *
         * You can pass a string of the function name, or pass an array of function names to blacklist
         *
         * @param   string|array        $name       String of function name, or array of function names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistFunc($name){
            if(func_num_args() > 1){
                return $this->blacklistFunc(func_get_args());
            }
            $name = $this->normalizeFunc($name);
            return $this->blacklist('functions', $name);
        }

        /** Remove function from whitelist
         *
         * You can pass a string of the function name, or pass an array of function names to remove from whitelist
         *
         * @param   string|array        $name       String of function name or array of function names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistFunc($name){
            if(func_num_args() > 1){
                return $this->dewhitelistFunc(func_get_args());
            }
            $name = $this->normalizeFunc($name);
            return $this->dewhitelist('functions', $name);
        }

        /** Remove function from blacklist
         *
         * You can pass a string of the function name, or pass an array of function names to remove from blacklist
         *
         * @param   string|array        $name       String of function name or array of function names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistFunc($name){
            if(func_num_args() > 1){
                return $this->deblacklistFunc(func_get_args());
            }
            $name = $this->normalizeFunc($name);
            return $this->deblacklist('functions', $name);
        }

        /** Whitelist variable
         *
         * You can pass a string of variable name, or pass an array of the variable names to whitelist
         *
         * @param   string|array        $name       String of variable name or array of variable names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistVar($name){
            if(func_num_args() > 1){
                return $this->whitelistVar(func_get_args());
            }
            return $this->whitelist('variables', $name);
        }

        /** Blacklist variable
         *
         * You can pass a string of variable name, or pass an array of the variable names to blacklist
         *
         * @param   string|array        $name       String of variable name or array of variable names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistVar($name){
            if(func_num_args() > 1){
                return $this->blacklistVar(func_get_args());
            }
            return $this->blacklist('variables', $name);
        }

        /** Remove variable from whitelist
         *
         * You can pass a string of variable name, or pass an array of the variable names to remove from whitelist
         *
         * @param   string|array        $name       String of variable name or array of variable names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistVar($name){
            if(func_num_args() > 1){
                return $this->dewhitelistVar(func_get_args());
            }
            return $this->dewhitelist('variables', $name);
        }

        /** Remove function from blacklist
         *
         * You can pass a string of variable name, or pass an array of the variable names to remove from blacklist
         *
         * @param   string|array        $name       String of variable name or array of variable names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistVar($name){
            if(func_num_args() > 1){
                return $this->deblacklistVar(func_get_args());
            }
            return $this->deblacklist('variables', $name);
        }

        /** Whitelist global
         *
         * You can pass a string of global name, or pass an array of the global names to whitelist
         *
         * @param   string|array        $name       String of global name or array of global names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistGlobal($name){
            if(func_num_args() > 1){
                return $this->whitelistGlobal(func_get_args());
            }
            return $this->whitelist('globals', $name);
        }

        /** Blacklist global
         *
         * You can pass a string of global name, or pass an array of the global names to blacklist
         *
         * @param   string|array        $name       String of global name or array of global names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistGlobal($name){
            if(func_num_args() > 1){
                return $this->blacklistGlobal(func_get_args());
            }
            return $this->blacklist('globals', $name);
        }

        /** Remove global from whitelist
         *
         * You can pass a string of global name, or pass an array of the global names to remove from whitelist
         *
         * @param   string|array        $name       String of global name or array of global names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistGlobal($name){
            if(func_num_args() > 1){
                return $this->dewhitelistGlobal(func_get_args());
            }
            return $this->dewhitelist('globals', $name);
        }

        /** Remove global from blacklist
         *
         * You can pass a string of global name, or pass an array of the global names to remove from blacklist
         *
         * @param   string|array        $name       String of global name or array of global names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistGlobal($name){
            if(func_num_args() > 1){
                return $this->deblacklistGlobal(func_get_args());
            }
            return $this->deblacklist('globals', $name);
        }

        /** Whitelist superglobal or superglobal key
         *
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to whitelist
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to whitelist
         * @param   string              $key        String of superglobal key to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistSuperglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalizeSuperglobal($name);
            }
            if(is_string($name) && $name && !isset($this->whitelist['superglobals'][$name])){
                $this->whitelist['superglobals'][$name] = [];
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(is_string($key) && $key){
                            $this->whitelist['superglobals'][$key] = [];
                        }
                    } else {
                        $_name = $this->normalizeSuperglobal($_name);
                        if(is_string($_name) && $_name && !isset($this->whitelist['superglobals'][$_name])){
                            $this->whitelist['superglobals'][$_name] = [];
                        }
                        if(is_string($key) && $key && isset($this->whitelist['superglobals'][$_name])){
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
            } else if(is_string($key) && $key && isset($this->whitelist['superglobals'][$name])){
                $this->whitelist['superglobals'][$name][$key] = true;
            }
            return $this;
        }

        /** Blacklist superglobal or superglobal key
         **
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to blacklist
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to blacklist
         * @param   string              $key        String of superglobal key to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistSuperglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalizeSuperglobal($name);
            }
            if(is_string($name) && $name && !isset($this->blacklist['superglobals'][$name])){
                $this->blacklist['superglobals'][$name] = [];
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(is_string($key) && $key){
                            $this->blacklist['superglobals'][$key] = [];
                        }
                    } else {
                        $_name = $this->normalizeSuperglobal($_name);
                        if(is_string($_name) && $_name && !isset($this->blacklist['superglobals'][$_name])){
                            $this->blacklist['superglobals'][$_name] = [];
                        }
                        if(is_string($key) && $key && isset($this->blacklist['superglobals'][$_name])){
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
            } else if(is_string($key) && $key && isset($this->blacklist['superglobals'][$name])){
                $this->blacklist['superglobals'][$name][$key] = true;
            }
            return $this;
        }

        /** Remove superglobal or superglobal key from whitelist
         **
         * You can pass a string of the superglobal name, or a string of the superglobal name and a string of the key,
         * or pass an array of superglobal names, or an associative array of superglobal names and their keys to remove from whitelist
         *
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to remove from whitelist
         * @param   string              $key        String of superglobal key to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistSuperglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalizeSuperglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(isset($this->whitelist['superglobals'][$key])){
                            $this->whitelist['superglobals'][$key] = [];
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
         * @param   string|array        $name       String of superglobal name, or an array of superglobal names, or an associative array of superglobal names and their keys to remove from blacklist
         * @param   string              $key        String of superglobal key to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistSuperglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalizeSuperglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(is_int($_name)){
                        if(isset($this->blacklist['superglobals'][$key])){
                            $this->blacklist['superglobals'][$key] = [];
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
         * @param   string|array        $name       String of constant name or array of constant names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistConst($name){
            if(func_num_args() > 1){
                return $this->whitelistConst(func_get_args());
            }
            return $this->whitelist('constants', $name);
        }

        /** Blacklist constant
         *
         * You can pass a string of constant name, or pass an array of the constant names to blacklist
         *
         * @param   string|array        $name       String of constant name or array of constant names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistConst($name){
            if(func_num_args() > 1){
                return $this->blacklistConst(func_get_args());
            }
            return $this->blacklist('constants', $name);
        }

        /** Remove constant from whitelist
         *
         * You can pass a string of constant name, or pass an array of the constant names to remove from whitelist
         *
         * @param   string|array        $name       String of constant name or array of constant names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistConst($name){
            if(func_num_args() > 1){
                return $this->dewhitelistConst(func_get_args());
            }
            return $this->dewhitelist('constants', $name);
        }

        /** Remove constant from blacklist
         *
         * You can pass a string of constant name, or pass an array of the constant names to remove from blacklist
         *
         * @param   string|array        $name       String of constant name or array of constant names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistConst($name){
            if(func_num_args() > 1){
                return $this->deblacklistConst(func_get_args());
            }
            return $this->deblacklist('constants', $name);
        }

        /** Whitelist magic constant
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to whitelist
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistMagicConst($name){
            if(func_num_args() > 1){
                return $this->whitelistMagicConst(func_get_args());
            }
            $name = $this->normalizeMagicConst($name);
            return $this->whitelist('magic_constants', $name);
        }

        /** Blacklist magic constant
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to blacklist
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistMagicConst($name){
            if(func_num_args() > 1){
                return $this->blacklistMagicConst(func_get_args());
            }
            $name = $this->normalizeMagicConst($name);
            return $this->blacklist('magic_constants', $name);
        }

        /** Remove magic constant from whitelist
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to remove from whitelist
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistMagicConst($name){
            if(func_num_args() > 1){
                return $this->dewhitelistMagicConst(func_get_args());
            }
            $name = $this->normalizeMagicConst($name);
            return $this->dewhitelist('magic_constants', $name);
        }

        /** Remove magic constant from blacklist
         *
         * You can pass a string of magic constant name, or pass an array of the magic constant names to remove from blacklist
         *
         * @param   string|array        $name       String of magic constant name or array of magic constant names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistMagicConst($name){
            if(func_num_args() > 1){
                return $this->deblacklistMagicConst(func_get_args());
            }
            $name = $this->normalizeMagicConst($name);
            return $this->deblacklist('magic_constants', $name);
        }

        /** Whitelist namespace
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to whitelist
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistNamespace($name){
            if(func_num_args() > 1){
                return $this->whitelistNamespace(func_get_args());
            }
            $name = $this->normalizeNamespace($name);
            return $this->whitelist('namespaces', $name);
        }

        /** Blacklist namespace
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to blacklist
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistNamespace($name){
            if(func_num_args() > 1){
                return $this->blacklistNamespace(func_get_args());
            }
            $name = $this->normalizeNamespace($name);
            return $this->blacklist('namespaces', $name);
        }

        /** Remove namespace from whitelist
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to remove from whitelist
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistNamespace($name){
            if(func_num_args() > 1){
                return $this->dewhitelistNamespace(func_get_args());
            }
            $name = $this->normalizeNamespace($name);
            return $this->dewhitelist('namespaces', $name);
        }

        /** Remove namespace from blacklist
         *
         * You can pass a string of namespace name, or pass an array of the namespace names to remove from blacklist
         *
         * @param   string|array        $name       String of namespace name or array of namespace names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistNamespace($name){
            if(func_num_args() > 1){
                return $this->deblacklistNamespace(func_get_args());
            }
            $name = $this->normalizeNamespace($name);
            return $this->deblacklist('namespaces', $name);
        }

        /** Whitelist alias
         *
         * You can pass a string of alias name, or pass an array of the alias names to whitelist
         *
         * @param   string|array        $name       String of alias names  or array of alias names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistAlias($name){
            if(func_num_args() > 1){
                return $this->whitelistAlias(func_get_args());
            }
            $name = $this->normalizeAlias($name);
            return $this->whitelist('aliases', $name);
        }

        /** Blacklist alias
         *
         * You can pass a string of alias name, or pass an array of the alias names to blacklist
         *
         * @param   string|array        $name       String of alias name or array of alias names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistAlias($name){
            if(func_num_args() > 1){
                return $this->blacklistAlias(func_get_args());
            }
            $name = $this->normalizeAlias($name);
            return $this->blacklist('aliases', $name);
        }

        /** Remove alias from whitelist
         *
         * You can pass a string of alias name, or pass an array of the alias names to remove from whitelist
         *
         * @param   string|array        $name       String of alias name or array of alias names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistAlias($name){
            if(func_num_args() > 1){
                return $this->dewhitelistAlias(func_get_args());
            }
            $name = $this->normalizeAlias($name);
            return $this->dewhitelist('aliases', $name);
        }

        /** Remove alias from blacklist
         *
         * You can pass a string of alias name, or pass an array of the alias names to remove from blacklist
         *
         * @param   string|array        $name       String of alias name or array of alias names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistAlias($name){
            if(func_num_args() > 1){
                return $this->deblacklistAlias(func_get_args());
            }
            $name = $this->normalizeAlias($name);
            return $this->deblacklist('aliases', $name);
        }

        /** Whitelist use (or alias)
         *
         * You can pass a string of use (or alias) name, or pass an array of the use (or alias) names to whitelist
         *
         * @alias   whitelistAlias();
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistUse($name){
            if(func_num_args() > 1){
                return $this->whitelistAlias(func_get_args());
            }
            return $this->whitelistAlias($name);
        }

        /** Blacklist use (or alias)
         *
         * You can pass a string of use (or alias) name, or pass an array of the use (or alias) names to blacklist
         *
         * @alias   blacklistAlias();
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistUse($name){
            if(func_num_args() > 1){
                return $this->blacklistAlias(func_get_args());
            }
            return $this->blacklistAlias($name);
        }

        /** Remove use (or alias) from whitelist
         *
         * You can pass a string of use (or alias name, or pass an array of the use (or alias) names to remove from whitelist
         *
         * @alias   dewhitelistAlias();
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistUse($name){
            if(func_num_args() > 1){
                return $this->dewhitelistAlias(func_get_args());
            }
            return $this->dewhitelistAlias($name);
        }

        /** Remove use (or alias) from blacklist
         *
         * You can pass a string of use (or alias name, or pass an array of the use (or alias) names to remove from blacklist
         *
         * @alias   deblacklistAlias();
         *
         * @param   string|array        $name       String of use (or alias) name or array of use (or alias) names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistUse($name){
            if(func_num_args() > 1){
                return $this->deblacklistAlias(func_get_args());
            }
            return $this->deblacklistAlias($name);
        }

        /** Whitelist class
         *
         * You can pass a string of class name, or pass an array of the class names to whitelist
         *
         * @param   string|array        $name       String of class name or array of class names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistClass($name){
            if(func_num_args() > 1){
                return $this->whitelistClass(func_get_args());
            }
            $name = $this->normalizeClass($name);
            return $this->whitelist('classes', $name);
        }

        /** Blacklist class
         *
         * You can pass a string of class name, or pass an array of the class names to blacklist
         *
         * @param   string|array        $name       String of class name or array of class names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistClass($name){
            if(func_num_args() > 1){
                return $this->blacklistClass(func_get_args());
            }
            $name = $this->normalizeClass($name);
            return $this->blacklist('classes', $name);
        }

        /** Remove class from whitelist
         *
         * You can pass a string of class name, or pass an array of the class names to remove from whitelist
         *
         * @param   string|array        $name       String of class name or array of class names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistClass($name){
            if(func_num_args() > 1){
                return $this->dewhitelistClass(func_get_args());
            }
            $name = $this->normalizeClass($name);
            return $this->dewhitelist('classes', $name);
        }

        /** Remove class from blacklist
         *
         * You can pass a string of class name, or pass an array of the class names to remove from blacklist
         *
         * @param   string|array        $name       String of class name or array of class names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistClass($name){
            if(func_num_args() > 1){
                return $this->deblacklistClass(func_get_args());
            }
            $name = $this->normalizeClass($name);
            return $this->deblacklist('classes', $name);
        }

        /** Whitelist interface
         *
         * You can pass a string of interface name, or pass an array of the interface names to whitelist
         *
         * @param   string|array        $name       String of interface name or array of interface names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistInterface($name){
            if(func_num_args() > 1){
                return $this->whitelistInterface(func_get_args());
            }
            $name = $this->normalizeInterface($name);
            return $this->whitelist('interfaces', $name);
        }

        /** Blacklist interface
         *
         * You can pass a string of interface name, or pass an array of the interface names to blacklist
         *
         * @param   string|array        $name       String of interface name or array of interface names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistInterface($name){
            if(func_num_args() > 1){
                return $this->blacklistInterface(func_get_args());
            }
            $name = $this->normalizeInterface($name);
            return $this->blacklist('interfaces', $name);
        }

        /** Remove interface from whitelist
         *
         * You can pass a string of interface name, or pass an array of the interface names to remove from whitelist
         *
         * @param   string|array        $name       String of interface name or array of interface names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistInterface($name){
            if(func_num_args() > 1){
                return $this->dewhitelistInterface(func_get_args());
            }
            $name = $this->normalizeInterface($name);
            return $this->dewhitelist('interfaces', $name);
        }

        /** Remove interface from blacklist
         *
         * You can pass a string of interface name, or pass an array of the interface names to remove from blacklist
         *
         * @param   string|array        $name       String of interface name or array of interface names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistInterface($name){
            if(func_num_args() > 1){
                return $this->deblacklistInterface(func_get_args());
            }
            $name = $this->normalizeInterface($name);
            return $this->deblacklist('interfaces', $name);
        }

        /** Whitelist trait
         *
         * You can pass a string of trait name, or pass an array of the trait names to whitelist
         *
         * @param   string|array        $name       String of trait name or array of trait names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistTrait($name){
            if(func_num_args() > 1){
                return $this->whitelistTrait(func_get_args());
            }
            $name = $this->normalizeTrait($name);
            return $this->whitelist('traits', $name);
        }

        /** Blacklist trait
         *
         * You can pass a string of trait name, or pass an array of the trait names to blacklist
         *
         * @param   string|array        $name       String of trait name or array of trait names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistTrait($name){
            if(func_num_args() > 1){
                return $this->blacklistTrait(func_get_args());
            }
            $name = $this->normalizeTrait($name);
            return $this->blacklist('traits', $name);
        }

        /** Remove trait from whitelist
         *
         * You can pass a string of trait name, or pass an array of the trait names to remove from whitelist
         *
         * @param   string|array        $name       String of trait name or array of trait names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistTrait($name){
            if(func_num_args() > 1){
                return $this->dewhitelistTrait(func_get_args());
            }
            $name = $this->normalizeTrait($name);
            return $this->dewhitelist('traits', $name);
        }

        /** Remove trait from blacklist
         *
         * You can pass a string of trait name, or pass an array of the trait names to remove from blacklist
         *
         * @param   string|array        $name       String of trait name or array of trait names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistTrait($name){
            if(func_num_args() > 1){
                return $this->deblacklistTrait(func_get_args());
            }
            $name = $this->normalizeTrait($name);
            return $this->deblacklist('traits', $name);
        }

        /** Whitelist keyword
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to whitelist
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistKeyword($name){
            if(func_num_args() > 1){
                return $this->whitelistKeyword(func_get_args());
            }
            $name = $this->normalizeKeyword($name);
            return $this->whitelist('keywords', $name);
        }

        /** Blacklist keyword
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to blacklist
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistKeyword($name){
            if(func_num_args() > 1){
                return $this->blacklistKeyword(func_get_args());
            }
            $name = $this->normalizeKeyword($name);
            return $this->blacklist('keywords', $name);
        }

        /** Remove keyword from whitelist
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to remove from whitelist
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistKeyword($name){
            if(func_num_args() > 1){
                return $this->dewhitelistKeyword(func_get_args());
            }
            $name = $this->normalizeKeyword($name);
            return $this->dewhitelist('keywords', $name);
        }

        /** Remove keyword from blacklist
         *
         * You can pass a string of keyword name, or pass an array of the keyword names to remove from blacklist
         *
         * @param   string|array        $name       String of keyword name or array of keyword names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistKeyword($name){
            if(func_num_args() > 1){
                return $this->deblacklistKeyword(func_get_args());
            }
            $name = $this->normalizeKeyword($name);
            return $this->deblacklist('keywords', $name);
        }

        /** Whitelist operator
         *
         * You can pass a string of operator name, or pass an array of the operator names to whitelist
         *
         * @param   string|array        $name       String of operator name or array of operator names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistOperator($name){
            if(func_num_args() > 1){
                return $this->whitelistOperator(func_get_args());
            }
            $name = $this->normalizeOperator($name);
            return $this->whitelist('operators', $name);
        }

        /** Blacklist operator
         *
         * You can pass a string of operator name, or pass an array of the operator names to blacklist
         *
         * @param   string|array        $name       String of operator name or array of operator names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistOperator($name){
            if(func_num_args() > 1){
                return $this->blacklistOperator(func_get_args());
            }
            $name = $this->normalizeOperator($name);
            return $this->blacklist('operators', $name);
        }

        /** Remove operator from whitelist
         *
         * You can pass a string of operator name, or pass an array of the operator names to remove from whitelist
         *
         * @param   string|array        $name       String of operator name or array of operator names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistOperator($name){
            if(func_num_args() > 1){
                return $this->dewhitelistOperator(func_get_args());
            }
            $name = $this->normalizeOperator($name);
            return $this->dewhitelist('operators', $name);
        }

        /** Remove operator from blacklist
         *
         * You can pass a string of operator name, or pass an array of the operator names to remove from blacklist
         *
         * @param   string|array        $name       String of operator name or array of operator names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistOperator($name){
            if(func_num_args() > 1){
                return $this->deblacklistOperator(func_get_args());
            }
            $name = $this->normalizeOperator($name);
            return $this->deblacklist('operators', $name);
        }

        /** Whitelist primitive
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to whitelist
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistPrimitive($name){
            if(func_num_args() > 1){
                return $this->whitelistPrimitive(func_get_args());
            }
            $name = $this->normalizePrimitive($name);
            return $this->whitelist('primitives', $name);
        }

        /** Blacklist primitive
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to blacklist
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistPrimitive($name){
            if(func_num_args() > 1){
                return $this->blacklistPrimitive(func_get_args());
            }
            $name = $this->normalizePrimitive($name);
            return $this->blacklist('primitives', $name);
        }

        /** Remove primitive from whitelist
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to remove from whitelist
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistPrimitive($name){
            if(func_num_args() > 1){
                return $this->dewhitelistPrimitive(func_get_args());
            }
            $name = $this->normalizePrimitive($name);
            return $this->dewhitelist('primitives', $name);
        }

        /** Remove primitive from blacklist
         *
         * You can pass a string of primitive name, or pass an array of the primitive names to remove from blacklist
         *
         * @param   string|array        $name       String of primitive name or array of primitive names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistPrimitive($name){
            if(func_num_args() > 1){
                return $this->deblacklistPrimitive(func_get_args());
            }
            $name = $this->normalizePrimitive($name);
            return $this->deblacklist('primitives', $name);
        }

        /** Whitelist type
         *
         * You can pass a string of type name, or pass an array of the type names to whitelist
         *
         * @param   string|array        $name       String of type name or array of type names to whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function whitelistType($name){
            if(func_num_args() > 1){
                return $this->whitelistType(func_get_args());
            }
            $name = $this->normalizeType($name);
            return $this->whitelist('types', $name);
        }

        /** Blacklist type
         *
         * You can pass a string of type name, or pass an array of the type names to blacklist
         *
         * @param   string|array        $name       String of type name or array of type names to blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function blacklistType($name){
            if(func_num_args() > 1){
                return $this->blacklistType(func_get_args());
            }
            $name = $this->normalizeType($name);
            return $this->blacklist('types', $name);
        }

        /** Remove type from whitelist
         *
         * You can pass a string of type name, or pass an array of the type names to remove from whitelist
         *
         * @param   string|array        $name       String of type name or array of type names to remove from whitelist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function dewhitelistType($name){
            if(func_num_args() > 1){
                return $this->dewhitelistType(func_get_args());
            }
            $name = $this->normalizeType($name);
            return $this->dewhitelist('types', $name);
        }

        /** Remove type from blacklist
         *
         * You can pass a string of type name, or pass an array of the type names to remove from blacklist
         *
         * @param   string|array        $name       String of type name or array of type names to remove from blacklist
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function deblacklistType($name){
            if(func_num_args() > 1){
                return $this->deblacklistType(func_get_args());
            }
            $name = $this->normalizeType($name);
            return $this->deblacklist('types', $name);
        }

        /** Check function name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the function name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if function is valid, this is also used for testing closures
         */
        public function checkFunc($name){
            if(!$this->validate_functions){
                return true;
            }
            $original_name = $name;
            if($name instanceof \Closure){
                if(!$this->allow_closures){
                    $this->validationError("Sandboxed code attempted to call closure!", Error::CLOSURE_ERROR);
                }
                return true;
            } else if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name || !is_string($name)){
                $this->validationError("Sandboxed code attempted to call unnamed function!", Error::VALID_FUNC_ERROR, null, '');
            }
            $name = $this->normalizeFunc($name);
            if(is_callable($this->validation['function'])){
                return call_user_func_array($this->validation['function'], [$name, $this]);
            }
            if(!isset($this->definitions['functions'][$name]) || !is_callable($this->definitions['functions'][$name]['function'])){
                if(count($this->whitelist['functions'])){
                    if(!isset($this->whitelist['functions'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted function: $original_name", Error::WHITELIST_FUNC_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['functions'])){
                    if(isset($this->blacklist['functions'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted function: $original_name", Error::BLACKLIST_FUNC_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalid function: $original_name", Error::VALID_FUNC_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check variable name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the variable name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if variable is valid
         */
        public function checkVar($name){
            if(!$this->validate_variables){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed variable!", Error::VALID_VAR_ERROR, null, '');
            }
            if(is_callable($this->validation['variable'])){
                return call_user_func_array($this->validation['variable'], [$name, $this]);
            }
            if(!isset($this->definitions['variables'][$name])){
                if(count($this->whitelist['variables'])){
                    if(!isset($this->whitelist['variables'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted variable: $original_name", Error::WHITELIST_VAR_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['variables'])){
                    if(isset($this->blacklist['variables'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted variable: $original_name", Error::BLACKLIST_VAR_ERROR, null, $original_name);
                    }
                } else if(!$this->allow_variables){
                    $this->validationError("Sandboxed code attempted to call invalid variable: $original_name", Error::VALID_VAR_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check global name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the global name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if global is valid
         */
        public function checkGlobal($name){
            if(!$this->validate_globals){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed global!", Error::VALID_GLOBAL_ERROR, null, '');
            }
            if(is_callable($this->validation['global'])){
                return call_user_func_array($this->validation['global'], [$name, $this]);
            }
            if(count($this->whitelist['globals'])){
                if(!isset($this->whitelist['globals'][$name])){
                    $this->validationError("Sandboxed code attempted to call non-whitelisted global: $original_name", Error::WHITELIST_GLOBAL_ERROR, null, $original_name);
                }
            } else if(count($this->blacklist['globals'])){
                if(isset($this->blacklist['globals'][$name])){
                    $this->validationError("Sandboxed code attempted to call blacklisted global: $original_name", Error::BLACKLIST_GLOBAL_ERROR, null, $original_name);
                }
            } else {
                $this->validationError("Sandboxed code attempted to call invalid global: $original_name", Error::VALID_GLOBAL_ERROR, null, $original_name);
            }
            return true;
        }

        /** Check superglobal name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the superglobal name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if superglobal is valid
         */
        public function checkSuperglobal($name){
            if(!$this->validate_superglobals){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed superglobal!", Error::VALID_SUPERGLOBAL_ERROR, null, '');
            }
            $name = $this->normalizeSuperglobal($name);
            if(is_callable($this->validation['superglobal'])){
                return call_user_func_array($this->validation['superglobal'], [$name, $this]);
            }
            if(!isset($this->definitions['superglobals'][$name])){
                if(count($this->whitelist['superglobals'])){
                    if(!isset($this->whitelist['superglobals'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted superglobal: $original_name", Error::WHITELIST_SUPERGLOBAL_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['superglobals'])){
                    if(isset($this->blacklist['superglobals'][$name]) && !count($this->blacklist['superglobals'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted superglobal: $original_name", Error::BLACKLIST_SUPERGLOBAL_ERROR, null, $original_name);
                    }
                } else if(!$this->overwrite_superglobals){
                    $this->validationError("Sandboxed code attempted to call invalid superglobal: $original_name", Error::VALID_SUPERGLOBAL_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check constant name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the constant name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if constant is valid
         */
        public function checkConst($name){
            if(!$this->validate_constants){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed constant!", Error::VALID_CONST_ERROR, null, '');
            }
            if(strtolower($name) == 'true' || strtolower($name) == 'false'){
                return $this->checkPrimitive('bool');
            }
            if(strtolower($name) == 'null'){
                return $this->checkPrimitive('null');
            }
            if(is_callable($this->validation['constant'])){
                return call_user_func_array($this->validation['constant'], [$name, $this]);
            }
            if(!isset($this->definitions['constants'][$name])){
                if(count($this->whitelist['constants'])){
                    if(!isset($this->whitelist['constants'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted constant: $original_name", Error::WHITELIST_CONST_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['constants'])){
                    if(isset($this->blacklist['constants'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted constant: $original_name", Error::BLACKLIST_CONST_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalid constant: $original_name", Error::VALID_CONST_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check magic constant name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the magic constant name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if magic constant is valid
         */
        public function checkMagicConst($name){
            if(!$this->validate_magic_constants){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed magic constant!", Error::VALID_MAGIC_CONST_ERROR, null, '');
            }
            $name = $this->normalizeMagicConst($name);
            if(is_callable($this->validation['magic_constant'])){
                return call_user_func_array($this->validation['magic_constant'], [$name, $this]);
            }
            if(!isset($this->definitions['magic_constants'][$name])){
                if(count($this->whitelist['magic_constants'])){
                    if(!isset($this->whitelist['magic_constants'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted magic constant: $original_name", Error::WHITELIST_MAGIC_CONST_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['magic_constants'])){
                    if(isset($this->blacklist['magic_constants'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted magic constant: $original_name", Error::BLACKLIST_MAGIC_CONST_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalid magic constant: $original_name", Error::VALID_MAGIC_CONST_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check namespace name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the namespace name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if namespace is valid
         */
        public function checkNamespace($name){
            if(!$this->validate_namespaces){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed namespace!", Error::VALID_NAMESPACE_ERROR, null, '');
            }
            $name = $this->normalizeNamespace($name);
            if(is_callable($this->validation['namespace'])){
                return call_user_func_array($this->validation['namespace'], [$name, $this]);
            }
            if(!isset($this->definitions['namespaces'][$name])){
                if(count($this->whitelist['namespaces'])){
                    if(!isset($this->whitelist['namespaces'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted namespace: $original_name", Error::WHITELIST_NAMESPACE_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['namespaces'])){
                    if(isset($this->blacklist['namespaces'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted namespace: $original_name", Error::BLACKLIST_NAMESPACE_ERROR, null, $original_name);
                    }
                } else if(!$this->allow_namespaces){
                    $this->validationError("Sandboxed code attempted to call invalid namespace: $original_name", Error::VALID_NAMESPACE_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check alias name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the alias name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if alias is valid
         */
        public function checkAlias($name){
            if(!$this->validate_aliases){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed alias!", Error::VALID_ALIAS_ERROR, null, '');
            }
            $name = $this->normalizeAlias($name);
            if(is_callable($this->validation['alias'])){
                return call_user_func_array($this->validation['alias'], [$name, $this]);
            }
            if(count($this->whitelist['aliases'])){
                if(!isset($this->whitelist['aliases'][$name])){
                    $this->validationError("Sandboxed code attempted to call non-whitelisted alias: $original_name", Error::WHITELIST_ALIAS_ERROR, null, $original_name);
                }
            } else if(count($this->blacklist['aliases'])){
                if(isset($this->blacklist['aliases'][$name])){
                    $this->validationError("Sandboxed code attempted to call blacklisted alias: $original_name", Error::BLACKLIST_ALIAS_ERROR, null, $original_name);
                }
            } else if(!$this->allow_aliases){
                $this->validationError("Sandboxed code attempted to call invalid alias: $original_name", Error::VALID_ALIAS_ERROR, null, $original_name);
            }
            return true;
        }

        /** Check use (or alias) name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         *
         * @alias checkAlias();
         *
         * @param   string   $name      String of the use (or alias) name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if use (or alias) is valid
         */
        public function checkUse($name){
            return $this->checkAlias($name);
        }

        /** Check class name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the class name to check
         * @param   bool     $extends   Flag whether this is an extended class
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if class is valid
         */
        public function checkClass($name, $extends = false){
            if(!$this->validate_classes){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            $action = $extends ? 'extend' : 'call';
            if(!$name){
                $this->validationError("Sandboxed code attempted to $action unnamed class!", Error::VALID_CLASS_ERROR, null, '');
            }
            $name = $this->normalizeClass($name);
            if($name == 'self' || $name == 'static' || $name == 'parent'){
                return true;
            }
            if(is_callable($this->validation['class'])){
                return call_user_func_array($this->validation['class'], [$name, $this]);
            }
            if(!isset($this->definitions['classes'][$name])){
                if(count($this->whitelist['classes'])){
                    if(!isset($this->whitelist['classes'][$name])){
                        $this->validationError("Sandboxed code attempted to $action non-whitelisted class: $original_name", Error::WHITELIST_CLASS_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['classes'])){
                    if(isset($this->blacklist['classes'][$name])){
                        $this->validationError("Sandboxed code attempted to $action blacklisted class: $original_name", Error::BLACKLIST_CLASS_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to $action invalid class: $original_name", Error::VALID_CLASS_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check interface name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the interface name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if interface is valid
         */
        public function checkInterface($name){
            if(!$this->validate_interfaces){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed interface!", Error::VALID_INTERFACE_ERROR, null, '');
            }
            $name = $this->normalizeInterface($name);
            if(is_callable($this->validation['interface'])){
                return call_user_func_array($this->validation['interface'], [$name, $this]);
            }
            if(!isset($this->definitions['interfaces'][$name])){
                if(count($this->whitelist['interfaces'])){
                    if(!isset($this->whitelist['interfaces'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted interface: $original_name", Error::WHITELIST_INTERFACE_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['interfaces'])){
                    if(isset($this->blacklist['interfaces'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted interface: $original_name", Error::BLACKLIST_INTERFACE_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalidnterface: $original_name", Error::VALID_INTERFACE_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check trait name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the trait name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if trait is valid
         */
        public function checkTrait($name){
            if(!$this->validate_traits){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed trait!", Error::VALID_TRAIT_ERROR, null, '');
            }
            $name = $this->normalizeTrait($name);
            if(is_callable($this->validation['trait'])){
                return call_user_func_array($this->validation['trait'], [$name, $this]);
            }
            if(!isset($this->definitions['traits'][$name])){
                if(count($this->whitelist['traits'])){
                    if(!isset($this->whitelist['traits'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted trait: $original_name", Error::WHITELIST_TRAIT_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['traits'])){
                    if(isset($this->blacklist['traits'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted trait: $original_name", Error::BLACKLIST_TRAIT_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalid trait: $original_name", Error::VALID_TRAIT_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check keyword name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the keyword name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if keyword is valid
         */
        public function checkKeyword($name){
            if(!$this->validate_keywords){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed keyword!", Error::VALID_KEYWORD_ERROR, null, '');
            }
            $name = $this->normalizeKeyword($name);
            if(is_callable($this->validation['keyword'])){
                return call_user_func_array($this->validation['keyword'], [$name, $this]);
            }
            if(count($this->whitelist['keywords'])){
                if(!isset($this->whitelist['keywords'][$name])){
                    $this->validationError("Sandboxed code attempted to call non-whitelisted keyword: $original_name", Error::WHITELIST_KEYWORD_ERROR, null, $original_name);
                }
            } else if(count($this->blacklist['keywords'])){
                if(isset($this->blacklist['keywords'][$name])){
                    $this->validationError("Sandboxed code attempted to call blacklisted keyword: $original_name", Error::BLACKLIST_KEYWORD_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check operator name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the type operator to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if operator is valid
         */
        public function checkOperator($name){
            if(!$this->validate_operators){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed operator!", Error::VALID_OPERATOR_ERROR, null, '');
            }
            $name = $this->normalizeOperator($name);
            if(is_callable($this->validation['operator'])){
                return call_user_func_array($this->validation['operator'], [$name, $this]);
            }
            if(count($this->whitelist['operators'])){
                if(!isset($this->whitelist['operators'][$name])){
                    $this->validationError("Sandboxed code attempted to call non-whitelisted operator: $original_name", Error::WHITELIST_OPERATOR_ERROR, null, $original_name);
                }
            } else if(count($this->blacklist['operators'])){
                if(isset($this->blacklist['operators'][$name])){
                    $this->validationError("Sandboxed code attempted to call blacklisted operator: $original_name", Error::BLACKLIST_OPERATOR_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check primitive name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the primitive name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if primitive is valid
         */
        public function checkPrimitive($name){
            if(!$this->validate_primitives){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed primitive!", Error::VALID_PRIMITIVE_ERROR, null, '');
            }
            $name = $this->normalizePrimitive($name);
            if(is_callable($this->validation['primitive'])){
                return call_user_func_array($this->validation['primitive'], [$name, $this]);
            }
            if(count($this->whitelist['primitives'])){
                if(!isset($this->whitelist['primitives'][$name])){
                    $this->validationError("Sandboxed code attempted to call non-whitelisted primitive: $original_name", Error::WHITELIST_PRIMITIVE_ERROR, null, $original_name);
                }
            } else if(count($this->blacklist['primitives'])){
                if(isset($this->blacklist['primitives'][$name])){
                    $this->validationError("Sandboxed code attempted to call blacklisted primitive: $original_name", Error::BLACKLIST_PRIMITIVE_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Check type name against PHPSandbox validation rules. This is an internal PHPSandbox function but requires public access to work.
         * @param   string   $name      String of the type name to check
         * @throws  Error    Throws exception if validation error occurs
         *
         * @return  bool     Returns true if type is valid
         */
        public function checkType($name){
            if(!$this->validate_types){
                return true;
            }
            $original_name = $name;
            if($name instanceof SandboxedString){
                $name = strval($name);
            }
            if(!$name){
                $this->validationError("Sandboxed code attempted to call unnamed type!", Error::VALID_TYPE_ERROR, null, '');
            }
            $name = $this->normalizeType($name);
            if(is_callable($this->validation['type'])){
                return call_user_func_array($this->validation['type'], [$name, $this]);
            }
            if(!isset($this->definitions['classes'][$name])){
                if(count($this->whitelist['types'])){
                    if(!isset($this->whitelist['types'][$name])){
                        $this->validationError("Sandboxed code attempted to call non-whitelisted type: $original_name", Error::WHITELIST_TYPE_ERROR, null, $original_name);
                    }
                } else if(count($this->blacklist['types'])){
                    if(isset($this->blacklist['types'][$name])){
                        $this->validationError("Sandboxed code attempted to call blacklisted type: $original_name", Error::BLACKLIST_TYPE_ERROR, null, $original_name);
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to call invalid type: $original_name", Error::VALID_TYPE_ERROR, null, $original_name);
                }
            }
            return true;
        }

        /** Prepare defined variables for execution
         *
         * @throws  Error       Throws exception if variable preparation error occurs
         * @return  string      Prepared string of variable output
         */
        protected function prepareVars(){
            $output = [];
            foreach($this->definitions['variables'] as $name => $value){
                if(is_int($name)){  //can't define numeric variable names
                    $this->validationError("Cannot define variable name that begins with an integer!", Error::DEFINE_VAR_ERROR, null, $name);
                }
                if(is_scalar($value) || is_null($value)){
                    if(is_bool($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? 'true' : 'false');
                    } else if(is_int($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? $value : '0');
                    } else if(is_float($value)){
                        $output[] = '$' . $name . ' = ' . ($value ? $value : '0.0');
                    } else if(is_string($value)){
                        $output[] = '$' . $name . " = '" . addcslashes($value, "'\\") . "'";
                    } else {
                        $output[] = '$' . $name . " = null";
                    }
                } else {
                    $output[] = '$' . $name . " = unserialize('" . addcslashes(serialize($value), "'\\") . "')";
                }
            }
            return count($output) ? "\r\n" . implode(";\r\n", $output) . ";\r\n" : '';
        }

        /** Prepare defined constants for execution
         */
        protected function prepareConsts(){
            $output = [];
            foreach($this->definitions['constants'] as $name => $value){
                if(is_scalar($value) || is_null($value)){
                    if(is_bool($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? 'true' : 'false') . ');';
                    } else if(is_int($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? $value : '0') . ');';
                    } else if(is_float($value)){
                        $output[] = '\define(' . "'" . $name . "', " . ($value ? $value : '0.0') . ');';
                    } else if(is_string($value)){
                        $output[] = '\define(' . "'" . $name . "', '" . addcslashes($value, "'\\") . "');";
                    } else {
                        $output[] = '\define(' . "'" . $name . "', null);";
                    }
                } else {
                    $this->validationError("Sandboxed code attempted to define non-scalar constant value: $name", Error::DEFINE_CONST_ERROR, null, $name);
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        /** Prepare defined namespaces for execution
         */
        protected function prepareNamespaces(){
            $output = [];
            foreach($this->definitions['namespaces'] as $name){
                if(is_string($name) && $name){
                    $output[] = 'namespace ' . $name . ';';
                } else {
                    $this->validationError("Sandboxed code attempted to create invalid namespace: $name", Error::DEFINE_NAMESPACE_ERROR, null, $name);
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        /** Prepare defined aliases for execution
         */
        protected function prepareAliases(){
            $output = [];
            foreach($this->definitions['aliases'] as $alias){
                if(is_array($alias) && isset($alias['original']) && is_string($alias['original']) && $alias['original']){
                    $output[] = 'use ' . $alias['original'] . ((isset($alias['alias']) && is_string($alias['alias']) && $alias['alias']) ? ' as ' . $alias['alias'] : '') . ';';
                } else {
                    $this->validationError("Sandboxed code attempted to use invalid namespace alias: " . $alias['original'], Error::DEFINE_ALIAS_ERROR, null, $alias['original']);
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        /** Prepare defined uses (or aliases) for execution
         * @alias   prepareAliases();
         */
        protected function prepareUses(){
            return $this->prepareAliases();
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
            if(is_string($closure) && !is_callable($closure)){
                return substr($closure, 0, 2) == '<?' ? $closure : '<?php ' . $closure;
            }
            $disassembled_closure = FunctionParser::fromCallable($closure);
            if($this->auto_define_vars){
                $this->autoDefine($disassembled_closure);
            }
            return '<?php' . $disassembled_closure->getBody();
        }

        /** Automatically whitelisted trusted code
         *
         * @param   string    $code         String of trusted $code to automatically whitelist
         * @param   bool      $appended     Flag if this code ir prended or appended (true = appended)
         *
         * @return  mixed     Return result of error handler if $code could not be parsed
         *
         * @throws  Error     Throw exception if code cannot be parsed for whitelisting
         */
        protected function autoWhitelist($code, $appended = false){
            $factory = new ParserFactory;
            $parser = $factory->create(ParserFactory::PREFER_PHP5);
            try {
                $statements = $parser->parse($code);
            } catch (ParserError $error) {
                return $this->validationError('Error parsing ' . ($appended ? 'appended' : 'prepended') . ' sandboxed code for auto-whitelisting!', Error::PARSER_ERROR, null, $code, $error);
            }
            $traverser = new NodeTraverser;
            $whitelister = new WhitelistVisitor($this);
            $traverser->addVisitor($whitelister);
            $traverser->traverse($statements);
            return true;
        }

        /** Automatically define variables passed to disassembled closure
         * @param FunctionParser    $disassembled_closure
         */
        protected function autoDefine(FunctionParser $disassembled_closure){
            $parameters = $disassembled_closure->getReflection()->getParameters();
            foreach($parameters as $param){
                /**
                 * @var \ReflectionParameter $param
                 */
                $this->defineVar($param->getName(), $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }

        /** Prepend trusted code
         * @param   string|callable     $code         String or callable of trusted $code to prepend to generated code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function prepend($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code);
            if($this->auto_whitelist_trusted_code){
                $this->autoWhitelist($code);
            }
            $this->prepended_code .= substr($code, 6) . "\r\n"; //remove opening php tag
            return $this;
        }

        /** Append trusted code
         * @param   string|callable     $code         String or callable of trusted $code to append to generated code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function append($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code);
            if($this->auto_whitelist_trusted_code){
                $this->autoWhitelist($code, true);
            }
            $this->appended_code .= "\r\n" . substr($code, 6) . "\r\n"; //remove opening php tag
            return $this;
        }

        /** Clear all trusted and sandboxed code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clear(){
            $this->prepended_code = '';
            $this->generated_code = null;
            $this->appended_code = '';
            return $this;
        }

        /** Clear all trusted code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearTrustedCode(){
            $this->prepended_code = '';
            $this->appended_code = '';
            return $this;
        }

        /** Clear all prepended trusted code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearPrepended(){
            $this->prepended_code = '';
            return $this;
        }

        /** Clear all prepended trusted code
         *
         * @alias   $this->clearPrepended()
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearPrependedCode(){
            $this->prepended_code = '';
            return $this;
        }

        /** Clear all appended trusted code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearAppend(){
            $this->appended_code = '';
            return $this;
        }

        /** Clear all appended trusted code
         *
         * @alias   $this->clearAppend()
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearAppendedCode(){
            $this->appended_code = '';
            return $this;
        }

        /** Clear generated code
         *
         * @return  $this               Returns the PHPSandbox instance for fluent querying
         */
        public function clearCode(){
            $this->generated_code = null;
            return $this;
        }

        /** Return the amount of time the sandbox spent preparing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to prepare the sandboxed code
         */
        public function getPreparedTime($round = 0){
            return $round ? round($this->prepare_time, $round) : $this->prepare_time;
        }

        /** Return the amount of time the sandbox spent executing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to execute the sandboxed code
         */
        public function getExecutionTime($round = 0){
            return $round ? round($this->execution_time, $round) : $this->execution_time;
        }

        /** Return the current file being executed in the sandbox
         *
         * @return  string           The current file being executed
         */
        public function getExecutingFile(){
            return $this->executing_file;
        }

        /** Return the amount of time the sandbox spent preparing and executing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  float           The amount of time in microseconds it took to prepare and execute the sandboxed code
         */
        public function getTime($round = 0){
            return $round ? round($this->prepare_time + $this->execution_time, $round) : ($this->prepare_time + $this->execution_time);
        }

        /** Return the amount of bytes the sandbox allocated while preparing and executing the sandboxed code
         *
         * You can pass the number of digits you wish to round the return value
         *
         * @param   int|null        $round      The number of digits to round the return value
         *
         * @return  int             The amount of bytes in memory it took to prepare and execute the sandboxed code
         */
        public function getMemoryUsage($round = 0){
            return $round ? round($this->memory_usage, $round) : $this->memory_usage;
        }

        /** Validate passed callable for execution
         *
         * @param   callable|string $code      The callable or string of code to validate
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function validate($code){
            $this->preparsed_code = $this->disassemble($code);
            $factory = new ParserFactory;
            $parser = $factory->create(ParserFactory::PREFER_PHP5);

            try {
                $this->parsed_ast = $parser->parse($this->preparsed_code);
            } catch (ParserError $error) {
                $this->validationError("Could not parse sandboxed code!", Error::PARSER_ERROR, null, $this->preparsed_code, $error);
            }

            $prettyPrinter = new Standard();

            if(($this->allow_functions && $this->auto_whitelist_functions) ||
                ($this->allow_constants && $this->auto_whitelist_constants) ||
                ($this->allow_classes && $this->auto_whitelist_classes) ||
                ($this->allow_interfaces && $this->auto_whitelist_interfaces) ||
                ($this->allow_traits && $this->auto_whitelist_traits) ||
                ($this->allow_globals && $this->auto_whitelist_globals)){

                $traverser = new NodeTraverser;
                $whitelister = new SandboxWhitelistVisitor($this);
                $traverser->addVisitor($whitelister);
                $traverser->traverse($this->parsed_ast);
            }

            $traverser = new NodeTraverser;

            $validator = new ValidatorVisitor($this);

            $traverser->addVisitor($validator);

            $this->prepared_ast = $traverser->traverse($this->parsed_ast);

            $this->prepared_code = $prettyPrinter->prettyPrint($this->prepared_ast);

            return $this;
        }

        /** Prepare passed callable for execution
         *
         * This function validates your code and automatically whitelists it according to your specified configuration
         *
         * @param   callable    $code               The callable to prepare for execution
         * @param   boolean     $skip_validation    Boolean flag to indicate whether the sandbox should skip validation. Default is false.
         *
         * @throws  Error       Throws exception if error occurs in parsing, validation or whitelisting
         *
         * @return  string      The generated code (this can also be accessed via $sandbox->generated_code)
         */
        public function prepare($code, $skip_validation = false){
            $this->prepare_time = microtime(true);

            if($this->allow_constants && !$this->isDefinedFunc('define') && ($this->hasWhitelistedFuncs() || !$this->hasBlacklistedFuncs())){
                $this->whitelistFunc('define');    //makes no sense to allow constants if you can't define them!
            }

            if(!$skip_validation){
                $this->validate($code);
            }

            static::$sandboxes[$this->name] = $this;

            $this->generated_code = $this->prepareNamespaces() .
                $this->prepareAliases() .
                $this->prepareConsts() .
                "\r\n" . '$closure = function(){' . "\r\n" .
                $this->prepareVars() .
                $this->prepended_code .
                ($skip_validation ? $code : $this->prepared_code) .
                $this->appended_code .
                "\r\n" . '};' .
                "\r\n" . 'if(method_exists($closure, "bindTo")){ $closure = $closure->bindTo(null); }' .
                "\r\n" . 'return $closure();';

            usleep(1); //guarantee at least some time passes
            $this->prepare_time = (microtime(true) - $this->prepare_time);
            return $this->generated_code;
        }

        /** Prepare and execute callable and return output
         *
         * This function validates your code and automatically whitelists it according to your specified configuration, then executes it.
         *
         * @param   callable|string     $callable           Callable or string of PHP code to prepare and execute within the sandbox
         * @param   boolean             $skip_validation    Boolean flag to indicate whether the sandbox should skip validation of the pass callable. Default is false.
         * @param   string              $executing_file     The file path of the code to execute
         *
         * @throws  Error       Throws exception if error occurs in parsing, validation or whitelisting or if generated closure is invalid
         *
         * @return  mixed       The output from the executed sandboxed code
         */
        public function execute($callable = null, $skip_validation = false, $executing_file = false){
            if ($executing_file)
              $this->executing_file = realpath($executing_file);
            $this->execution_time = microtime(true);
            $this->memory_usage = memory_get_peak_usage();
            if($callable !== null){
                $this->prepare($callable, $skip_validation);
            }
            $saved_error_level = null;
            if($this->error_level !== null){
                $saved_error_level = error_reporting();
                error_reporting(intval($this->error_level));
            }
            if(is_callable($this->error_handler) || $this->convert_errors){
                set_error_handler([$this, 'error'], $this->error_handler_types);
            }
            if($this->time_limit){
                set_time_limit($this->time_limit);
            }
            $exception = null;
            $result = null;
            try {
                if($this->capture_output){
                    ob_start();
                    eval($this->generated_code);
                    $result = ob_get_clean();
                } else {
                    $result = eval($this->generated_code);
                }
            } catch(\Exception $exception){
                //swallow any exceptions
            }
            if(is_callable($this->error_handler) || $this->convert_errors){
                restore_error_handler();
            }
            usleep(1); //guarantee at least some time passes
            $this->memory_usage = (memory_get_peak_usage() - $this->memory_usage);
            $this->execution_time = (microtime(true) - $this->execution_time);
            if($this->error_level !== null && $this->restore_error_level){
                error_reporting($saved_error_level);
            }
            return $exception instanceof \Exception ? $this->exception($exception) : $result;
        }

        /** Set callable to handle errors
         *
         * This function sets the sandbox error handler and the handled error types. The handler accepts the error number,
         * the error message, the error file, the error line, the error context and the sandbox instance as arguments.
         * If the error handler does not handle errors correctly then the sandbox's security may become compromised!
         *
         * }, E_ALL);  //ignore all errors, INSECURE
         *
         * @param   callable        $handler       Callable to handle thrown Errors
         * @param   int             $error_types   Integer flag of the error types to handle (default is E_ALL)
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function setErrorHandler($handler, $error_types = E_ALL){
            $this->error_handler = $handler;
            $this->error_handler_types = $error_types;
            return $this;
        }

        /** Get error handler
         *
         * This function returns the sandbox error handler.
         *
         * @return null|callable
         */
        public function getErrorHandler(){
            return $this->error_handler;
        }

        /** Unset error handler
         *
         * This function unsets the sandbox error handler.
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function unsetErrorHandler(){
            $this->error_handler = null;
            return $this;
        }

        /** Gets the last sandbox error
         * @return array
         */
        public function getLastError(){
            return $this->last_error;
        }

        /** Invoke sandbox error handler
         *
         * @param   int                         $errno          Error number
         * @param   string                      $errstr         Error message
         * @param   string                      $errfile        Error file
         * @param   int                         $errline        Error line number
         * @param   array                       $errcontext     Error context array
         * @return  mixed
         */
        public function error($errno, $errstr, $errfile, $errline, $errcontext){
            $this->last_error = error_get_last();
            if($this->convert_errors){
                return $this->exception(new \ErrorException($errstr, 0, $errno, $errfile, $errline));
            }
            return is_callable($this->error_handler) ? call_user_func_array($this->error_handler, [$errno, $errstr, $errfile, $errline, $errcontext, $this]) : null;
        }

        /** Set callable to handle thrown exceptions
         *
         * This function sets the sandbox exception handler. The handler accepts the thrown exception and the sandbox instance
         * as arguments. If the exception handler does not handle exceptions correctly then the sandbox's security may
         * become compromised!
         *
         * @param   callable        $handler       Callable to handle thrown exceptions
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function setExceptionHandler($handler){
            $this->exception_handler = $handler;
            return $this;
        }

        /** Get exception handler
         *
         * This function returns the sandbox exception handler.
         *
         * @return null|callable
         */
        public function getExceptionHandler(){
            return $this->exception_handler;
        }

        /** Unset exception handler
         *
         * This function unsets the sandbox exception handler.
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function unsetExceptionHandler(){
            $this->exception_handler = null;
            return $this;
        }

        /** Gets the last exception thrown by the sandbox
         * @return \Exception|Error
         */
        public function getLastException(){
            return $this->last_exception;
        }

        /** Invoke sandbox exception handler
         *
         * @param   \Exception                  $exception      Error number
         * @throws  \Exception
         *
         * @return  mixed
         */
        public function exception(\Exception $exception){
            $this->last_exception = $exception;
            if(is_callable($this->exception_handler)){
                return call_user_func_array($this->exception_handler, [$exception, $this]);
            }
            throw $exception;
        }

        /** Set callable to handle thrown validation Errors
         *
         * This function sets the sandbox validation Error handler. The handler accepts the thrown Error and the sandbox
         * instance as arguments. If the error handler does not handle validation errors correctly then the sandbox's
         * security may become compromised!
         *
         * @param   callable        $handler       Callable to handle thrown validation Errors
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function setValidationErrorHandler($handler){
            $this->validation_error_handler = $handler;
            return $this;
        }

        /** Get validation error handler
         *
         * This function returns the sandbox validation error handler.
         *
         * @return null|callable
         */
        public function getValidationErrorHandler(){
            return $this->validation_error_handler;
        }

        /** Unset validation error handler
         *
         * This function unsets the sandbox validation error handler.
         *
         * @return  $this      Returns the PHPSandbox instance for fluent querying
         */
        public function unsetValidationErrorHandler(){
            $this->validation_error_handler = null;
            return $this;
        }

        /** Gets the last validation error thrown by the sandbox
         * @return \Exception|Error
         */
        public function getLastValidationError(){
            return $this->last_validation_error;
        }

        /** Invoke sandbox error validation handler if it exists, throw Error otherwise
         *
         * @param   \Exception|Error|string     $error      Error to throw if Error is not handled, or error message string
         * @param   int                         $code       The error code
         * @param   Node|null                   $node       The error parser node
         * @param   mixed                       $data       The error data
         * @param   \Exception|Error|null       $previous   The previous Error thrown
         *
         * @throws  \Exception|Error
         * @return  mixed
         */
        public function validationError($error, $code = 0, Node $node = null, $data = null, \Exception $previous = null){
            $error = ($error instanceof \Exception)
                ? (($error instanceof Error)
                    ? new Error($error->getMessage(), $error->getCode(), $error->getNode(), $error->getData(), $error->getPrevious() ?: $this->last_validation_error)
                    : new Error($error->getMessage(), $error->getCode(), null, null, $error->getPrevious() ?: $this->last_validation_error))
                : new Error($error, $code, $node, $data, $previous ?: $this->last_validation_error);
            $this->last_validation_error = $error;
            if($this->validation_error_handler && is_callable($this->validation_error_handler)){
                $result = call_user_func_array($this->validation_error_handler, [$error, $this]);
                if($result instanceof \Exception){
                    throw $result;
                }
                return $result;
            } else {
                throw $error;
            }
        }

        /** Get a named PHPSandbox instance (used to retrieve the sandbox instance from within sandboxed code)
         * @param   string                      $name       The name of the PHPSandbox instance to retrieve
         * @return  null|PHPSandbox
         */
        public static function getSandbox($name){
            return isset(static::$sandboxes[$name]) ? static::$sandboxes[$name] : null;
        }

        /** Get an iterator of all the public PHPSandbox properties
         * @return array
         */
        public function getIterator(){
            return new \ArrayIterator(get_object_vars($this));
        }

        /** Magic method to provide API compatibility for v1.* code
         * @param   string                     $method       The method name to call
         * @param   array                      $arguments    The method arguments to call
         * @return  mixed
         */
        public function __call($method, $arguments){
            $renamed_method = lcfirst(str_replace('_', '', ucwords($method, '_')));
            if(method_exists($this, $renamed_method)){
                return call_user_func_array([$this, $renamed_method], $arguments);
            }
            trigger_error('Fatal error: Call to undefined method PHPSandbox::' . $method, E_ERROR);
            return null;
        }
    }
