<?php
    /** SandboxWhitelistVisitor class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    use PhpParser\Node,
        PhpParser\NodeVisitorAbstract;

    /**
     * SandboxWhitelister class for PHP Sandboxes.
     *
     * This class takes parsed AST code and checks it against the passed PHPSandbox instance configuration to
     * autmatically whitelist sandboxed code functions, classes, etc. if the appropriate settings are configured.
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 3.0
     */
    class SandboxWhitelistVisitor extends NodeVisitorAbstract {
        /** The PHPSandbox instance to check against
         * @var PHPSandbox
         */
        protected PHPSandbox $sandbox;

        /** SandboxWhitelistVisitor class constructor
         *
         * This constructor takes a passed PHPSandbox instance to check against for whitelisting sandboxed code.
         *
         * @param   PHPSandbox   $sandbox            The PHPSandbox instance to check against
         */
        public function __construct(PHPSandbox $sandbox){
            $this->sandbox = $sandbox;
        }

        /** Examine the current PhpParser\Node node against the PHPSandbox configuration for whitelisting sandboxed code
         *
         * @param   Node   $node          The sandboxed $node to examine
         *
         * @return  void
         */
        public function leaveNode(Node $node){
            if($node instanceof Node\Stmt\Class_
                && $node->name instanceof Node\Identifier
                && $this->sandbox->allow_classes
                && $this->sandbox->auto_whitelist_classes && !$this->sandbox->hasBlacklistedClasses()
            ){
                $this->sandbox->whitelistClass($node->name->toString());
                $this->sandbox->whitelistType($node->name->toString());
            } else if($node instanceof Node\Stmt\Interface_
                && is_string($node->name)
                && $this->sandbox->allow_interfaces
                && $this->sandbox->auto_whitelist_interfaces
                && !$this->sandbox->hasBlacklistedInterfaces()
            ){
                $this->sandbox->whitelistInterface($node->name);
            } else if($node instanceof Node\Stmt\Trait_
                && (is_string($node->name) || $node->name instanceof Node\Identifier)
                && $this->sandbox->allow_traits
                && $this->sandbox->auto_whitelist_traits
                && !$this->sandbox->hasBlacklistedTraits()
            ){
                $this->sandbox->whitelistTrait($node->name);
            } else if($node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'define'
                && $this->sandbox->allow_constants
                && $this->sandbox->auto_whitelist_constants
                && !$this->sandbox->isDefinedFunc('define')
                && !$this->sandbox->hasBlacklistedConsts()
            ){
                $name = $node->args[0] ?? null;
                if($name instanceof Node\Arg
                    && $name->value instanceof Node\Scalar\String_
                    && is_string($name->value->value)
                    && $name->value->value
                ){
                    $this->sandbox->whitelistConst($name->value->value);
                }
            } else if($node instanceof Node\Stmt\Global_
                && $this->sandbox->allow_globals
                && $this->sandbox->auto_whitelist_globals
                && $this->sandbox->hasWhitelistedVars()
            ){
                foreach($node->vars as $var){
                    if($var instanceof Node\Expr\Variable){
                        $this->sandbox->whitelistVar($var->name);
                    }
                }
            } else if($node instanceof Node\Stmt\Function_
                && $node->name instanceof Node\Identifier
                && $this->sandbox->allow_functions
                && $this->sandbox->auto_whitelist_functions
                && !$this->sandbox->hasBlacklistedFuncs()
            ){
                $this->sandbox->whitelistFunc($node->name->toString());
            }
        }
    }
