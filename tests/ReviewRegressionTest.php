<?php

namespace PHPSandboxReviewFixtures {
    function namespacedCallable(): string
    {
        return 'namespaced callable';
    }

    final class RuntimeTarget
    {
        public const LABEL = 'constant';
        public static string $property = 'property';
        public string $receiverValue = 'bound receiver';

        public function receiverCallable(): string
        {
            return $this->receiverValue;
        }

        public function mutate(string &$value): void
        {
            $value .= ':instance';
        }

        public static function mutateStatic(string &$value): void
        {
            $value .= ':static';
        }

        public static function run(): string
        {
            return 'method';
        }
    }
}

namespace {
    use PHPSandbox\PHPSandbox;
    use PHPSandboxReviewFixtures\RuntimeTarget;
    use PHPUnit\Framework\TestCase;

    final class ReviewRegressionTest extends TestCase
    {
        public function testInstanceMethodCallableRetainsItsReceiver(): void
        {
            $target = new RuntimeTarget();
            $sandbox = new PHPSandbox();
            $sandbox->whitelistProperty(RuntimeTarget::class, 'receiverValue');

            self::assertSame('bound receiver', $sandbox->execute([$target, 'receiverCallable']));
        }

        public function testNamespacedFunctionCallableIsLocatedByItsQualifiedName(): void
        {
            $sandbox = new PHPSandbox();

            self::assertSame(
                'namespaced callable',
                $sandbox->execute('PHPSandboxReviewFixtures\\namespacedCallable')
            );
        }

        public function testAliasesAreResolvedForEveryGuardedStaticAccess(): void
        {
            $sandbox = new PHPSandbox();
            $sandbox->allow_aliases = true;
            $sandbox->allow_dynamic_class_constants = true;
            $sandbox->whitelistAlias(RuntimeTarget::class);
            $sandbox->whitelistMethod(RuntimeTarget::class, 'run');
            $sandbox->whitelistProperty(RuntimeTarget::class, 'property');
            $sandbox->whitelistClassConstant(RuntimeTarget::class, 'LABEL');

            self::assertSame(
                ['method', 'property', 'constant'],
                $sandbox->execute(
                    'use PHPSandboxReviewFixtures\\RuntimeTarget as Target;'
                    . '$constant = "LABEL";'
                    . 'return [Target::run(), Target::$property, Target::{$constant}];'
                )
            );
        }

        public function testGuardedMethodCallsPreserveByReferenceArguments(): void
        {
            $target = new RuntimeTarget();
            $sandbox = new PHPSandbox([], [], ['target' => $target]);
            $sandbox->whitelistMethod(RuntimeTarget::class, 'mutate');
            $sandbox->whitelistMethod(RuntimeTarget::class, 'mutateStatic');
            $sandbox->whitelistClass(RuntimeTarget::class);

            self::assertSame(
                'start:instance:static',
                $sandbox->execute(
                    '$value = "start";'
                    . '$target->mutate($value);'
                    . 'PHPSandboxReviewFixtures\\RuntimeTarget::mutateStatic($value);'
                    . 'return $value;'
                )
            );
        }
    }
}
