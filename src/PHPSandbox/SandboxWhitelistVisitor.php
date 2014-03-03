<?php
    /** SandboxWhitelistVisitor class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    /**
     * SandboxWhitelister class for PHP Sandboxes.
     *
     * This class takes parsed AST code and checks it against the passed PHPSandbox instance configuration to
     * autmatically whitelist sandboxed code functions, classes, etc. if the appropriate settings are configured.
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <fieryprophet@yahoo.com>
     * @version 1.3.1
     */
    class SandboxWhitelistVisitor extends \PHPParser_NodeVisitorAbstract {
        /** The PHPSandbox instance to check against
         * @var PHPSandbox
         */
        protected $sandbox;
        /** SandboxWhitelistVisitor class constructor
         *
         * This constructor takes a passed PHPSandbox instance to check against for whitelisting sandboxed code.
         *
         * @param   PHPSandbox   $sandbox            The PHPSandbox instance to check against
         */
        public function __construct(PHPSandbox $sandbox){
            $this->sandbox = $sandbox;
        }
        /** Examine the current PHPParser_Node node against the PHPSandbox configuration for whitelisting sandboxed code
         *
         * @param   \PHPParser_Node   $node          The sandboxed $node to examine
         */
        public function leaveNode(\PHPParser_Node $node){
            if($node instanceof \PHPParser_Node_Stmt_Class && is_string($node->name) && $this->sandbox->allow_classes && $this->sandbox->auto_whitelist_classes && !$this->sandbox->has_blacklist_classes()){
                $this->sandbox->whitelist_class($node->name);
                $this->sandbox->whitelist_type($node->name);
            } else if($node instanceof \PHPParser_Node_Stmt_Interface && is_string($node->name) && $this->sandbox->allow_interfaces && $this->sandbox->auto_whitelist_interfaces && !$this->sandbox->has_blacklist_interfaces()){
                $this->sandbox->whitelist_interface($node->name);
            } else if($node instanceof \PHPParser_Node_Stmt_Trait && is_string($node->name) && $this->sandbox->allow_traits && $this->sandbox->auto_whitelist_traits && !$this->sandbox->has_blacklist_traits()){
                $this->sandbox->whitelist_trait($node->name);
            } else if($node instanceof \PHPParser_Node_Expr_FuncCall && $node->name instanceof \PHPParser_Node_Name && $node->name->toString() == 'define' && $this->sandbox->allow_constants && $this->sandbox->auto_whitelist_constants && !$this->sandbox->is_defined_func('define') && !$this->sandbox->has_blacklist_consts()){
                $name = isset($node->args[0]) ? $node->args[0] : null;
                if($name && $name instanceof \PHPParser_Node_Arg && $name->value instanceof \PHPParser_Node_Scalar_String && is_string($name->value->value) && $name->value->value){
                    $this->sandbox->whitelist_const($name->value->value);
                }
            } else if($node instanceof \PHPParser_Node_Stmt_Global && $this->sandbox->allow_globals && $this->sandbox->auto_whitelist_globals && $this->sandbox->has_whitelist_vars()){
                foreach($node->vars as $var){
                    /**
                     * @var \PHPParser_Node_Expr_Variable    $var
                     */
                    if($var instanceof \PHPParser_Node_Expr_Variable){
                        $this->sandbox->whitelist_var($var->name);
                    }
                }
            } else if($node instanceof \PHPParser_Node_Stmt_Function && is_string($node->name) && $node->name && $this->sandbox->allow_functions && $this->sandbox->auto_whitelist_functions && !$this->sandbox->has_blacklist_funcs()){
                $this->sandbox->whitelist_func($node->name);
            }
        }
    }