<?php

use PHPSandbox\AstPolicyVisitor;
use PHPSandbox\Error;
use PHPSandbox\PHPSandbox;
use PHPUnit\Framework\TestCase;

final class Php85PolicyFixture
{
    public const VERSION = '4';
    public int $value = 1;
    public array $items = ['original'];
    public static int $counter = 1;

    public function increment(int $by = 1): int
    {
        return $this->value += $by;
    }

    public static function label(): string
    {
        return 'fixture';
    }
}

final class Php85PolicyTest extends TestCase
{
    public function testArrowFunctionsUseClosurePolicy(): void
    {
        $sandbox = new PHPSandbox();
        $this->expectException(Error::class);
        $sandbox->execute('return fn() => 42;');
    }

    public function testArrowFunctionsAndFirstClassCallablesCanBeAllowed(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_closures = true;
        $sandbox->whitelistFunc('strlen');

        self::assertSame(5, $sandbox->execute('$length = strlen(...); return $length("hello");'));
    }

    public function testEnumsAreDeniedByDefault(): void
    {
        $sandbox = new PHPSandbox();
        $this->expectException(Error::class);
        $sandbox->execute('enum PolicyDefaultDeniedEnum { case A; }');
    }

    public function testEnumsAndImplicitMembersCanBeAllowed(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_enums = true;

        self::assertSame('Ready', $sandbox->execute(
            'enum PolicyAllowedEnum { case Ready; } return PolicyAllowedEnum::Ready->name;'
        ));
    }

    public function testAttributesHaveAnIndependentPolicy(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_classes = true;
        $sandbox->allow_attributes = true;
        $sandbox->whitelistAttribute('Attribute');
        $sandbox->whitelistAttribute('PolicyMarkerAttribute');

        self::assertTrue($sandbox->execute(
            '#[Attribute] class PolicyMarkerAttribute {} #[PolicyMarkerAttribute] class PolicyMarkedClass {} return true;'
        ));
    }

    public function testExternalMembersAreDeniedUntilGranted(): void
    {
        $sandbox = new PHPSandbox([], [], ['fixture' => new Php85PolicyFixture()]);
        $this->expectException(Error::class);
        $sandbox->execute('return $fixture->increment();');
    }

    public function testMethodAndPropertyPoliciesGuardRuntimeObjects(): void
    {
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'increment');
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'label');
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'value');
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'counter');

        self::assertSame(7, $sandbox->execute('$fixture->value = 3; return $fixture->increment(by: 4);'));
        self::assertSame(7, $fixture->value, 'Defined objects retain identity instead of being serialized and cloned.');
    }

    public function testNestedMutationAndUnsetUsePropertyPolicy(): void
    {
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'items');
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'value');

        self::assertTrue($sandbox->execute('$fixture->items[] = "added"; unset($fixture->value); return true;'));
        self::assertSame(['original', 'added'], $fixture->items);
        self::assertFalse(isset($fixture->value));
    }

    public function testPropertyReferenceAssignmentUsesPropertyPolicy(): void
    {
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'value');

        self::assertSame(8, $sandbox->execute('$value = 4; $fixture->value =& $value; $value = 8; return $fixture->value;'));
        self::assertSame(8, $fixture->value);
    }

    public function testNamedArgumentsCanBeDisabled(): void
    {
        $sandbox = new PHPSandbox([], [], ['fixture' => new Php85PolicyFixture()]);
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'increment');
        $sandbox->allow_named_arguments = false;
        $this->expectException(Error::class);
        $sandbox->execute('return $fixture->increment(by: 2);');
    }

    public function testMatchExpressionIsCovered(): void
    {
        $sandbox = new PHPSandbox();
        self::assertSame('hello', $sandbox->execute('return match (2) { 2 => "hello", default => "x" };'));
    }

    public function testPipeOperatorIsCovered(): void
    {
        if (PHP_VERSION_ID < 80500) {
            self::markTestSkipped('The pipe operator executes on PHP 8.5+.');
        }
        $sandbox = new PHPSandbox();
        $sandbox->allow_closures = true;
        $sandbox->whitelistFunc('strlen');
        self::assertSame(5, $sandbox->execute('return match (2) { 2 => "hello", default => "x" } |> strlen(...);'));
    }

    public function testPropertyHooksAreConfigurable(): void
    {
        if (PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks execute on PHP 8.4+.');
        }
        $sandbox = new PHPSandbox();
        $sandbox->allow_classes = true;
        $sandbox->allow_property_hooks = true;
        $sandbox->whitelistFunc('strtoupper');

        self::assertSame('HELLO', $sandbox->execute(
            'class PolicyHookedClass { public string $value { set => strtoupper($value); } }'
            . '$object = new PolicyHookedClass; $object->value = "hello"; return $object->value;'
        ));
    }

    public function testCloneWithUsesPropertyPolicy(): void
    {
        if (PHP_VERSION_ID < 80500) {
            self::markTestSkipped('Clone-with executes on PHP 8.5+.');
        }
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'value');

        self::assertSame(9, $sandbox->execute('return (clone($fixture, ["value" => 9]))->value;'));
        self::assertSame(1, $fixture->value);
    }

    public function testTemplateMemberPoliciesUseCanonicalEntries(): void
    {
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->import([
            'schema_version' => 4,
            'whitelist' => [
                'method' => [Php85PolicyFixture::class . '::increment'],
                'property' => [Php85PolicyFixture::class . '::$value'],
            ],
        ], PHPSandbox::IMPORT_WHITELIST);

        self::assertSame(3, $sandbox->execute('$fixture->value = 2; return $fixture->increment();'));
    }

    public function testModernCustomValidatorFalseDeniesAccess(): void
    {
        $sandbox = new PHPSandbox([], [], ['fixture' => new Php85PolicyFixture()]);
        $sandbox->setMethodValidator(static fn(string $member): bool => false);

        $this->expectException(Error::class);
        $sandbox->execute('return $fixture->increment();');
    }

    public function testModernPoliciesHaveMatchingQueryAndRemovalApis(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'increment');
        self::assertTrue($sandbox->isWhitelistedMethod(Php85PolicyFixture::class, 'increment'));
        self::assertSame(1, $sandbox->hasWhitelistedMethods());

        $sandbox->dewhitelistMethod(Php85PolicyFixture::class, 'increment');
        self::assertFalse($sandbox->isWhitelistedMethod(Php85PolicyFixture::class, 'increment'));
    }

    public function testResetOptionsRestoresNumericPolicyLimits(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->setOptions(['time_limit' => 4, 'max_include_depth' => 2, 'max_include_bytes' => 20]);
        $sandbox->resetOptions();

        self::assertSame(0, $sandbox->time_limit);
        self::assertSame(32, $sandbox->max_include_depth);
        self::assertSame(1048576, $sandbox->max_include_bytes);
    }

    public function testDynamicMembersRemainSubjectToCanonicalMemberPolicy(): void
    {
        $fixture = new Php85PolicyFixture();
        $sandbox = new PHPSandbox([], [], ['fixture' => $fixture]);
        $sandbox->allow_dynamic_methods = true;
        $sandbox->allow_dynamic_properties = true;
        $sandbox->allow_dynamic_class_constants = true;
        $sandbox->whitelistClass(Php85PolicyFixture::class);
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'increment');
        $sandbox->whitelistMethod(Php85PolicyFixture::class, 'label');
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'value');
        $sandbox->whitelistProperty(Php85PolicyFixture::class, 'counter');
        $sandbox->whitelistClassConstant(Php85PolicyFixture::class, 'VERSION');

        self::assertSame('8:4:fixture:9', $sandbox->execute(
            '$method = "increment"; $staticMethod = "label"; $property = "value"; $staticProperty = "counter"; $constant = "VERSION";'
            . '$fixture->{$property} = $fixture->{$method}(by: 7);'
            . 'Php85PolicyFixture::${$staticProperty} = 9;'
            . 'return $fixture->{$property} . ":" . Php85PolicyFixture::{$constant} . ":" . Php85PolicyFixture::{$staticMethod}() . ":" . Php85PolicyFixture::${$staticProperty};'
        ));
    }

    public function testPhp81IntersectionAndReadonlySyntax(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_interfaces = true;
        $sandbox->allow_classes = true;
        $sandbox->allow_functions = true;

        self::assertTrue($sandbox->execute(
            'interface PolicyA {} interface PolicyB {} '
            . 'readonly class PolicyReadonly implements PolicyA, PolicyB { public function __construct(public int $id) {} } '
            . 'function policyDnf81(PolicyA&PolicyB $value): PolicyA&PolicyB { return $value; } '
            . '$unpacked = [...["octal" => 0o14]]; return $unpacked["octal"] === 12;'
        ));
    }

    public function testPhp82DnfAndTraitConstantsSyntax(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_interfaces = true;
        $sandbox->allow_traits = true;
        $sandbox->allow_functions = true;

        self::assertTrue($sandbox->execute(
            'interface PolicyDnfA {} interface PolicyDnfB {} '
            . 'trait PolicyConstantTrait { public const VALUE = 2; } '
            . 'function policyDnf82((PolicyDnfA&PolicyDnfB)|null $value): (PolicyDnfA&PolicyDnfB)|null { return $value; } return true;'
        ));
    }

    public function testPhp83TypedConstantsDynamicConstantsAndStaticInitializers(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_classes = true;
        $sandbox->allow_functions = true;
        $sandbox->allow_dynamic_class_constants = true;
        $sandbox->allow_traits = true;

        self::assertSame(6, $sandbox->execute(
            'class PolicyTypedConstant { public const int VALUE = 3; } '
            . 'trait PolicyFinalTrait { public function value(): int { return 3; } } '
            . 'class PolicyFinalTraitConsumer { use PolicyFinalTrait { value as final; } } '
            . 'function policyInitialValue(): int { return 3; } '
            . 'function policyStaticInitializer(): int { static $value = policyInitialValue(); return $value; } '
            . '$name = "VALUE"; return PolicyTypedConstant::{$name} + policyStaticInitializer();'
        ));
    }

    public function testPhp84AsymmetricVisibilityAndNewChaining(): void
    {
        if (PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Asymmetric visibility executes on PHP 8.4+.');
        }
        $sandbox = new PHPSandbox();
        $sandbox->allow_classes = true;

        self::assertSame(4, $sandbox->execute(
            'class PolicyAsymmetric { public private(set) int $value = 4; public function value(): int { return $this->value; } } '
            . 'return new PolicyAsymmetric()->value();'
        ));
    }

    public function testPhp85ConstantClosuresAttributesAndVoidCast(): void
    {
        if (PHP_VERSION_ID < 80500) {
            self::markTestSkipped('PHP 8.5 constant-expression syntax executes on PHP 8.5+.');
        }
        $sandbox = new PHPSandbox();
        $sandbox->allow_classes = true;
        $sandbox->allow_constants = true;
        $sandbox->allow_attributes = true;
        $sandbox->allow_closures = true;
        $sandbox->allow_casting = true;
        $sandbox->whitelistAttribute('PolicyConstantMarker');

        $result = $sandbox->execute(
            'class PolicyConstantMarker {} '
            . 'class PolicyFinalPromoted { public function __construct(public final int $id) {} } '
            . 'class PolicyStaticAsymmetric { public private(set) static int $value = 1; } '
            . 'const POLICY_CAST_CONSTANT = (int) 0.3; '
            . ' #[PolicyConstantMarker] const POLICY_CONSTANT_CLOSURE = static function(): int { return 5 + POLICY_CAST_CONSTANT; }; '
            . '(void) 1; return POLICY_CONSTANT_CLOSURE;'
        );
        self::assertInstanceOf(Closure::class, $result);
        self::assertSame(5, $result());
    }

    public function testExecutionRestoresOutputBufferAfterException(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->capture_output = true;
        $sandbox->whitelistType(Exception::class);
        $before = ob_get_level();

        try {
            $sandbox->execute('echo "partial"; throw new Exception("failure");');
            self::fail('Expected exception');
        } catch (Exception) {
            self::assertSame($before, ob_get_level());
        }
    }

    public function testLastErrorIsInitiallyNull(): void
    {
        self::assertNull((new PHPSandbox())->getLastError());
    }

    public function testInvalidDefinedVariableNamesCannotInjectGeneratedCode(): void
    {
        $sandbox = new PHPSandbox();
        $this->expectException(Error::class);
        $sandbox->defineVar('value; throw new Exception; $other', 1);
    }

    public function testJsonStringTemplatesDecodeAsAssociativeData(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->import('{"schema_version":4,"options":{"capture_output":true}}');
        self::assertTrue($sandbox->capture_output);
    }

    public function testExecutableTemplateSectionsRequireTrustedImportFlag(): void
    {
        $sandbox = new PHPSandbox();
        $this->expectException(Error::class);
        $sandbox->import([
            'schema_version' => 4,
            'definitions' => [
                'func' => ['danger' => ['fullcode' => 'function () {}', 'pass' => false]],
            ],
        ]);
    }

    public function testIncludeRootsRejectPathsOutsideConfiguredRoot(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_includes = true;
        $sandbox->allowIncludePath(__DIR__ . '/samples');
        $this->expectException(Error::class);
        $sandbox->execute('include ' . var_export(__FILE__, true) . ';');
    }

    public function testFileIncludesRequireAnExplicitRoot(): void
    {
        $sandbox = new PHPSandbox();
        $sandbox->allow_includes = true;

        $this->expectException(Error::class);
        $sandbox->execute('include ' . var_export(__FILE__, true) . ';');
    }

    public function testEveryConcreteParserNodeIsInTheFailClosedInventory(): void
    {
        $supported = AstPolicyVisitor::supportedNodes();
        $root = dirname(__DIR__) . '/vendor/nikic/php-parser/lib/PhpParser/Node';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relative = substr($file->getPathname(), strlen(dirname($root)) + 1, -4);
            $class = 'PhpParser\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
            if (!class_exists($class) || !(new ReflectionClass($class))->isSubclassOf(PhpParser\Node::class)) {
                continue;
            }
            self::assertArrayHasKey($class, $supported, "Unclassified parser node: $class");
        }
    }
}
