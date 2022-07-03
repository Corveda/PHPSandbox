<?php
    /** WhitelistVisitor class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    use PhpParser\Node,
        PhpParser\NodeTraverser,
        PhpParser\NodeVisitorAbstract,
        Throwable;

    /**
     * Whitelister class for PHP Sandboxes.
     *
     * This class takes parsed AST code and checks it against the passed PHPSandbox instance configuration to
     * autmatically whitelist trusted code functions, classes, etc. if the appropriate settings are configured.
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 3.0
     */
    class WhitelistVisitor extends NodeVisitorAbstract {
        /** The PHPSandbox instance to check against
         * @var PHPSandbox
         */
        protected PHPSandbox $sandbox;

        /** WhitelistVisitor class constructor
         *
         * This constructor takes a passed PHPSandbox instance to check against for whitelisting trusted code.
         *
         * @param   PHPSandbox   $sandbox            The PHPSandbox instance to check against
         */
        public function __construct(PHPSandbox $sandbox){
            $this->sandbox = $sandbox;
        }

        /** Examine the current PhpParser_Node node against the PHPSandbox configuration for whitelisting trusted code
         *
         * @param Node $node          The trusted $node to examine
         *
         * @throws Throwable
         *
         * @return  bool|null         Return false if node must be removed, or null if no changes to the node are made
         */
        public function leaveNode(Node $node) : ?bool {
            if($node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
                && !$this->sandbox->hasBlacklistedFuncs()
            ){
                $this->sandbox->whitelistFunc($node->name->toString());
            } else if($node instanceof Node\Stmt\Function_
                && $node->name instanceof Node\Identifier
                && !$this->sandbox->hasBlacklistedFuncs()
            ){
                $this->sandbox->whitelistFunc($node->name->toString());
            } else if(($node instanceof Node\Expr\Variable || $node instanceof Node\Stmt\StaticVar)
                && is_string($node->name)
                && $this->sandbox->hasWhitelistedVars()
                && !$this->sandbox->allow_variables
            ){
                $this->sandbox->whitelistVar($node->name);
            } else if($node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Name
                && $node->name->toString() === 'define'
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
            } else if($node instanceof Node\Expr\ConstFetch
                && $node->name instanceof Node\Name
                && !$this->sandbox->hasBlacklistedConsts()
            ){
                $this->sandbox->whitelistConst($node->name->toString());
            } else if($node instanceof Node\Stmt\Class_
                && $node->name instanceof Node\Identifier
                && !$this->sandbox->hasBlacklistedClasses()
            ){
                $this->sandbox->whitelistClass($node->name->toString());
            } else if($node instanceof Node\Stmt\Interface_
                && is_string($node->name)
                && !$this->sandbox->hasBlacklistedInterfaces()
            ){
                $this->sandbox->whitelistInterface($node->name);
            } else if($node instanceof Node\Stmt\Trait_
                && is_string($node->name)
                && !$this->sandbox->hasBlacklistedTraits()
            ){
                $this->sandbox->whitelistTrait($node->name);
            } else if($node instanceof Node\Expr\New_
                && $node->class instanceof Node\Name
                && !$this->sandbox->hasBlacklistedTypes()
            ){
                $this->sandbox->whitelistType($node->class->toString());
            } else if($node instanceof Node\Stmt\Global_
                && $this->sandbox->hasWhitelistedVars()
            ){
                foreach($node->vars as $var){
                    if($var instanceof Node\Expr\Variable){
                        $this->sandbox->whitelistVar($var->name);
                    }
                }
            } else if($node instanceof Node\Stmt\Namespace_){
                if($node->name instanceof Node\Name){
                    $name = $node->name->toString();
                    $this->sandbox->checkNamespace($name);
                    if(!$this->sandbox->isDefinedNamespace($name)){
                        $this->sandbox->defineNamespace($name);
                    }
                }
                return NodeTraverser::REMOVE_NODE;
            } else if($node instanceof Node\Stmt\Use_){
                foreach($node->uses as $use){
                    if($use instanceof Node\Stmt\UseUse
                        && $use->name instanceof Node\Name
                        && (is_string($use->alias) || is_null($use->alias))
                    ){
                        $name = $use->name->toString();
                        $this->sandbox->checkAlias($name);
                        if(!$this->sandbox->isDefinedAlias($name)){
                            $this->sandbox->defineAlias($name, $use->alias);
                        }
                    }
                }
                return NodeTraverser::REMOVE_NODE;
            }
            return null;
        }
    }
