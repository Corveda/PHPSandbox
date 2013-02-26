<?php
    namespace PHPSandbox;

    class SandboxWhitelistVisitor extends \PHPParser_NodeVisitorAbstract {
        /**
         * @var PHPSandbox
         */
        protected $sandbox;

        public function __construct(PHPSandbox $sandbox){
            $this->sandbox = $sandbox;
        }

        public function leaveNode(\PHPParser_Node $node){
            if($node instanceof \PHPParser_Node_Stmt_Class && is_string($node->name) && $this->sandbox->allow_classes && $this->sandbox->auto_whitelist_classes){
                $this->sandbox->whitelist_class($node->name);
                $this->sandbox->whitelist_type($node->name);
            } else if($node instanceof \PHPParser_Node_Stmt_Interface && is_string($node->name) && $this->sandbox->allow_interfaces && $this->sandbox->auto_whitelist_interfaces){
                $this->sandbox->whitelist_interface($node->name);
            } else if($node instanceof \PHPParser_Node_Stmt_Trait && is_string($node->name) && $this->sandbox->allow_traits && $this->sandbox->auto_whitelist_traits){
                $this->sandbox->whitelist_trait($node->name);
            } else if($node instanceof \PHPParser_Node_Expr_FuncCall && $node->name instanceof \PHPParser_Node_Name && $node->name->toString() == 'define' && $this->sandbox->allow_constants && $this->sandbox->auto_whitelist_constants && !$this->sandbox->is_defined_func('define')){
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
            } else if($node instanceof \PHPParser_Node_Stmt_Function && is_string($node->name) && $node->name && $this->sandbox->allow_functions && $this->sandbox->auto_whitelist_functions){
                $this->sandbox->whitelist_func($node->name);
            }
        }
    }