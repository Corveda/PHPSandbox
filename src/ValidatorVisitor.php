<?php
    /** ValidatorVisitor class declaration
     * @package PHPSandbox
     */
    namespace PHPSandbox;

    use PhpParser\Node,
        PhpParser\NodeTraverser,
        PhpParser\NodeVisitorAbstract,
        Throwable;

    /**
     * Validator class for PHP Sandboxes.
     *
     * This class takes parsed AST code and checks it against the passed PHPSandbox instance
     * configuration for errors, and throws exceptions if they are found
     *
     * @namespace PHPSandbox
     *
     * @author  Elijah Horton <elijah@corveda.com>
     * @version 3.0
     */
    class ValidatorVisitor extends NodeVisitorAbstract {
        /** The PHPSandbox instance to check against
         * @var PHPSandbox
         */
        protected PHPSandbox $sandbox;
        /** ValidatorVisitor class constructor
         *
         * This constructor takes a passed PHPSandbox instance to check against for validating sandboxed code.
         *
         * @param   PHPSandbox   $sandbox            The PHPSandbox instance to check against
         */
        public function __construct(PHPSandbox $sandbox){
            $this->sandbox = $sandbox;
        }
        /** Examine the current PhpParser_Node node against the PHPSandbox configuration for validating sandboxed code
         *
         * @param   Node   $node          The sandboxed $node to validate
         *
         * @throws  Throwable             Throws an exception if validation fails
         *
         * @return  Node|bool|null        Return rewritten node, false if node must be removed, or null if no changes to the node are made
         */
        public function leaveNode(Node $node){
            if($node instanceof Node\Param){
                $this->validateTypeNode($node->type, $node);
            }
            if($node instanceof Node\Stmt\Property){
                $this->validateTypeNode($node->type, $node);
            }
            if($node instanceof Node\Stmt\ClassConst){
                $this->validateTypeNode($node->type, $node);
            }
            if($node instanceof Node\FunctionLike){
                $this->validateTypeNode($node->getReturnType(), $node);
            }
            if($node instanceof Node\Arg && $this->sandbox->sandbox_strings && !$this->memberArgumentNeedsReference($node)){
                if($node->name !== null && !$this->sandbox->allow_named_arguments){
                    $this->sandbox->validationError('Sandboxed code attempted to use named arguments!', Error::VALID_KEYWORD_ERROR, $node, $node->name->toString());
                }
                $wrapped = new Node\Expr\FuncCall(
                    new Node\Name\FullyQualified(($node->value instanceof Node\Expr\Variable) ? 'PHPSandbox\\wrapByRef' : 'PHPSandbox\\wrap'),
                    [
                        new Node\Arg($node->value),
                        new Node\Arg(new Node\Expr\StaticCall(new Node\Name\FullyQualified("PHPSandbox\\PHPSandbox"), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))])),
                    ],
                    $node->getAttributes()
                );
                return new Node\Arg($wrapped, $node->byRef, $node->unpack, $node->getAttributes(), $node->name);
            } else if($node instanceof Node\Arg && $node->name !== null && !$this->sandbox->allow_named_arguments){
                $this->sandbox->validationError('Sandboxed code attempted to use named arguments!', Error::VALID_KEYWORD_ERROR, $node, $node->name->toString());
            } else if($node instanceof Node\Attribute){
                if(!$this->sandbox->allow_attributes){
                    $this->sandbox->validationError('Sandboxed code attempted to use an attribute!', Error::ATTRIBUTE_ERROR, $node, $node->name->toString());
                }
                $this->sandbox->checkAttribute($node->name->toString());
            } else if($node instanceof Node\Stmt\InlineHTML){
                if(!$this->sandbox->allow_escaping){
                    $this->sandbox->validationError("Sandboxed code attempted to escape to HTML!", Error::ESCAPE_ERROR, $node);
                }
            }else if($node instanceof Node\Expr\Cast){
                if(!$this->sandbox->allow_casting){
                    $this->sandbox->validationError("Sandboxed code attempted to cast!", Error::CAST_ERROR, $node);
                }
                if($this->isConstantExpression($node)){
                    return null;
                }
                if($node instanceof Node\Expr\Cast\Int_){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_intval', [new Node\Arg($node->expr)], $node->getAttributes());
                } else if($node instanceof Node\Expr\Cast\Double){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_floatval', [new Node\Arg($node->expr)], $node->getAttributes());
                } else if($node instanceof Node\Expr\Cast\Bool_){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_boolval', [new Node\Arg($node->expr)], $node->getAttributes());
                } else if($node instanceof Node\Expr\Cast\Array_){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_arrayval', [new Node\Arg($node->expr)], $node->getAttributes());
                } else if($node instanceof Node\Expr\Cast\Object_){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_objectval', [new Node\Arg($node->expr)], $node->getAttributes());
                }
            } else if($node instanceof Node\Expr\FuncCall){
                if($node->isFirstClassCallable() && !$this->sandbox->allow_closures){
                    $this->sandbox->validationError('Sandboxed code attempted to create a first-class callable!', Error::CLOSURE_ERROR, $node);
                }
                if($node->name instanceof Node\Name){
                    $name = strtolower($node->name->toString());
                    if($name === 'clone' && PHP_VERSION_ID >= 80500){
                        $this->sandbox->checkKeyword('clone');
                        if(count($node->args) > 1){
                            return new Node\Expr\MethodCall(
                                new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                                '_clone_with',
                                $node->args,
                                $node->getAttributes()
                            );
                        }
                        return null;
                    }
                    if(!$this->sandbox->checkFunc($name)){
                        $this->sandbox->validationError('Function failed custom validation!', Error::VALID_FUNC_ERROR, $node);
                    }
                    if($node->isFirstClassCallable()){
                        if($this->sandbox->isDefinedFunc($name)){
                            return new Node\Expr\MethodCall(
                                new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                                '_get_func_callable',
                                [new Node\Arg(new Node\Scalar\String_($name))],
                                $node->getAttributes()
                            );
                        }
                        return null;
                    }
                    if($this->sandbox->isDefinedFunc($name)){
                        $args = $node->args;
                        array_unshift($args, new Node\Arg(new Node\Scalar\String_($name)));
                        return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), 'call_func', $args, $node->getAttributes());
                    }
                    if($this->sandbox->overwrite_defined_funcs && in_array($name, PHPSandbox::$defined_funcs)){
                        return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_' . $name, [new Node\Arg(new Node\Expr\FuncCall(new Node\Name([$name])))], $node->getAttributes());
                    }
                    if($this->sandbox->overwrite_sandboxed_string_funcs && in_array($name, PHPSandbox::$sandboxed_string_funcs)){
                        $args = $node->args;
                        return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_' . $name, $args, $node->getAttributes());
                    }
                    if($this->sandbox->overwrite_func_get_args && in_array($name, PHPSandbox::$arg_funcs)){
                        if($name === 'func_get_arg'){
                            $index = new Node\Arg(new Node\Scalar\LNumber(0));
                            if(isset($node->args[0]) && $node->args[0] instanceof Node\Arg){
                                $index = $node->args[0];
                            }
                            return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_' . $name, [new Node\Arg(new Node\Expr\FuncCall(new Node\Name(['func_get_args']))), $index], $node->getAttributes());
                        }
                        return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_' . $name, [new Node\Arg(new Node\Expr\FuncCall(new Node\Name(['func_get_args'])))], $node->getAttributes());
                    }
                } else {
                    return new Node\Expr\Ternary(
                        new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), 'checkFunc', [new Node\Arg($node->name)], $node->getAttributes()),
                        $node,
                        new Node\Expr\ConstFetch(new Node\Name('null'))
                    );
                }
            } else if($node instanceof Node\Stmt\Function_){
                if(!$this->sandbox->allow_functions){
                    $this->sandbox->validationError('Sandboxed code attempted to define function!', Error::DEFINE_FUNC_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('function')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'function');
                }
                if(!$node->name){
                    $this->sandbox->validationError('Sandboxed code attempted to define unnamed function!', Error::DEFINE_FUNC_ERROR, $node, '');
                }
                if($this->sandbox->isDefinedFunc($node->name)){
                    $this->sandbox->validationError('Sandboxed code attempted to redefine function!', Error::DEFINE_FUNC_ERROR, $node, $node->name);
                }
                if($node->byRef && !$this->sandbox->allow_references){
                    $this->sandbox->validationError('Sandboxed code attempted to define function return by reference!', Error::BYREF_ERROR, $node);
                }
            } else if($node instanceof Node\Expr\Closure){
                if(!$this->sandbox->allow_closures){
                    $this->sandbox->validationError('Sandboxed code attempted to create a closure!', Error::CLOSURE_ERROR, $node);
                }
            } else if($node instanceof Node\Expr\ArrowFunction){
                if(!$this->sandbox->allow_closures){
                    $this->sandbox->validationError('Sandboxed code attempted to create an arrow function!', Error::CLOSURE_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('fn')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'fn');
                }
            } else if($node instanceof Node\Stmt\Enum_){
                if(!$this->sandbox->allow_enums){
                    $this->sandbox->validationError('Sandboxed code attempted to define an enum!', Error::ENUM_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('enum')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'enum');
                }
                if(!$node->name){
                    $this->sandbox->validationError('Sandboxed code attempted to define an unnamed enum!', Error::ENUM_ERROR, $node);
                }
                $this->sandbox->checkEnum($node->name->toString());
                foreach($node->implements as $implement){
                    $this->sandbox->checkInterface($implement->toString());
                }
            } else if($node instanceof Node\PropertyHook){
                if(!$this->sandbox->allow_property_hooks){
                    $this->sandbox->validationError('Sandboxed code attempted to define a property hook!', Error::PROPERTY_HOOK_ERROR, $node, $node->name->toString());
                }
            } else if($node instanceof Node\Stmt\Class_){
                if(!$this->sandbox->allow_classes){
                    $this->sandbox->validationError('Sandboxed code attempted to define class!', Error::DEFINE_CLASS_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('class')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'class');
                }
                if(!$node->name){
                    $this->sandbox->validationError('Sandboxed code attempted to define unnamed class!', Error::DEFINE_CLASS_ERROR, $node, '');
                }
                if(!$this->sandbox->checkClass($node->name)){
                    $this->sandbox->validationError('Class failed custom validation!', Error::VALID_CLASS_ERROR, $node, $node->name);
                }
                if($node->extends instanceof Node\Name){
                    if(!$this->sandbox->checkKeyword('extends')){
                        $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'extends');
                    }
                    if(!$node->extends->toString()){
                        $this->sandbox->validationError('Sandboxed code attempted to extend unnamed class!', Error::DEFINE_CLASS_ERROR, $node, '');
                    }
                    if(!$this->sandbox->checkClass($node->extends->toString(), true)){
                        $this->sandbox->validationError('Class extension failed custom validation!', Error::VALID_CLASS_ERROR, $node, $node->extends->toString());
                    }
                }
                if(is_array($node->implements)){
                    if(!$this->sandbox->checkKeyword('implements')){
                        $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'implements');
                    }
                    foreach($node->implements as $implement){
                        /**
                         * @var Node\Name   $implement
                         */
                        if(!$implement->toString()){
                            $this->sandbox->validationError('Sandboxed code attempted to implement unnamed interface!', Error::DEFINE_INTERFACE_ERROR, $node, '');
                        }
                        if(!$this->sandbox->checkInterface($implement->toString())){
                            $this->sandbox->validationError('Interface failed custom validation!', Error::VALID_INTERFACE_ERROR, $node, $implement->toString());
                        }
                    }
                }
            } else if($node instanceof Node\Stmt\Interface_){
                if(!$this->sandbox->allow_interfaces){
                    $this->sandbox->validationError('Sandboxed code attempted to define interface!', Error::DEFINE_INTERFACE_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('interface')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'interface');
                }
                if(!$node->name){
                    $this->sandbox->validationError('Sandboxed code attempted to define unnamed interface!', Error::DEFINE_INTERFACE_ERROR, $node, '');
                }
                if(!$this->sandbox->checkInterface($node->name)){
                    $this->sandbox->validationError('Interface failed custom validation!', Error::VALID_INTERFACE_ERROR, $node, $node->name);
                }
            } else if($node instanceof Node\Stmt\Trait_){
                if(!$this->sandbox->allow_traits){
                    $this->sandbox->validationError('Sandboxed code attempted to define trait!', Error::DEFINE_TRAIT_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('trait')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'trait');
                }
                if(!$node->name){
                    $this->sandbox->validationError('Sandboxed code attempted to define unnamed trait!', Error::DEFINE_TRAIT_ERROR, $node, '');
                }
                if(!$this->sandbox->checkTrait($node->name)){
                    $this->sandbox->validationError('Trait failed custom validation!', Error::VALID_TRAIT_ERROR, $node, $node->name);
                }
            } else if($node instanceof Node\Stmt\TraitUse){
                if(!$this->sandbox->checkKeyword('use')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'use');
                }
                if(is_array($node->traits)){
                    foreach($node->traits as $trait){
                        /**
                         * @var Node\Name   $trait
                         */
                        if(!$trait->toString()){
                            $this->sandbox->validationError('Sandboxed code attempted to use unnamed trait!', Error::DEFINE_TRAIT_ERROR, $node, '');
                        }
                        if(!$this->sandbox->checkTrait($trait->toString())){
                            $this->sandbox->validationError('Trait failed custom validation!', Error::VALID_TRAIT_ERROR, $node, $trait->toString());
                        }
                    }
                }
            } else if($node instanceof Node\Expr\Yield_){
                if(!$this->sandbox->allow_generators){
                    $this->sandbox->validationError('Sandboxed code attempted to create a generator!', Error::GENERATOR_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('yield')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'yield');
                }
            } else if($node instanceof Node\Stmt\Global_){
                if(!$this->sandbox->allow_globals){
                    $this->sandbox->validationError('Sandboxed code attempted to use global keyword!', Error::GLOBALS_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('global')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'global');
                }
                foreach($node->vars as $var){
                    /**
                     * @var Node\Expr\Variable    $var
                     */
                    if($var instanceof Node\Expr\Variable){
                        if(!$this->sandbox->checkGlobal($var->name)){
                            $this->sandbox->validationError('Global failed custom validation!', Error::VALID_GLOBAL_ERROR, $node, $var->name);
                        }
                    } else {
                        $this->sandbox->validationError('Sandboxed code attempted to pass non-variable to global keyword!', Error::DEFINE_GLOBAL_ERROR, $node);
                    }
                }
            } else if($node instanceof Node\Expr\Variable){
                if(!is_string($node->name)){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named variable call!', Error::DYNAMIC_VAR_ERROR, $node);
                }
                if($node->name == $this->sandbox->name){
                    $this->sandbox->validationError('Sandboxed code attempted to access the PHPSandbox instance!', Error::SANDBOX_ACCESS_ERROR, $node);
                }
                if(in_array($node->name, PHPSandbox::$superglobals)){
                    if(!$this->sandbox->checkSuperglobal($node->name)){
                        $this->sandbox->validationError('Superglobal failed custom validation!', Error::VALID_SUPERGLOBAL_ERROR, $node, $node->name);
                    }
                    if($this->sandbox->overwrite_superglobals){
                        return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_get_superglobal', [new Node\Arg(new Node\Scalar\String_($node->name))], $node->getAttributes());
                    }
                } else {
                    if(!$this->sandbox->checkVar($node->name)){
                        $this->sandbox->validationError('Variable failed custom validation!', Error::VALID_VAR_ERROR, $node, $node->name);
                    }
                }
            } else if($node instanceof Node\Stmt\StaticVar){
                if(!$this->sandbox->allow_static_variables){
                    $this->sandbox->validationError('Sandboxed code attempted to create static variable!', Error::STATIC_VAR_ERROR, $node);
                }
                if(!is_string($node->var->name)){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named static variable call!', Error::DYNAMIC_STATIC_VAR_ERROR, $node);
                }
                if(!$this->sandbox->checkVar($node->var->name)){
                    $this->sandbox->validationError('Variable failed custom validation!', Error::VALID_VAR_ERROR, $node, $node->var->name);
                }
                if($node->default instanceof Node\Expr\New_){
                    $node->default = $node->default->args[0];
                }
            } else if($node instanceof Node\Stmt\Const_){
                if(!$this->sandbox->allow_constants){
                    $this->sandbox->validationError('Sandboxed code attempted to declare a global constant!', Error::GLOBAL_CONST_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('const')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'const');
                }
                foreach($node->consts as $constant){
                    $this->sandbox->checkConst($constant->name->toString());
                }
            } else if($node instanceof Node\Expr\ConstFetch){
                if(!$node->name instanceof Node\Name){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named constant call!', Error::DYNAMIC_CONST_ERROR, $node);
                }
                if(!$this->sandbox->checkConst($node->name->toString())){
                    $this->sandbox->validationError('Constant failed custom validation!', Error::VALID_CONST_ERROR, $node, $node->name->toString());
                }
            } else if($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\StaticPropertyFetch){
                if($this->isExternalStaticProperty($node->var)){
                    return $this->sandboxStaticPropertyCall('_set_static_property', $node->var, [$node->expr], $node);
                }
            } else if($node instanceof Node\Expr\AssignRef && $node->var instanceof Node\Expr\StaticPropertyFetch){
                if($this->isExternalStaticProperty($node->var)){
                    return $this->sandboxStaticPropertyCall('_set_static_property_reference', $node->var, [$node->expr], $node);
                }
            } else if($node instanceof Node\Expr\AssignOp && $node->var instanceof Node\Expr\StaticPropertyFetch){
                if($this->isExternalStaticProperty($node->var)){
                    return $this->sandboxStaticPropertyCall('_assign_static_property', $node->var, [new Node\Scalar\String_($this->isOperator($node)), $node->expr], $node);
                }
            } else if(($node instanceof Node\Expr\PreInc || $node instanceof Node\Expr\PreDec || $node instanceof Node\Expr\PostInc || $node instanceof Node\Expr\PostDec)
                && $node->var instanceof Node\Expr\StaticPropertyFetch
            ){
                if($this->isExternalStaticProperty($node->var)){
                    $operator = $node instanceof Node\Expr\PreInc ? '++' : ($node instanceof Node\Expr\PreDec ? '--' : ($node instanceof Node\Expr\PostInc ? 'post++' : 'post--'));
                    return $this->sandboxStaticPropertyCall('_update_static_property', $node->var, [new Node\Scalar\String_($operator)], $node);
                }
            } else if($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\PropertyFetch){
                if(!$node->var->name instanceof Node\Identifier){
                    if(!$this->sandbox->allow_dynamic_properties){
                        $this->sandbox->validationError('Sandboxed code attempted dynamically-named property assignment!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                    }
                }
                return $this->sandboxPropertyCall('_set_property', $node->var, [$node->expr], $node);
            } else if($node instanceof Node\Expr\AssignRef && $node->var instanceof Node\Expr\PropertyFetch){
                if(!$node->var->name instanceof Node\Identifier){
                    if(!$this->sandbox->allow_dynamic_properties){
                        $this->sandbox->validationError('Sandboxed code attempted dynamically-named property reference assignment!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                    }
                }
                return $this->sandboxPropertyCall('_set_property_reference', $node->var, [$node->expr], $node);
            } else if($node instanceof Node\Expr\AssignOp && $node->var instanceof Node\Expr\PropertyFetch){
                if(!$node->var->name instanceof Node\Identifier){
                    if(!$this->sandbox->allow_dynamic_properties){
                        $this->sandbox->validationError('Sandboxed code attempted dynamically-named property assignment!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                    }
                }
                return $this->sandboxPropertyCall('_assign_property', $node->var, [new Node\Scalar\String_($this->isOperator($node)), $node->expr], $node);
            } else if(($node instanceof Node\Expr\PreInc || $node instanceof Node\Expr\PreDec || $node instanceof Node\Expr\PostInc || $node instanceof Node\Expr\PostDec)
                && $node->var instanceof Node\Expr\PropertyFetch
            ){
                if(!$node->var->name instanceof Node\Identifier && !$this->sandbox->allow_dynamic_properties){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named property update!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                }
                $operator = $node instanceof Node\Expr\PreInc ? '++' : ($node instanceof Node\Expr\PreDec ? '--' : ($node instanceof Node\Expr\PostInc ? 'post++' : 'post--'));
                return $this->sandboxPropertyCall('_update_property', $node->var, [new Node\Scalar\String_($operator)], $node);
            } else if($node instanceof Node\Expr\Isset_){
                $expressions = [];
                $remaining = [];
                foreach($node->vars as $variable){
                    if($variable instanceof Node\Expr\PropertyFetch){
                        if(!$variable->name instanceof Node\Identifier && !$this->sandbox->allow_dynamic_properties){
                            $this->sandbox->validationError('Sandboxed code attempted dynamically-named property isset!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                        }
                        $expressions[] = $this->sandboxPropertyCall('_isset_property', $variable, [], $node);
                    } else if($variable instanceof Node\Expr\StaticPropertyFetch && $this->isExternalStaticProperty($variable)){
                        $expressions[] = $this->sandboxStaticPropertyCall('_isset_static_property', $variable, [], $node);
                    } else {
                        $remaining[] = $variable;
                    }
                }
                if($remaining){
                    $expressions[] = new Node\Expr\Isset_($remaining, $node->getAttributes());
                }
                if($expressions){
                    $result = array_shift($expressions);
                    foreach($expressions as $expression){
                        $result = new Node\Expr\BinaryOp\BooleanAnd($result, $expression, $node->getAttributes());
                    }
                    return $result;
                }
            } else if($node instanceof Node\Stmt\Unset_){
                $statements = [];
                $rewritten = false;
                foreach($node->vars as $variable){
                    if($variable instanceof Node\Expr\PropertyFetch){
                        if(!$variable->name instanceof Node\Identifier){
                            if(!$this->sandbox->allow_dynamic_properties){
                                $this->sandbox->validationError('Sandboxed code attempted dynamically-named property unset!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                            }
                        }
                        $statements[] = new Node\Stmt\Expression($this->sandboxPropertyCall('_unset_property', $variable, [], $node), $node->getAttributes());
                        $rewritten = true;
                    } else if($variable instanceof Node\Expr\StaticPropertyFetch && $this->isExternalStaticProperty($variable)){
                        $statements[] = new Node\Stmt\Expression($this->sandboxStaticPropertyCall('_unset_static_property', $variable, [], $node), $node->getAttributes());
                        $rewritten = true;
                    } else {
                        $statements[] = new Node\Stmt\Unset_([$variable], $node->getAttributes());
                    }
                }
                if($rewritten){
                    return $statements;
                }
            } else if($node instanceof Node\Expr\PropertyFetch || $node instanceof Node\Expr\NullsafePropertyFetch){
                if(!$node->name instanceof Node\Identifier){
                    if(!$this->sandbox->allow_dynamic_properties){
                        $this->sandbox->validationError('Sandboxed code attempted dynamically-named property access!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                    }
                }
                $parent = $node->getAttribute('parent');
                if(($parent instanceof Node\Expr\Assign || $parent instanceof Node\Expr\AssignOp) && $parent->var === $node){
                    return null;
                }
                if($parent instanceof Node\Expr\AssignRef && $parent->var === $node){
                    return null;
                }
                if(($parent instanceof Node\Expr\PreInc || $parent instanceof Node\Expr\PreDec || $parent instanceof Node\Expr\PostInc || $parent instanceof Node\Expr\PostDec) && $parent->var === $node){
                    return null;
                }
                if($parent instanceof Node\Expr\Isset_){
                    return null;
                }
                if($parent instanceof Node\Stmt\Unset_){
                    return null;
                }
                if($node instanceof Node\Expr\NullsafePropertyFetch){
                    $this->sandbox->checkOperator('?->');
                    $temporary = new Node\Expr\Variable('__PHPSandbox_nullsafe_' . substr(md5($this->sandbox->name . $node->getStartFilePos()), 0, 12));
                    $fetch = new Node\Expr\PropertyFetch($temporary, $node->name, $node->getAttributes());
                    return new Node\Expr\Ternary(
                        new Node\Expr\BinaryOp\Identical(new Node\Expr\Assign($temporary, $node->var), new Node\Expr\ConstFetch(new Node\Name('null'))),
                        new Node\Expr\ConstFetch(new Node\Name('null')),
                        $this->sandboxPropertyCall('_get_property', $fetch, [], $node),
                        $node->getAttributes()
                    );
                }
                return $this->sandboxPropertyCall($this->propertyNeedsReference($node) ? '_get_property_reference' : '_get_property', $node, [], $node);
            } else if($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\NullsafeMethodCall){
                if($node instanceof Node\Expr\NullsafeMethodCall){
                    $this->sandbox->checkOperator('?->');
                }
                if($node->isFirstClassCallable() && !$this->sandbox->allow_closures){
                    $this->sandbox->validationError('Sandboxed code attempted to create a first-class method callable!', Error::CLOSURE_ERROR, $node);
                }
                if(!$node->name instanceof Node\Identifier){
                    if(!$this->sandbox->allow_dynamic_methods){
                        $this->sandbox->validationError('Sandboxed code attempted dynamically-named method call!', Error::DYNAMIC_METHOD_ERROR, $node);
                    }
                }
                $method = $node->name instanceof Node\Identifier ? new Node\Scalar\String_($node->name->toString()) : $node->name;
                if($node->isFirstClassCallable()){
                    return new Node\Expr\MethodCall(
                        new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                        '_get_method_callable',
                        [new Node\Arg($node->var), new Node\Arg($method)],
                        $node->getAttributes()
                    );
                }
                $args = [new Node\Arg($node->var), new Node\Arg($method), new Node\Arg($this->argumentArray($node->args))];
                $call = new Node\Expr\MethodCall(
                    new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                    '_call_method',
                    $args,
                    $node->getAttributes()
                );
                if($node instanceof Node\Expr\NullsafeMethodCall){
                    $temporary = new Node\Expr\Variable('__PHPSandbox_nullsafe_' . substr(md5($this->sandbox->name . $node->getStartFilePos()), 0, 12));
                    $call->args[0] = new Node\Arg($temporary);
                    return new Node\Expr\Ternary(
                        new Node\Expr\BinaryOp\Identical(new Node\Expr\Assign($temporary, $node->var), new Node\Expr\ConstFetch(new Node\Name('null'))),
                        new Node\Expr\ConstFetch(new Node\Name('null')),
                        $call,
                        $node->getAttributes()
                    );
                }
                return $call;
            } else if($node instanceof Node\Expr\ClassConstFetch || $node instanceof Node\Expr\StaticCall || $node instanceof Node\Expr\StaticPropertyFetch){
                $class = $node->class;
                if(!$class instanceof Node\Name){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named class call!', Error::DYNAMIC_CLASS_ERROR, $node);
                }
                if($this->sandbox->isDefinedClass($class->toString())){
                    $node->class = new Node\Name\FullyQualified($this->sandbox->getDefinedClass($class->toString()));
                    $class = $node->class;
                }
                /**
                 * @var Node\Name    $class
                 */
                if(!$this->sandbox->checkClass($class->toString())){
                    $this->sandbox->validationError('Class constant failed custom validation!', Error::VALID_CLASS_ERROR, $node, $class->toString());
                }
                $className = $class->toString();
                $runtimeClassName = $this->sandbox->resolveAlias($className);
                if(!in_array(strtolower($className), ['self', 'static', 'parent'], true)){
                    $node->class = new Node\Name\FullyQualified($runtimeClassName, $node->class->getAttributes());
                }
                if($node instanceof Node\Expr\StaticCall){
                    if($node->isFirstClassCallable() && !$this->sandbox->allow_closures){
                        $this->sandbox->validationError('Sandboxed code attempted to create a first-class static callable!', Error::CLOSURE_ERROR, $node);
                    }
                    if(!$node->name instanceof Node\Identifier){
                        if(!$this->sandbox->allow_dynamic_methods){
                            $this->sandbox->validationError('Sandboxed code attempted dynamically-named static method call!', Error::DYNAMIC_METHOD_ERROR, $node);
                        }
                    }
                    if(!in_array(strtolower($className), ['self', 'static', 'parent'], true)){
                        $method = $node->name instanceof Node\Identifier ? new Node\Scalar\String_($node->name->toString()) : $node->name;
                        return new Node\Expr\MethodCall(
                            new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                            $node->isFirstClassCallable() ? '_get_static_callable' : '_call_static',
                            [
                                new Node\Arg(new Node\Scalar\String_($runtimeClassName)),
                                new Node\Arg($method),
                                ...($node->isFirstClassCallable() ? [] : [new Node\Arg($this->argumentArray($node->args))]),
                            ],
                            $node->getAttributes()
                        );
                    }
                } else if($node instanceof Node\Expr\StaticPropertyFetch){
                    if(!$node->name instanceof Node\VarLikeIdentifier){
                        if(!$this->sandbox->allow_dynamic_properties){
                            $this->sandbox->validationError('Sandboxed code attempted dynamically-named static property access!', Error::DYNAMIC_PROPERTY_ERROR, $node);
                        }
                    }
                    if(!in_array(strtolower($className), ['self', 'static', 'parent'], true)){
                        $parent = $node->getAttribute('parent');
                        if((($parent instanceof Node\Expr\Assign || $parent instanceof Node\Expr\AssignOp || $parent instanceof Node\Expr\AssignRef) && $parent->var === $node)
                            || (($parent instanceof Node\Expr\PreInc || $parent instanceof Node\Expr\PreDec || $parent instanceof Node\Expr\PostInc || $parent instanceof Node\Expr\PostDec) && $parent->var === $node)
                            || $parent instanceof Node\Expr\Isset_
                            || $parent instanceof Node\Stmt\Unset_
                        ){
                            return null;
                        }
                        return $this->sandboxStaticPropertyCall($this->staticPropertyNeedsReference($node) ? '_get_static_property_reference' : '_get_static_property', $node, [], $node);
                    }
                } else if($node instanceof Node\Expr\ClassConstFetch){
                    if(!$node->name instanceof Node\Identifier){
                        if(!$this->sandbox->allow_dynamic_class_constants){
                            $this->sandbox->validationError('Sandboxed code attempted dynamically-named class constant access!', Error::DYNAMIC_CLASS_CONST_ERROR, $node);
                        }
                        if(!in_array(strtolower($className), ['self', 'static', 'parent'], true)){
                            return new Node\Expr\MethodCall(
                                new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                                '_get_class_constant',
                                [new Node\Arg(new Node\Scalar\String_($runtimeClassName)), new Node\Arg($node->name)],
                                $node->getAttributes()
                            );
                        }
                    } else if(strtolower($node->name->toString()) !== 'class' && !in_array(strtolower($className), ['self', 'static', 'parent'], true)){
                        $this->sandbox->checkClassConstant($runtimeClassName, $node->name->toString());
                    }
                }
                return $node;
            } else if($node instanceof Node\Param && $node->type instanceof Node\Name){
                $class = $node->type->toString();
                if($this->sandbox->isDefinedClass($class)){
                    $node->type = new Node\Name($this->sandbox->getDefinedClass($class));
                }
                return $node;
            } else if($node instanceof Node\Expr\New_){
                if(!$this->sandbox->allow_objects){
                    $this->sandbox->validationError('Sandboxed code attempted to create object!', Error::CREATE_OBJECT_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('new')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'new');
                }
                if(!$node->class instanceof Node\Name){
                    $this->sandbox->validationError('Sandboxed code attempted dynamically-named class call!', Error::DYNAMIC_CLASS_ERROR, $node);
                }
                $class = $node->class->toString();
                if($this->sandbox->isDefinedClass($class)){
                    $node->class = new Node\Name($this->sandbox->getDefinedClass($class));
                }
                $this->sandbox->checkType($class);
                return $node;
            } else if($node instanceof Node\Expr\ErrorSuppress){
                if(!$this->sandbox->allow_error_suppressing){
                    $this->sandbox->validationError('Sandboxed code attempted to suppress error!', Error::ERROR_SUPPRESS_ERROR, $node);
                }
            } else if($node instanceof Node\Expr\AssignRef){
                if(!$this->sandbox->allow_references){
                    $this->sandbox->validationError('Sandboxed code attempted to assign by reference!', Error::BYREF_ERROR, $node);
                }
            } else if($node instanceof Node\Stmt\HaltCompiler){
                if(!$this->sandbox->allow_halting){
                    $this->sandbox->validationError('Sandboxed code attempted to halt compiler!', Error::HALT_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('halt')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'halt');
                }
            } else if($node instanceof Node\Stmt\Namespace_){
                if(!$this->sandbox->allow_namespaces){
                    $this->sandbox->validationError('Sandboxed code attempted to define namespace!', Error::DEFINE_NAMESPACE_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('namespace')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'namespace');
                }
                if($node->name instanceof Node\Name){
                    $namespace = $node->name->toString();
                    $this->sandbox->checkNamespace($namespace);
                    if(!$this->sandbox->isDefinedNamespace($namespace)){
                        $this->sandbox->defineNamespace($namespace);
                    }
                } else {
                    $this->sandbox->validationError('Sandboxed code attempted use invalid namespace!', Error::DEFINE_NAMESPACE_ERROR, $node);
                }
                return $node->stmts;
            } else if($node instanceof Node\Stmt\Use_){
                if(!$this->sandbox->allow_aliases){
                    $this->sandbox->validationError('Sandboxed code attempted to use namespace and/or alias!', Error::DEFINE_ALIAS_ERROR, $node);
                }
                if(!$this->sandbox->checkKeyword('use')){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'use');
                }
                foreach($node->uses as $use){
                    /**
                     * @var Node\Stmt\UseUse    $use
                     */
                    if($use instanceof Node\Stmt\UseUse && $use->name instanceof Node\Name && (is_string($use->alias) || $use->alias instanceof Node\Identifier || is_null($use->alias))){
                        $this->sandbox->checkAlias($use->name->toString());
                        if($use->alias){
                            if(!$this->sandbox->checkKeyword('as')){
                                $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, 'as');
                            }
                        }
                        $this->sandbox->whitelistClass($use->getAlias());
                        $this->sandbox->whitelistType($use->getAlias());
                        $this->sandbox->defineAlias($use->name->toString(), $use->getAlias());
                    } else {
                        $this->sandbox->validationError('Sandboxed code attempted use invalid namespace or alias!', Error::DEFINE_ALIAS_ERROR, $node);
                    }
                }
                return NodeTraverser::REMOVE_NODE;
            } else if($node instanceof Node\Expr\ShellExec){
                if($this->sandbox->isDefinedFunc('shell_exec')){
                    $args = [
                        new Node\Arg(new Node\Scalar\String_('shell_exec')),
                        new Node\Arg(new Node\Scalar\String_(implode('', $node->parts)))
                    ];
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), 'call_func', $args, $node->getAttributes());
                }
                if($this->sandbox->hasWhitelistedFuncs()){
                    if(!$this->sandbox->isWhitelistedFunc('shell_exec')){
                        $this->sandbox->validationError('Sandboxed code attempted to use shell execution backticks when the shell_exec function is not whitelisted!', Error::BACKTICKS_ERROR, $node);
                    }
                } else if($this->sandbox->hasBlacklistedFuncs() && $this->sandbox->isBlacklistedFunc('shell_exec')){
                    $this->sandbox->validationError('Sandboxed code attempted to use shell execution backticks when the shell_exec function is blacklisted!', Error::BACKTICKS_ERROR, $node);
                }
                if(!$this->sandbox->allow_backticks){
                    $this->sandbox->validationError('Sandboxed code attempted to use shell execution backticks!', Error::BACKTICKS_ERROR, $node);
                }
            } else if($name = $this->isMagicConst($node)){
                if(!$this->sandbox->checkMagicConst($name)){
                    $this->sandbox->validationError('Magic constant failed custom validation!', Error::VALID_MAGIC_CONST_ERROR, $node, $name);
                }
                if($this->sandbox->isDefinedMagicConst($name)){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_get_magic_const', [new Node\Arg(new Node\Scalar\String_($name))], $node->getAttributes());
                } else if(($filepath = $this->sandbox->getExecutingFile()) && in_array($name, ['__DIR__', '__FILE__'], true)){
                    return new Node\Scalar\String_($name === '__DIR__' ? dirname($filepath) : $filepath);
                }
            } else if($name = $this->isKeyword($node)){
                if(!$this->sandbox->checkKeyword($name)){
                    $this->sandbox->validationError('Keyword failed custom validation!', Error::VALID_KEYWORD_ERROR, $node, $name);
                }
                if($node instanceof Node\Expr\Include_ && !$this->sandbox->allow_includes){
                    $this->sandbox->validationError('Sandboxed code attempted to include files!', Error::INCLUDE_ERROR, $node, $name);
                } else if($node instanceof Node\Expr\Include_ &&
                    (
                        ($node->type == Node\Expr\Include_::TYPE_INCLUDE && $this->sandbox->isDefinedFunc('include'))
                        || ($node->type == Node\Expr\Include_::TYPE_INCLUDE_ONCE && $this->sandbox->isDefinedFunc('include_once'))
                        || ($node->type == Node\Expr\Include_::TYPE_REQUIRE && $this->sandbox->isDefinedFunc('require'))
                        || ($node->type == Node\Expr\Include_::TYPE_REQUIRE_ONCE && $this->sandbox->isDefinedFunc('require_once'))
                    )
                ){
                    return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), 'call_func', [new Node\Arg(new Node\Scalar\String_($name)), new Node\Arg($node->expr)], $node->getAttributes());
                } else if($node instanceof Node\Expr\Include_ && $this->sandbox->sandbox_includes){
                    switch($node->type){
                        case Node\Expr\Include_::TYPE_INCLUDE_ONCE:
                            return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_include_once', [new Node\Arg($node->expr)], $node->getAttributes());
                        case Node\Expr\Include_::TYPE_REQUIRE:
                            return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_require', [new Node\Arg($node->expr)], $node->getAttributes());
                        case Node\Expr\Include_::TYPE_REQUIRE_ONCE:
                            return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_require_once', [new Node\Arg($node->expr)], $node->getAttributes());

                        case Node\Expr\Include_::TYPE_INCLUDE:
                        default:
                            return new Node\Expr\MethodCall(new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]), '_include', [new Node\Arg($node->expr)], $node->getAttributes());
                    }
                }
            } else if($name = $this->isOperator($node)){
                if(!$this->sandbox->checkOperator($name)){
                    $this->sandbox->validationError('Operator failed custom validation!', Error::VALID_OPERATOR_ERROR, $node, $name);
                }
            } else if($name = $this->isPrimitive($node)){
                if(!$this->sandbox->checkPrimitive($name)){
                    $this->sandbox->validationError('Primitive failed custom validation!', Error::VALID_PRIMITIVE_ERROR, $node, $name);
                }
            }
            return null;
        }

        /** Rewrite an instance-property operation through the runtime member guard. */
        protected function sandboxPropertyCall(string $method, Node\Expr\PropertyFetch $property, array $extra, Node $origin) : Node\Expr\MethodCall {
            $name = $property->name instanceof Node\Identifier
                ? new Node\Scalar\String_($property->name->toString())
                : $property->name;
            $arguments = [
                new Node\Arg($property->var),
                new Node\Arg($name),
            ];
            foreach($extra as $argument){
                $arguments[] = new Node\Arg($argument);
            }
            return new Node\Expr\MethodCall(
                new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                $method,
                $arguments,
                $origin->getAttributes()
            );
        }

        /** Rewrite a static-property operation through the same runtime member policy. */
        protected function sandboxStaticPropertyCall(string $method, Node\Expr\StaticPropertyFetch $property, array $extra, Node $origin) : Node\Expr\MethodCall {
            $name = $property->name instanceof Node\VarLikeIdentifier
                ? new Node\Scalar\String_($property->name->toString())
                : $property->name;
            $arguments = [
                new Node\Arg(new Node\Scalar\String_($property->class->toString())),
                new Node\Arg($name),
            ];
            foreach($extra as $argument){
                $arguments[] = new Node\Arg($argument);
            }
            return new Node\Expr\MethodCall(
                new Node\Expr\StaticCall(new Node\Name\FullyQualified('PHPSandbox\\PHPSandbox'), 'getSandbox', [new Node\Arg(new Node\Scalar\String_($this->sandbox->name))]),
                $method,
                $arguments,
                $origin->getAttributes()
            );
        }

        /** Convert call arguments to an invoke-array while retaining references and named/unpacked semantics. */
        protected function argumentArray(array $arguments) : Node\Expr\Array_ {
            $items = [];
            foreach($arguments as $argument){
                $key = $argument->name !== null ? new Node\Scalar\String_($argument->name->toString()) : null;
                $items[] = new Node\ArrayItem(
                    $argument->value,
                    $key,
                    !$argument->unpack && $this->isReferenceableExpression($argument->value),
                    $argument->getAttributes(),
                    $argument->unpack
                );
            }
            return new Node\Expr\Array_($items, ['kind' => Node\Expr\Array_::KIND_SHORT]);
        }

        protected function memberArgumentNeedsReference(Node\Arg $argument) : bool {
            $parent = $argument->getAttribute('parent');
            return ($parent instanceof Node\Expr\MethodCall
                    || $parent instanceof Node\Expr\NullsafeMethodCall
                    || $parent instanceof Node\Expr\StaticCall)
                && $this->isReferenceableExpression($argument->value);
        }

        protected function isReferenceableExpression(Node\Expr $expression) : bool {
            return $expression instanceof Node\Expr\Variable
                || $expression instanceof Node\Expr\ArrayDimFetch
                || $expression instanceof Node\Expr\PropertyFetch
                || $expression instanceof Node\Expr\StaticPropertyFetch;
        }

        protected function isExternalStaticProperty(Node\Expr\StaticPropertyFetch $property) : bool {
            if(!$property->class instanceof Node\Name){
                $this->sandbox->validationError('Sandboxed code attempted dynamically-named class access!', Error::DYNAMIC_CLASS_ERROR, $property);
            }
            if(!$property->name instanceof Node\VarLikeIdentifier && !$this->sandbox->allow_dynamic_properties){
                $this->sandbox->validationError('Sandboxed code attempted dynamically-named static property access!', Error::DYNAMIC_PROPERTY_ERROR, $property);
            }
            return !in_array(strtolower($property->class->toString()), ['self', 'static', 'parent'], true);
        }

        /** Determine whether a property must remain a writable reference through nested array dimensions. */
        protected function propertyNeedsReference(Node\Expr\PropertyFetch $property) : bool {
            $current = $property;
            $parent = $current->getAttribute('parent');
            while($parent instanceof Node\Expr\ArrayDimFetch && $parent->var === $current){
                $current = $parent;
                $parent = $current->getAttribute('parent');
            }
            if($parent instanceof Node\Expr\AssignRef && ($parent->var === $current || $parent->expr === $current)){
                return true;
            }
            if(($parent instanceof Node\Expr\Assign || $parent instanceof Node\Expr\AssignOp) && $parent->var === $current){
                return true;
            }
            if(($parent instanceof Node\Expr\PreInc || $parent instanceof Node\Expr\PreDec || $parent instanceof Node\Expr\PostInc || $parent instanceof Node\Expr\PostDec) && $parent->var === $current){
                return true;
            }
            return $parent instanceof Node\Stmt\Unset_;
        }

        protected function staticPropertyNeedsReference(Node\Expr\StaticPropertyFetch $property) : bool {
            $current = $property;
            $parent = $current->getAttribute('parent');
            while($parent instanceof Node\Expr\ArrayDimFetch && $parent->var === $current){
                $current = $parent;
                $parent = $current->getAttribute('parent');
            }
            return (($parent instanceof Node\Expr\Assign || $parent instanceof Node\Expr\AssignOp || $parent instanceof Node\Expr\AssignRef) && ($parent->var === $current || ($parent instanceof Node\Expr\AssignRef && $parent->expr === $current)))
                || (($parent instanceof Node\Expr\PreInc || $parent instanceof Node\Expr\PreDec || $parent instanceof Node\Expr\PostInc || $parent instanceof Node\Expr\PostDec) && $parent->var === $current)
                || $parent instanceof Node\Stmt\Unset_;
        }

        /** Constant expressions cannot contain runtime guard calls; their syntax is still policy-validated. */
        protected function isConstantExpression(Node $node) : bool {
            $parent = $node->getAttribute('parent');
            while($parent instanceof Node){
                if($parent instanceof Node\Expr\Closure || $parent instanceof Node\Expr\ArrowFunction){
                    return false;
                }
                if($parent instanceof Node\Stmt\Const_
                    || $parent instanceof Node\Stmt\ClassConst
                    || $parent instanceof Node\Attribute
                ){
                    return true;
                }
                $parent = $parent->getAttribute('parent');
            }
            return false;
        }

        /** Validate all leaves in nullable, union, intersection, and DNF type declarations. */
        protected function validateTypeNode($type, Node $origin) : void {
            if($type === null){
                return;
            }
            if($type instanceof Node\NullableType || $type instanceof Node\UnionType || $type instanceof Node\IntersectionType){
                $types = $type instanceof Node\NullableType ? [$type->type] : $type->types;
                foreach($types as $nested){
                    $this->validateTypeNode($nested, $origin);
                }
                return;
            }
            if($type instanceof Node\Identifier){
                $this->sandbox->checkPrimitive($type->toString());
                return;
            }
            if($type instanceof Node\Name){
                $name = $type->toString();
                if(!in_array(strtolower($name), ['self', 'static', 'parent'], true)){
                    $this->sandbox->checkType($name);
                }
                return;
            }
            $this->sandbox->validationError('Sandboxed code used an unsupported type declaration!', Error::VALID_TYPE_ERROR, $origin, $type);
        }

        /** Test the current PhpParser_Node node to see if it is a magic constant, and return the name if it is and null if it is not
         *
         * @param   Node   $node          The sandboxed $node to test
         *
         * @return  string|null       Return string name of node, or null if it is not a magic constant
         */
        protected function isMagicConst(Node $node) : ?string {
            return ($node instanceof Node\Scalar\MagicConst) ? $node->getName() : null;
        }
        /** Test the current PhpParser_Node node to see if it is a keyword, and return the name if it is and null if it is not
         *
         * @param   Node   $node          The sandboxed $node to test
         *
         * @return  string|null       Return string name of node, or null if it is not a keyword
         */
        protected function isKeyword(Node $node) : ?string {
            switch($node->getType()){
                case 'Expr_Eval':
                    return 'eval';
                case 'Expr_Exit':
                    return 'exit';
                case 'Expr_Include':
                    return 'include';
                case 'Stmt_Echo':
                case 'Expr_Print':  //for our purposes print is treated as functionally equivalent to echo
                    return 'echo';
                case 'Expr_Clone':
                    return 'clone';
                case 'Expr_Empty':
                    return 'empty';
                case 'Expr_Yield':
                    return 'yield';
                case 'Expr_YieldFrom':
                    return 'yield from';
                case 'Expr_Throw':
                    return 'throw';
                case 'Expr_ArrowFunction':
                    return 'fn';
                case 'Expr_Match':
                    return 'match';
                case 'Stmt_Enum':
                    return 'enum';
                case 'Stmt_Goto':
                case 'Stmt_Label':  //no point in using labels without goto
                    return 'goto';
                case 'Stmt_If':
                case 'Stmt_Else':    //no point in using ifs without else
                case 'Stmt_ElseIf':  //no point in using ifs without elseif
                    return 'if';
                case 'Stmt_Break':
                    return 'break';
                case 'Stmt_Switch':
                case 'Stmt_Case':    //no point in using cases without switch
                    return 'switch';
                case 'Stmt_Try':
                case 'Stmt_Catch':    //no point in using catch without try
                case 'Stmt_TryCatch': //no point in using try, catch or finally without try
                    return 'try';
                case 'Stmt_Throw':
                    return 'throw';
                case 'Stmt_Unset':
                    return 'unset';
                case 'Stmt_Return':
                    return 'return';
                case 'Stmt_Static':
                    return 'static';
                case 'Stmt_Const':
                    return 'const';
                case 'Stmt_While':
                case 'Stmt_Do':       //no point in using do without while
                    return 'while';
                case 'Stmt_Declare':
                case 'Stmt_DeclareDeclare': //no point in using declare key=>value without declare
                    return 'declare';
                case 'Stmt_For':
                case 'Stmt_Foreach':  //no point in using foreach without for
                    return 'for';
                case 'Expr_Instanceof':
                    return 'instanceof';
                case 'Expr_Isset':
                    return 'isset';
                case 'Expr_List':
                    return 'list';
            }
            return null;
        }
        /** Test the current PhpParser_Node node to see if it is an operator, and return the name if it is and null if it is not
         *
         * @param   Node   $node          The sandboxed $node to test
         *
         * @return  string|null       Return string name of node, or null if it is not an operator
         */
        protected function isOperator(Node $node) : ?string {
            $type = str_replace(['Expr_AssignOp_', 'Expr_BinaryOp_'], ['Expr_Assign', 'Expr_'], $node->getType());
            switch($type){
                case 'Expr_Assign':
                    return '=';
                case 'Expr_AssignBitwiseAnd':
                    return '&=';
                case 'Expr_AssignBitwiseOr':
                    return '|=';
                case 'Expr_AssignBitwiseXor':
                    return '^=';
                case 'Expr_AssignConcat':
                    return '.=';
                case 'Expr_AssignDiv':
                    return '/=';
                case 'Expr_AssignMinus':
                    return '-=';
                case 'Expr_AssignMod':
                    return '%=';
                case 'Expr_AssignMul':
                    return '*=';
                case 'Expr_AssignPlus':
                    return '+=';
                case 'Expr_AssignRef':
                    return '=&';
                case 'Expr_AssignShiftLeft':
                    return '<<=';
                case 'Expr_AssignShiftRight':
                    return '>>=';
                case 'Expr_AssignCoalesce':
                    return '??=';
                case 'Expr_AssignPow':
                    return '**=';
                case 'Expr_BitwiseAnd':
                    return '&';
                case 'Expr_BitwiseNot':
                    return '~';
                case 'Expr_BitwiseOr':
                    return '|';
                case 'Expr_BitwiseXor':
                    return '^';
                case 'Expr_BooleanAnd':
                    return '&&';
                case 'Expr_BooleanNot':
                    return '!';
                case 'Expr_BooleanOr':
                    return '||';
                case 'Expr_Concat':
                    return '.';
                case 'Expr_Coalesce':
                    return '??';
                case 'Expr_Div':
                    return '/';
                case 'Expr_Equal':
                    return '==';
                case 'Expr_Greater':
                    return '>';
                case 'Expr_GreaterOrEqual':
                    return '>=';
                case 'Expr_Identical':
                    return '===';
                case 'Expr_LogicalAnd':
                    return 'and';
                case 'Expr_LogicalOr':
                    return 'or';
                case 'Expr_LogicalXor':
                    return 'xor';
                case 'Expr_Minus':
                    return '-';
                case 'Expr_Mod':
                    return '%';
                case 'Expr_Mul':
                    return '*';
                case 'Expr_Pow':
                    return '**';
                case 'Expr_NotEqual':
                    return '!=';
                case 'Expr_NotIdentical':
                    return '!==';
                case 'Expr_Plus':
                    return '+';
                case 'Expr_PostDec':
                    return 'n--';
                case 'Expr_PostInc':
                    return 'n++';
                case 'Expr_PreDec':
                    return '--n';
                case 'Expr_PreInc':
                    return '++n';
                case 'Expr_ShiftLeft':
                    return '<<';
                case 'Expr_ShiftRight':
                    return '>>';
                case 'Expr_Smaller':
                    return '<';
                case 'Expr_SmallerOrEqual':
                    return '<=';
                case 'Expr_Spaceship':
                    return '<=>';
                case 'Expr_Pipe':
                    return '|>';
                case 'Expr_Ternary':
                    return '?';
                case 'Expr_UnaryMinus':
                    return '-n';
                case 'Expr_UnaryPlus':
                    return '+n';
            }
            return null;
        }
        /** Test the current PhpParser_Node node to see if it is a primitive, and return the name if it is and null if it is not
         *
         * @param   Node   $node          The sandboxed $node to test
         *
         * @return  string|null       Return string name of node, or null if it is not a primitive
         */
        protected function isPrimitive(Node $node) : ?string {
            switch($node->getType()){
                case 'Expr_Cast_Array':
                case 'Expr_Array':
                    return 'array';
                case 'Expr_Cast_Bool': //booleans are treated as constants otherwise. . .
                    return 'bool';
                case 'Expr_Cast_String':
                case 'Scalar_String':
                case 'Scalar_Encapsed':
                    return 'string';
                case 'Expr_Cast_Double':
                case 'Scalar_DNumber':
                case 'Scalar_Float':
                    return 'float';
                case 'Expr_Cast_Int':
                case 'Scalar_LNumber':
                case 'Scalar_Int':
                    return 'int';
                case 'Expr_Cast_Object':
                    return 'object';
            }
            return null;
        }
    }
