<?php
    namespace PHPSandbox;

	class PHPSandbox {
        protected static $function_prefix = '__PHPSandbox_';
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
        public static $defined_funcs = array(
            'get_defined_functions',
            'get_defined_vars',
            'get_defined_constants',
            'get_declared_classes',
            'get_declared_interfaces',
            'get_declared_traits'
        );
        public $name = '';
        /* DEFINED */
        protected $definitions = array(
            'functions' => array(),
            'variables' => array(),
            'globals' => array(),
            'superglobals' => array(),
            'constants' => array(),
            'magic_constants' => array(),
            'namespaces' => array(),
            'aliases' => array()
        );
        /* WHITELISTED (IF WHITELISTED ARRAY IS SET IT OVERRIDES ITS BLACKLIST COUNTERPART!)  */
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
        /* BLACKLISTED  */
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
                'include' => true,
                'eval' => true,
                'exit' => true
            ),
            'operators' => array(),
            'primitives' => array(),
            'types' => array()
        );
        /* BOOLEAN FLAGS */
        public $error_level                 = null;     //null = use parent scope error level
        public $auto_whitelist_trusted_code = true;     //automagically whitelist prepended and appended code?
        public $auto_whitelist_functions    = true;     //automagically whitelist functions created in sandboxed code if $allow_functions is true?
        public $auto_whitelist_constants    = true;     //automagically whitelist constants created in sandboxed code if $allow_constants is true?
        public $auto_whitelist_globals      = true;     //automagically whitelist global variables created in sandboxed code if $allow_globals is true? (Used to whitelist them in the variables list)
        public $auto_whitelist_classes      = true;     //automagically whitelist classes created in sandboxed code if $allow_classes is true?
        public $auto_whitelist_interfaces   = true;     //automagically whitelist interfaces created in sandboxed code if $allow_interfaces is true?
        public $auto_whitelist_traits       = true;     //automagically whitelist traits created in sandboxed code if $allow_traits is true?
        public $auto_define_vars            = true;     //automagically define variables passed to prepended, appended and prepared code closures?
        public $overwrite_defined_funcs     = true;     //overwrite get_defined_functions, get_defined_vars, get_defined_constants, get_declared_classes, get_declared_interfaces and get_declared_traits?
        public $overwrite_superglobals      = true;     //overwrite $_GET, $_POST, $_COOKIE, $_FILES, $_ENV, $_REQUEST, $_SERVER, $_SESSION and $GLOBALS superglobals?
        public $allow_functions             = true;         //allow sandboxed code to declare functions?
        public $allow_closures              = true;         //allow sandboxed code to create closures?
        public $allow_variables             = true;         //allow sandboxed code to create variables?
        public $allow_static_variables      = true;         //allow sandboxed code to create static variables?
        public $allow_objects               = true;         //allow sandboxed code to create objects (e.g. new keyword)?
        public $allow_constants             = true;         //allow sandboxed code to define constants?
        public $allow_globals               = true;         //allow sandboxed code to use global keyword?
        public $allow_namespaces            = true;         //allow sandboxed code to declare namespaces? (these utilize the define_namespace function)
        public $allow_aliases               = true;         //allow sandboxed code to declare aliases? (these utilize the define_alias function)
        public $allow_classes               = true;         //allow sandboxed code to declare classes?
        public $allow_interfaces            = false;        //allow sandboxed code to declare interfaces?
        public $allow_traits                = false;        //allow sandboxed code to declare traits?
        public $allow_escaping              = false;        //allow sandboxed code to escape to HTML?
        public $allow_casting               = false;        //allow sandboxed code to cast types?
        public $allow_error_suppressing     = false;        //allow sandboxed code to suppress errors?
        public $allow_references            = true;         //allow sandboxed code to assign references?
        public $allow_backticks             = false;        //allow sandboxed code to use shell execution backticks? (will be disabled if shell_exec is not whitelisted or is blacklisted, will be converted to defined shell_exec if a shell_exec function is defined)
        public $allow_halting               = false;        //allow sandboxed code to halt compiler?
        /* TRUSTED CODE STRINGS */
        public $prepended_code = '';
        public $appended_code = '';
        /* OUTPUT */
        public $preparsed_code = '';                    //string of preparsed code
        public $parsed_ast = array();                   //array of parse code
        public $prepared_code = '';                     //string of prepared code
        public $prepared_ast = array();                 //array of prepared code
        public $generated_code = '';                    //string of generated code
        /**
         * @var \Closure|null
         */
        public $generated_function = null;              //generated closure

		public function __construct(array $functions = array(),
                                    array $variables = array(),
                                    array $constants = array(),
                                    array $namespaces = array(),
                                    array $aliases = array(),
                                    array $globals = array(),
                                    array $superglobals = array(),
                                    array $magic_constants = array()){
            $this->name = static::$function_prefix . md5(uniqid());
            $this->define_funcs($functions)
                ->define_vars($variables)
                ->define_consts($constants)
                ->define_namespaces($namespaces)
                ->define_aliases($aliases)
                ->define_globals($globals)
                ->define_superglobals($superglobals)
                ->define_magic_consts($magic_constants);
		}

        public static function create(array $functions = array(),
                                      array $variables = array(),
                                      array $constants = array(),
                                      array $namespaces = array(),
                                      array $aliases = array(),
                                      array $globals = array(),
                                      array $superglobals = array(),
                                      array $magic_constants = array()){
            return new static($functions, $variables, $constants, $namespaces, $aliases, $globals, $superglobals, $magic_constants);
        }

        public function __invoke(){
            return call_user_func_array(array($this, 'execute'), func_get_args());
        }

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

        public function _get_defined_vars(array $variables = array()){
            if(isset($variables[$this->name])){
                unset($variables[$this->name]); //hide PHPSandbox variable
            }
            return $variables;
        }

        public function _get_superglobal($name){
            $original_name = strtoupper($name);
            $name = $this->normalize_superglobal($name);
            if(isset($this->definitions['superglobals'][$name])){
                return $this->definitions['superglobals'][$name];
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

        public function _get_magic_const($name){
            $name = $this->normalize_magic_const($name);
            if(isset($this->definitions['magic_constants'][$name])){
                return $this->definitions['magic_constants'][$name];
            }
            return null;
        }

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

        public function _get_declared_classes(array $classes = array()){
            if(count($this->whitelist['classes'])){
                $classes = array();
                foreach($this->whitelist['classes'] as $name => $value){
                    if(class_exists($name)){
                        $classes[] = $name;
                    }
                }
                return $classes;
            } else if(count($this->blacklist['classes'])){
                foreach($classes as $index => $name){
                    if(isset($this->blacklist['classes'][$name])){
                        unset($classes[$index]);
                    }
                }
                reset($classes);
                return $classes;
            }
            return array();
        }

        public function _get_declared_interfaces(array $interfaces = array()){
            if(count($this->whitelist['interfaces'])){
                $interfaces = array();
                foreach($this->whitelist['interfaces'] as $name => $value){
                    if(interface_exists($name)){
                        $interfaces[] = $name;
                    }
                }
                return $interfaces;
            } else if(count($this->blacklist['interfaces'])){
                foreach($interfaces as $index => $name){
                    if(isset($this->blacklist['interfaces'][$name])){
                        unset($interfaces[$index]);
                    }
                }
                reset($interfaces);
                return $interfaces;
            }
            return array();
        }

        public function _get_declared_traits(array $traits = array()){
            if(count($this->whitelist['traits'])){
                $traits = array();
                foreach($this->whitelist['traits'] as $name => $value){
                    if(trait_exists($name)){
                        $traits[] = $name;
                    }
                }
                return $traits;
            } else if(count($this->blacklist['traits'])){
                foreach($traits as $index => $name){
                    if(isset($this->blacklist['traits'][$name])){
                        unset($traits[$index]);
                    }
                }
                reset($traits);
                return $traits;
            }
            return array();
        }

        public function call_func(){
            $arguments = func_get_args();
            $name = array_shift($arguments);
            $original_name = $name;
            $name = $this->normalize_func($name);
            if(isset($this->definitions['functions'][$name]) && is_callable($this->definitions['functions'][$name])){
                $function = $this->definitions['functions'][$name];
                return call_user_func_array($function, $arguments);
            }
            if(is_callable($name)){
                return call_user_func_array($name, $arguments);
            }
            throw new Exception("Sandboxed code attempted to call invalid function: $original_name");
        }

        public function define($type, $name = null, $value = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($_type) && $_type && is_array($name)){
                        foreach($name as $_name => $value){
                            if(is_string($_name) && $_name){
                                $this->define($type, $name, $value);
                            }
                        }
                    }
                }
            } else if(is_string($type) && $type && is_array($name)){
                foreach($name as $_name => $value){
                    if(is_string($_name) && $_name){
                        $this->define($type, $name, $value);
                    }
                }
            } else if($type && $name){
                switch($type){
                    case 'functions':
                        return $this->define_func($name, $value);
                    case 'variables':
                        return $this->define_var($name, $value);
                    case 'globals':
                        return $this->define_global($name, $value);
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
                }
            }
            return $this;
        }

        public function undefine($type, $name = null){
            if(is_array($type)){
                foreach($type as $_type => $name){
                    if(is_string($_type) && $_type && is_array($name)){
                        foreach($name as $_name => $value){
                            if(is_string($_name) && $_name){
                                $this->undefine($type, $name);
                            }
                        }
                    }
                }
            } else if(is_string($type) && $type && is_array($name)){
                foreach($name as $_name => $value){
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
                    case 'globals':
                        return $this->undefine_global($name);
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
                }
            }
            return $this;
        }

        public function define_func($name, $function){
            if(is_array($name)){
                return $this->define_funcs($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed function!");
            }
            $original_name = $name;
            $name = $this->normalize_func($name);
            if(!is_callable($function)){
                throw new Exception("Cannot define uncallable function : $original_name");
            }
            $this->definitions['functions'][$name] = $function;
            return $this;
        }

        public function define_funcs(array $functions = array()){
            foreach($functions as $name => $function){
                $this->define_func($name, $function);
            }
            return $this;
        }

        public function has_defined_funcs(){
            return count($this->definitions['functions']);
        }

        public function is_defined_func($name){
            $name = $this->normalize_func($name);
            return isset($this->definitions['functions'][$name]);
        }

        public function undefine_func($name){
            $name = $this->normalize_func($name);
            if(isset($this->definitions['functions'][$name])){
                unset($this->definitions['functions'][$name]);
            }
            return $this;
        }

        public function undefine_funcs(){
            $this->definitions['functions'] = array();
            return $this;
        }

        public function define_var($name, $value){
            if(is_array($name)){
                return $this->define_vars($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed variable!");
            }
            $this->definitions['variables'][$name] = $value;
            return $this;
        }

        public function define_vars(array $variables = array()){
            foreach($variables as $name => $value){
                $this->define_var($name, $value);
            }
            return $this;
        }

        public function has_defined_vars(){
            return count($this->definitions['variables']);
        }

        public function is_defined_var($name){
            return isset($this->definitions['variables'][$name]);
        }

        public function undefine_var($name){
            if(isset($this->definitions['variables'][$name])){
                unset($this->definitions['variables'][$name]);
            }
            return $this;
        }

        public function undefine_vars(){
            $this->definitions['variables'] = array();
            return $this;
        }

        public function define_global($name, $value){
            if(is_array($name)){
                return $this->define_globals($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed global!");
            }
            $this->definitions['globals'][$name] = $value;
            return $this;
        }

        public function define_globals(array $globals = array()){
            foreach($globals as $name => $value){
                $this->define_global($name, $value);
            }
            return $this;
        }

        public function has_defined_globals(){
            return count($this->definitions['globals']);
        }

        public function is_defined_global($name){
            return isset($this->definitions['globals'][$name]);
        }

        public function undefine_global($name){
            if(isset($this->definitions['globals'][$name])){
                unset($this->definitions['globals'][$name]);
            }
            return $this;
        }

        public function undefine_globals(){
            $this->definitions['globals'] = array();
            return $this;
        }

        public function define_superglobal($name, $value){
            if(is_array($name)){
                return $this->define_superglobals($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed superglobal!");
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

        public function define_superglobals(array $superglobals = array()){
            foreach($superglobals as $name => $value){
                $this->define_superglobal($name, $value);
            }
            return $this;
        }

        public function has_defined_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            return $name ? count($this->definitions['superglobals'][$name]) : count($this->definitions['superglobals']);
        }

        public function is_defined_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->definitions['superglobals'][$name][$key]) : isset($this->definitions['superglobals'][$name]);
        }

        public function undefine_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            if($key !== null){
                if(isset($this->definitions['superglobals'][$name][$key])){
                    unset($this->definitions['superglobals'][$name][$key]);
                }
            } else if(isset($this->definitions['superglobals'][$name])){
                unset($this->definitions['superglobals'][$name]);
            }
            return $this;
        }

        public function undefine_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            if($name){
                $this->definitions['superglobals'][$name] = array();
            } else {
                $this->definitions['superglobals'] = array();
            }
            return $this;
        }

        public function define_const($name, $value){
            if(is_array($name)){
                return $this->define_consts($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed constant!");
            }
            $this->definitions['constants'][$name] = $value;
            return $this;
        }

        public function define_consts(array $constants = array()){
            foreach($constants as $name => $value){
                $this->define_const($name, $value);
            }
            return $this;
        }

        public function has_defined_consts(){
            return count($this->definitions['constants']);
        }

        public function is_defined_const($name){
            return isset($this->definitions['constants'][$name]);
        }

        public function undefine_const($name){
            if(isset($this->definitions['constants'][$name])){
                unset($this->definitions['constants'][$name]);
            }
            return $this;
        }

        public function undefine_consts(){
            $this->definitions['constants'] = array();
            return $this;
        }

        public function define_magic_const($name, $value){
            if(is_array($name)){
                return $this->define_magic_consts($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed magic constant!");
            }
            $name = $this->normalize_magic_const($name);
            $this->definitions['magic_constants'][$name] = $value;
            return $this;
        }

        public function define_magic_consts(array $magic_constants = array()){
            foreach($magic_constants as $name => $value){
                $this->define_magic_const($name, $value);
            }
            return $this;
        }

        public function has_defined_magic_consts(){
            return count($this->definitions['magic_constants']);
        }

        public function is_defined_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->definitions['magic_constants'][$name]);
        }

        public function undefine_magic_const($name){
            $name = $this->normalize_magic_const($name);
            if(isset($this->definitions['magic_constants'][$name])){
                unset($this->definitions['magic_constants'][$name]);
            }
            return $this;
        }

        public function undefine_magic_consts(){
            $this->definitions['magic_constants'] = array();
            return $this;
        }

        public function define_namespace($name){
            if(is_array($name)){
                return $this->define_namespaces($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed namespace!");
            }
            $this->definitions['namespaces'][$name] = $name;
            return $this;
        }

        public function define_namespaces(array $namespaces = array()){
            foreach($namespaces as $name => $alias){
                $this->define_namespace($name, $alias);
            }
            return $this;
        }

        public function has_defined_namespaces(){
            return count($this->definitions['namespaces']);
        }

        public function is_defined_namespace($name){
            return isset($this->definitions['namespaces'][$name]);
        }

        public function undefine_namespace($name){
            if(isset($this->definitions['namespaces'][$name])){
                unset($this->definitions['namespaces'][$name]);
            }
            return $this;
        }

        public function undefine_namespaces(){
            $this->definitions['namespaces'] = array();
            return $this;
        }

        public function define_alias($name, $alias = null){
            if(is_array($name)){
                return $this->define_aliases($name);
            }
            if(!$name){
                throw new Exception("Cannot define unnamed namespace alias!");
            }
            $this->definitions['aliases'][$name] = $alias;
            return $this;
        }

        public function define_aliases(array $aliases = array()){
            foreach($aliases as $name => $alias){
                $this->define_alias($name, $alias);
            }
            return $this;
        }

        public function has_defined_aliases(){
            return count($this->definitions['aliases']);
        }

        public function is_defined_alias($name){
            return isset($this->definitions['aliases'][$name]);
        }

        public function undefine_alias($name){
            if(isset($this->definitions['aliases'][$name])){
                unset($this->definitions['aliases'][$name]);
            }
            return $this;
        }

        public function undefine_aliases(){
            $this->definitions['aliases'] = array();
            return $this;
        }

        public function define_use($name, $alias = null){
            return $this->define_alias($name, $alias);
        }

        public function define_uses(array $uses = array()){
            return $this->define_aliases($uses);
        }

        public function has_defined_uses(){
            return $this->has_defined_aliases();
        }

        public function is_defined_use($name){
            return $this->is_defined_alias($name);
        }

        public function undefine_use($name){
            return $this->undefine_alias($name);
        }

        public function undefine_uses(){
            return $this->undefine_aliases();
        }

        protected function normalize_func($name){
            return strtolower($name);
        }

        protected function normalize_superglobal($name){
            return strtoupper(ltrim($name, '_'));
        }

        protected function normalize_magic_const($name){
            return strtoupper(trim($name, '_'));
        }

        protected function normalize_namespace($name){
            return strtolower($name);
        }

        protected function normalize_alias($name){
            return strtolower($name);
        }

        protected function normalize_use($name){
            return $this->normalize_alias($name);
        }

        protected function normalize_class($name){
            return strtolower($name);
        }

        protected function normalize_interface($name){
            return strtolower($name);
        }

        protected function normalize_trait($name){
            return strtolower($name);
        }

        protected function normalize_keyword($name){
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
            }
            return $name;
        }

        protected function normalize_primitive($name){
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

        protected function normalize_type($name){
            return strtolower($name);
        }

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

        public function has_whitelist($type){
            return count($this->whitelist[$type]);
        }

        public function has_blacklist($type){
            return count($this->blacklist[$type]);
        }

        public function is_whitelisted($type, $name){
            return isset($this->whitelist[$type][$name]);
        }

        public function is_blacklisted($type, $name){
            return isset($this->blacklist[$type][$name]);
        }

        public function has_whitelist_funcs(){
            return count($this->whitelist['functions']);
        }

        public function has_blacklist_funcs(){
            return count($this->blacklist['functions']);
        }

        public function is_whitelisted_func($name){
            $name = $this->normalize_func($name);
            return isset($this->whitelist['functions'][$name]);
        }

        public function is_blacklisted_func($name){
            $name = $this->normalize_func($name);
            return isset($this->blacklist['functions'][$name]);
        }

        public function has_whitelist_vars(){
            return count($this->whitelist['variables']);
        }

        public function has_blacklist_vars(){
            return count($this->blacklist['variables']);
        }

        public function is_whitelisted_var($name){
            return isset($this->whitelist['variables'][$name]);
        }

        public function is_blacklisted_var($name){
            return isset($this->blacklist['variables'][$name]);
        }

        public function has_whitelist_globals(){
            return count($this->whitelist['globals']);
        }

        public function has_blacklist_globals(){
            return count($this->blacklist['globals']);
        }

        public function is_whitelisted_global($name){
            return isset($this->whitelist['globals'][$name]);
        }

        public function is_blacklisted_global($name){
            return isset($this->blacklist['globals'][$name]);
        }

        public function has_whitelist_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            return $name !== null ? (isset($this->whitelist['superglobals'][$name]) ? count($this->whitelist['superglobals'][$name]) : 0) : count($this->whitelist['superglobals']);
        }

        public function has_blacklist_superglobals($name = null){
            $name = $this->normalize_superglobal($name);
            return $name !== null ? (isset($this->blacklist['superglobals'][$name]) ? count($this->blacklist['superglobals'][$name]) : 0) : count($this->blacklist['superglobals']);
        }

        public function is_whitelisted_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->whitelist['superglobals'][$name][$key]) : isset($this->whitelist['superglobals'][$name]);
        }

        public function is_blacklisted_superglobal($name, $key = null){
            $name = $this->normalize_superglobal($name);
            return $key !== null ? isset($this->blacklist['superglobals'][$name][$key]) : isset($this->blacklist['superglobals'][$name]);
        }

        public function has_whitelist_consts(){
            return count($this->whitelist['constants']);
        }

        public function has_blacklist_consts(){
            return count($this->blacklist['constants']);
        }

        public function is_whitelisted_const($name){
            return isset($this->whitelist['constants'][$name]);
        }

        public function is_blacklisted_const($name){
            return isset($this->blacklist['constants'][$name]);
        }

        public function has_whitelist_magic_consts(){
            return count($this->whitelist['magic_constants']);
        }

        public function has_blacklist_magic_consts(){
            return count($this->blacklist['magic_constants']);
        }

        public function is_whitelisted_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->whitelist['magic_constants'][$name]);
        }

        public function is_blacklisted_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return isset($this->blacklist['magic_constants'][$name]);
        }

        public function has_whitelist_namespaces(){
            return count($this->whitelist['namespaces']);
        }

        public function has_blacklist_namespaces(){
            return count($this->blacklist['namespaces']);
        }

        public function is_whitelisted_namespace($name){
            $name = $this->normalize_namespace($name);
            return isset($this->whitelist['namespaces'][$name]);
        }

        public function is_blacklisted_namespace($name){
            $name = $this->normalize_namespace($name);
            return isset($this->blacklist['namespaces'][$name]);
        }

        public function has_whitelist_aliases(){
            return count($this->whitelist['aliases']);
        }

        public function has_blacklist_aliases(){
            return count($this->blacklist['aliases']);
        }

        public function is_whitelisted_alias($name){
            $name = $this->normalize_alias($name);
            return isset($this->whitelist['aliases'][$name]);
        }

        public function is_blacklisted_alias($name){
            $name = $this->normalize_alias($name);
            return isset($this->blacklist['aliases'][$name]);
        }

        public function has_whitelist_uses(){
            return $this->has_whitelist_aliases();
        }

        public function has_blacklist_uses(){
            return $this->has_blacklist_aliases();
        }

        public function is_whitelisted_use($name){
            return $this->is_whitelisted_alias($name);
        }

        public function is_blacklisted_use($name){
            return $this->is_blacklisted_alias($name);
        }

        public function has_whitelist_classes(){
            return count($this->whitelist['classes']);
        }

        public function has_blacklist_classes(){
            return count($this->blacklist['classes']);
        }

        public function is_whitelisted_class($name){
            $name = $this->normalize_class($name);
            return isset($this->whitelist['classes'][$name]);
        }

        public function is_blacklisted_class($name){
            $name = $this->normalize_class($name);
            return isset($this->blacklist['classes'][$name]);
        }

        public function has_whitelist_interfaces(){
            return count($this->whitelist['interfaces']);
        }

        public function has_blacklist_interfaces(){
            return count($this->blacklist['interfaces']);
        }

        public function is_whitelisted_interface($name){
            $name = $this->normalize_interface($name);
            return isset($this->whitelist['interfaces'][$name]);
        }

        public function is_blacklisted_interface($name){
            $name = $this->normalize_interface($name);
            return isset($this->blacklist['interfaces'][$name]);
        }

        public function has_whitelist_traits(){
            return count($this->whitelist['traits']);
        }

        public function has_blacklist_traits(){
            return count($this->blacklist['traits']);
        }

        public function is_whitelisted_trait($name){
            $name = $this->normalize_trait($name);
            return isset($this->whitelist['traits'][$name]);
        }

        public function is_blacklisted_trait($name){
            $name = $this->normalize_trait($name);
            return isset($this->blacklist['traits'][$name]);
        }

        public function has_whitelist_keywords(){
            return count($this->whitelist['keywords']);
        }

        public function has_blacklist_keywords(){
            return count($this->blacklist['keywords']);
        }

        public function is_whitelisted_keyword($name){
            $name = $this->normalize_keyword($name);
            return isset($this->whitelist['keywords'][$name]);
        }

        public function is_blacklisted_keyword($name){
            $name = $this->normalize_keyword($name);
            return isset($this->blacklist['keywords'][$name]);
        }

        public function has_whitelist_operators(){
            return count($this->whitelist['operators']);
        }

        public function has_blacklist_operators(){
            return count($this->blacklist['operators']);
        }

        public function is_whitelisted_operator($name){
            return isset($this->whitelist['operators'][$name]);
        }

        public function is_blacklisted_operator($name){
            return isset($this->blacklist['operators'][$name]);
        }

        public function has_whitelist_primitives(){
            return count($this->whitelist['primitives']);
        }

        public function has_blacklist_primitives(){
            return count($this->blacklist['primitives']);
        }

        public function is_whitelisted_primitive($name){
            $name = $this->normalize_primitive($name);
            return isset($this->whitelist['primitives'][$name]);
        }

        public function is_blacklisted_primitive($name){
            $name = $this->normalize_primitive($name);
            return isset($this->blacklist['primitives'][$name]);
        }

        public function has_whitelist_types(){
            return count($this->whitelist['types']);
        }

        public function has_blacklist_types(){
            return count($this->blacklist['types']);
        }

        public function is_whitelisted_type($name){
            $name = $this->normalize_type($name);
            return isset($this->whitelist['types'][$name]);
        }

        public function is_blacklisted_type($name){
            $name = $this->normalize_type($name);
            return isset($this->blacklist['types'][$name]);
        }

        public function whitelist_func($name){
            $name = $this->normalize_func($name);
            return $this->whitelist('functions', $name);
        }

        public function blacklist_func($name){
            $name = $this->normalize_func($name);
            return $this->blacklist('functions', $name);
        }

        public function dewhitelist_func($name){
            $name = $this->normalize_func($name);
            return $this->dewhitelist('functions', $name);
        }

        public function deblacklist_func($name){
            $name = $this->normalize_func($name);
            return $this->deblacklist('functions', $name);
        }

        public function whitelist_var($name){
            return $this->whitelist('variables', $name);
        }

        public function blacklist_var($name){
            return $this->blacklist('variables', $name);
        }

        public function dewhitelist_var($name){
            return $this->dewhitelist('variables', $name);
        }

        public function deblacklist_var($name){
            return $this->deblacklist('variables', $name);
        }

        public function whitelist_global($name){
            return $this->whitelist('globals', $name);
        }

        public function blacklist_global($name){
            return $this->blacklist('globals', $name);
        }

        public function dewhitelist_global($name){
            return $this->dewhitelist('globals', $name);
        }

        public function deblacklist_global($name){
            return $this->deblacklist('globals', $name);
        }

        public function whitelist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(!isset($this->whitelist['superglobals'][$name]) && is_string($name) && $name){
                $this->whitelist['superglobals'][$name] = array();
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
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

        public function blacklist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(!isset($this->blacklist['superglobals'][$name]) && is_string($name) && $name){
                $this->blacklist['superglobals'][$name] = array();
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
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

        public function dewhitelist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(isset($this->whitelist['superglobals'][$_name]) && is_string($key) && $key && isset($this->whitelist['superglobals'][$_name][$key])){
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

        public function deblacklist_superglobal($name, $key = null){
            if(is_string($name)){
                $name = $this->normalize_superglobal($name);
            }
            if(is_array($name)){
                foreach($name as $_name => $key){
                    if(isset($this->blacklist['superglobals'][$_name]) && is_string($key) && $key && isset($this->blacklist['superglobals'][$_name][$key])){
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

        public function whitelist_const($name){
            return $this->whitelist('constants', $name);
        }

        public function blacklist_const($name){
            return $this->blacklist('constants', $name);
        }

        public function dewhitelist_const($name){
            return $this->dewhitelist('constants', $name);
        }

        public function deblacklist_const($name){
            return $this->deblacklist('constants', $name);
        }

        public function whitelist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->whitelist('magic_constants', $name);
        }

        public function blacklist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->blacklist('magic_constants', $name);
        }

        public function dewhitelist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->dewhitelist('magic_constants', $name);
        }

        public function deblacklist_magic_const($name){
            $name = $this->normalize_magic_const($name);
            return $this->deblacklist('magic_constants', $name);
        }

        public function whitelist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->whitelist('namespaces', $name);
        }

        public function blacklist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->blacklist('namespaces', $name);
        }

        public function dewhitelist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->dewhitelist('namespaces', $name);
        }

        public function deblacklist_namespace($name){
            $name = $this->normalize_namespace($name);
            return $this->deblacklist('namespaces', $name);
        }

        public function whitelist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->whitelist('aliases', $name);
        }

        public function blacklist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->blacklist('aliases', $name);
        }

        public function dewhitelist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->dewhitelist('aliases', $name);
        }

        public function deblacklist_alias($name){
            $name = $this->normalize_alias($name);
            return $this->deblacklist('aliases', $name);
        }

        public function whitelist_use($name){
            return $this->whitelist_alias($name);
        }

        public function blacklist_use($name){
            return $this->blacklist_alias($name);
        }

        public function dewhitelist_use($name){
            return $this->dewhitelist_alias($name);
        }

        public function deblacklist_use($name){
            return $this->deblacklist_alias($name);
        }

        public function whitelist_class($name){
            $name = $this->normalize_class($name);
            return $this->whitelist('classes', $name);
        }

        public function blacklist_class($name){
            $name = $this->normalize_class($name);
            return $this->blacklist('classes', $name);
        }

        public function dewhitelist_class($name){
            $name = $this->normalize_class($name);
            return $this->dewhitelist('classes', $name);
        }

        public function deblacklist_class($name){
            $name = $this->normalize_class($name);
            return $this->deblacklist('classes', $name);
        }

        public function whitelist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->whitelist('interfaces', $name);
        }

        public function blacklist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->blacklist('interfaces', $name);
        }

        public function dewhitelist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->dewhitelist('interfaces', $name);
        }

        public function deblacklist_interface($name){
            $name = $this->normalize_interface($name);
            return $this->deblacklist('interfaces', $name);
        }

        public function whitelist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->whitelist('traits', $name);
        }

        public function blacklist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->blacklist('traits', $name);
        }

        public function dewhitelist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->dewhitelist('traits', $name);
        }

        public function deblacklist_trait($name){
            $name = $this->normalize_trait($name);
            return $this->deblacklist('traits', $name);
        }

        public function whitelist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->whitelist('keywords', $name);
        }

        public function blacklist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->blacklist('keywords', $name);
        }

        public function dewhitelist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->dewhitelist('keywords', $name);
        }

        public function deblacklist_keyword($name){
            $name = $this->normalize_keyword($name);
            return $this->deblacklist('keywords', $name);
        }

        public function whitelist_operator($name){
            return $this->whitelist('operators', $name);
        }

        public function blacklist_operator($name){
            return $this->blacklist('operators', $name);
        }

        public function dewhitelist_operator($name){
            return $this->dewhitelist('operators', $name);
        }

        public function deblacklist_operator($name){
            return $this->deblacklist('operators', $name);
        }

        public function whitelist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->whitelist('primitives', $name);
        }

        public function blacklist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->blacklist('primitives', $name);
        }

        public function dewhitelist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->dewhitelist('primitives', $name);
        }

        public function deblacklist_primitive($name){
            $name = $this->normalize_primitive($name);
            return $this->deblacklist('primitives', $name);
        }

        public function whitelist_type($name){
            $name = $this->normalize_type($name);
            return $this->whitelist('types', $name);
        }

        public function blacklist_type($name){
            $name = $this->normalize_type($name);
            return $this->blacklist('types', $name);
        }

        public function dewhitelist_type($name){
            $name = $this->normalize_type($name);
            return $this->dewhitelist('types', $name);
        }

        public function deblacklist_type($name){
            $name = $this->normalize_type($name);
            return $this->deblacklist('types', $name);
        }

        public function check_func($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed function!");
            }
            $name = $this->normalize_func($name);
            if(!isset($this->definitions['functions'][$name]) || !is_callable($this->definitions['functions'][$name])){
                if(count($this->whitelist['functions'])){
                    if(!isset($this->whitelist['functions'][$name])){
                        throw new Exception("Sandboxed code attempted to call non-whitelisted function: $original_name");
                    }
                } else if(count($this->blacklist['functions'])){
                    if(isset($this->blacklist['functions'][$name])){
                        throw new Exception("Sandboxed code attempted to call blacklisted function: $original_name");
                    }
                } else {
                   throw new Exception("Sandboxed code attempted to call invalid function: $original_name");
                }
            }
        }

        public function check_var($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed variable!");
            }
            if(!isset($this->definitions['variables'][$name])){
                if(count($this->whitelist['variables'])){
                    if(!isset($this->whitelist['variables'][$name])){
                        throw new Exception("Sandboxed code attempted to call non-whitelisted variable: $original_name");
                    }
                } else if(count($this->blacklist['variables'])){
                    if(isset($this->blacklist['variables'][$name])){
                        throw new Exception("Sandboxed code attempted to call blacklisted variable: $original_name");
                    }
                } else if(!$this->allow_variables){
                    throw new Exception("Sandboxed code attempted to call invalid variable: $original_name");
                }
            }
        }

        public function check_global($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed global!");
            }
            if(!isset($this->definitions['globals'][$name])){
                if(count($this->whitelist['globals'])){
                    if(!isset($this->whitelist['globals'][$name])){
                        throw new Exception("Sandboxed code attempted to call non-whitelisted global: $original_name");
                    }
                } else if(count($this->blacklist['globals'])){
                    if(isset($this->blacklist['globals'][$name])){
                        throw new Exception("Sandboxed code attempted to call blacklisted global: $original_name");
                    }
                } else {
                    throw new Exception("Sandboxed code attempted to call invalid global: $original_name");
                }
            }
        }

        public function check_superglobal($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed superglobal!");
            }
            $name = $this->normalize_superglobal($name);
            if(!isset($this->definitions['superglobals'][$name])){
                if(count($this->whitelist['superglobals'])){
                    if(!isset($this->whitelist['superglobals'][$name])){
                        throw new Exception("Sandboxed code attempted to call non-whitelisted superglobal: $original_name");
                    }
                } else if(count($this->blacklist['superglobals'])){
                    if(isset($this->blacklist['superglobals'][$name]) && !count($this->blacklist['superglobals'][$name])){
                        throw new Exception("Sandboxed code attempted to call blacklisted superglobal: $original_name");
                    }
                } else if(!$this->overwrite_superglobals){
                    throw new Exception("Sandboxed code attempted to call invalid superglobal: $original_name");
                }
            }
        }

        public function check_const($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed constant!");
            }
            if(strtolower($name) == 'true' || strtolower($name) == 'false'){
                $this->check_primitive('bool');
                return;
            }
            if(!isset($this->definitions['constants'][$name])){
                if(count($this->whitelist['constants'])){
                    if(!isset($this->whitelist['constants'][$name])){
                        throw new Exception("Sandboxed code attempted to call non-whitelisted constant: $original_name");
                    }
                } else if(count($this->blacklist['constants'])){
                    if(isset($this->blacklist['constants'][$name])){
                        throw new Exception("Sandboxed code attempted to call blacklisted constant: $original_name");
                    }
                } else {
                    throw new Exception("Sandboxed code attempted to call invalid constant: $original_name");
                }
            }
        }

        public function check_magic_const($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed magic constant!");
            }
            $name = $this->normalize_magic_const($name);
            if(!isset($this->definitions['magic_constants'][$name])){
                if(count($this->whitelist['magic_constants'])){
                    if(!isset($this->whitelist['magic_constants'][$name])){
                        $name = '__' . $name . '__';
                        throw new Exception("Sandboxed code attempted to call non-whitelisted magic constant: $original_name");
                    }
                } else if(count($this->blacklist['magic_constants'])){
                    if(isset($this->blacklist['magic_constants'][$name])){
                        $name = '__' . $name . '__';
                        throw new Exception("Sandboxed code attempted to call blacklisted magic constant: $original_name");
                    }
                } else {
                    $name = '__' . $name . '__';
                    throw new Exception("Sandboxed code attempted to call invalid magic constant: $original_name");
                }
            }
        }

        public function check_namespace($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed namespace!");
            }
            $name = $this->normalize_namespace($name);
            if(count($this->whitelist['namespaces'])){
                if(!isset($this->whitelist['namespaces'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted namespace: $original_name");
                }
            } else if(count($this->blacklist['namespaces'])){
                if(isset($this->blacklist['namespaces'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted namespace: $original_name");
                }
            } else if(!$this->allow_namespaces){
                throw new Exception("Sandboxed code attempted to call invalid namespace: $original_name");
            }
        }

        public function check_alias($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed alias!");
            }
            $name = $this->normalize_alias($name);
            if(count($this->whitelist['aliases'])){
                if(!isset($this->whitelist['aliases'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted alias: $original_name");
                }
            } else if(count($this->blacklist['aliases'])){
                if(isset($this->blacklist['aliases'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted alias: $original_name");
                }
            } else if(!$this->allow_aliases){
                throw new Exception("Sandboxed code attempted to call invalid alias: $original_name");
            }
        }

        public function check_use($name){
            $this->check_alias($name);
        }

        public function check_class($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed class!");
            }
            $name = $this->normalize_class($name);
            if(count($this->whitelist['classes'])){
                if(!isset($this->whitelist['classes'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted class: $original_name");
                }
            } else if(count($this->blacklist['classes'])){
                if(isset($this->blacklist['classes'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted class: $original_name");
                }
            } else {
                throw new Exception("Sandboxed code attempted to call invalid class: $original_name");
            }
        }

        public function check_interface($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed interface!");
            }
            $name = $this->normalize_interface($name);
            if(count($this->whitelist['interfaces'])){
                if(!isset($this->whitelist['interfaces'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted interface: $original_name");
                }
            } else if(count($this->blacklist['interfaces'])){
                if(isset($this->blacklist['interfaces'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted interface: $original_name");
                }
            } else {
                throw new Exception("Sandboxed code attempted to call invalidnterface: $original_name");
            }
        }

        public function check_trait($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed trait!");
            }
            $name = $this->normalize_trait($name);
            if(count($this->whitelist['traits'])){
                if(!isset($this->whitelist['traits'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted trait: $original_name");
                }
            } else if(count($this->blacklist['traits'])){
                if(isset($this->blacklist['traits'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted trait: $original_name");
                }
            } else {
                throw new Exception("Sandboxed code attempted to call invalid trait: $original_name");
            }
        }

        public function check_keyword($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed keyword!");
            }
            $name = $this->normalize_keyword($name);
            if(count($this->whitelist['keywords'])){
                if(!isset($this->whitelist['keywords'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted keyword: $original_name");
                }
            } else if(count($this->blacklist['keywords'])){
                if(isset($this->blacklist['keywords'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted keyword: $original_name");
                }
            }
        }

        public function check_operator($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed operator!");
            }
            if(count($this->whitelist['operators'])){
                if(!isset($this->whitelist['operators'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted operator: $original_name");
                }
            } else if(count($this->blacklist['operators'])){
                if(isset($this->blacklist['operators'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted operator: $original_name");
                }
            }
        }

        public function check_primitive($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed primitive!");
            }
            $name = $this->normalize_primitive($name);
            if(count($this->whitelist['primitives'])){
                if(!isset($this->whitelist['primitives'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted primitive: $original_name");
                }
            } else if(count($this->blacklist['primitives'])){
                if(isset($this->blacklist['primitives'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted primitive: $original_name");
                }
            }
        }

        public function check_type($name){
            $original_name = $name;
            if(!$name){
                throw new Exception("Sandboxed code attempted to call unnamed type!");
            }
            $name = $this->normalize_type($name);
            if(count($this->whitelist['types'])){
                if(!isset($this->whitelist['types'][$name])){
                    throw new Exception("Sandboxed code attempted to call non-whitelisted type: $original_name");
                }
            } else if(count($this->blacklist['types'])){
                if(isset($this->blacklist['types'][$name])){
                    throw new Exception("Sandboxed code attempted to call blacklisted type: $original_name");
                }
            } else {
                throw new Exception("Sandboxed code attempted to call invalid type: $original_name");
            }
        }

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
                    throw new Exception("Sandboxed code attempted to pass non-scalar default variable value: $name");
                }
            }
            return count($output) ? ', ' . implode(', ', $output) : '';
        }

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
                    throw new Exception("Sandboxed code attempted to define non-scalar constant value: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        protected function prepare_namespaces(){
            $output = array();
            foreach($this->definitions['namespaces'] as $name){
                if(is_string($name) && $name){
                    $output[] = 'namespace ' . $name . ';';
                } else {
                    throw new Exception("Sandboxed code attempted to create invalid namespace: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        protected function prepare_aliases(){
            $output = array();
            foreach($this->definitions['aliases'] as $name => $alias){
                if(is_string($name) && $name){
                    $output[] = 'use ' . $name . ((is_string($alias) && $alias) ? ' as ' . $alias : '') . ';';
                } else {
                    throw new Exception("Sandboxed code attempted to use invalid namespace alias: $name");
                }
            }
            return count($output) ? implode("\r\n", $output) ."\r\n" : '';
        }

        protected function prepare_uses(){
            return $this->prepare_aliases();
        }

        protected function disassemble($closure){
            if(!is_callable($closure)){
                if(is_string($closure) && $closure){
                    return $closure;
                }
                return '';
            }
            $disassembled_closure = \FunctionParser\FunctionParser::fromCallable($closure);
            if($this->auto_define_vars){
                $this->auto_define($disassembled_closure);
            }
            return $disassembled_closure->getBody();
        }

        protected function auto_whitelist($code, $appended = false){
            $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
            try {
                $statements = $parser->parse('<?php ' . $code);
            } catch (\PHPParser_Error $error) {
                throw new Exception('Error parsing ' . ($appended ? 'appended' : 'prepended') . ' sandboxed code for auto-whitelisting!');
            }
            $traverser = new \PHPParser_NodeTraverser;
            $whitelister = new WhitelistVisitor($this);
            $traverser->addVisitor($whitelister);
            $traverser->traverse($statements);
        }

        protected function auto_define(\FunctionParser\FunctionParser $disassembled_closure){
            $parameters = $disassembled_closure->getReflection()->getParameters();
            foreach($parameters as $param){
                /**
                 * @var \ReflectionParameter $param
                 */
                $this->define_var($param->getName(), $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null);
            }
        }

        public function prepend($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code);
            if($this->auto_whitelist_trusted_code){
                $this->auto_whitelist($code);
            }
            $this->prepended_code .= $code . "\r\n";
            return $this;
        }

        public function append($code){
            if(!$code){
                return $this;
            }
            $code = $this->disassemble($code);
            if($this->auto_whitelist_trusted_code){
                $this->auto_whitelist($code, true);
            }
            $this->appended_code .= "\r\n" . $code . "\r\n";
            return $this;
        }

        public function clear(){
            $this->prepended_code = '';
            $this->appended_code = '';
        }

        public function clear_prepend(){
            $this->prepended_code = '';
        }

        public function clear_append(){
            $this->appended_code = '';
        }

        public function prepare($code){
            if($this->allow_constants && !$this->is_defined_func('define') && ($this->has_whitelist_funcs() || !$this->has_blacklist_funcs())){
                $this->whitelist_func('define');
            }

            $this->preparsed_code = $this->disassemble($code);

            $parser = new \PHPParser_Parser(new \PHPParser_Lexer);

            try {
                $this->parsed_ast = $parser->parse('<?php ' . $this->preparsed_code);
            } catch (\PHPParser_Error $error) {
                throw new Exception("Error parsing sandboxed code!");
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
                '$this->generated_function = function($' . $this->name . $this->prepare_vars() . "){\r\n" .
                $this->prepended_code .
                $this->prepared_code .
                $this->appended_code .
                "\r\n};";

            @eval($this->generated_code);
        }

		public function execute(){
            $arguments = func_get_args();
            if(count($arguments)){
                $this->prepare(array_shift($arguments));
            }

            if(is_callable($this->generated_function)){
                if($this->error_level !== null){
                    error_reporting($this->error_level);
                }
                array_unshift($arguments, $this);
                return call_user_func_array($this->generated_function, $arguments);
            } else {
                throw new Exception("Error generating sandboxed code!");
            }
		}
	}