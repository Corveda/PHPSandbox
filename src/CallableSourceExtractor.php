<?php
namespace PHPSandbox;

use Closure;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use RuntimeException;

/** Extracts userland callable bodies using Reflection and PHP-Parser. */
final class CallableSourceExtractor
{
    /** @return array{body: string, reflection: ReflectionFunctionAbstract, receiver: ?object, scope: ?string} */
    public function extract(callable $callable): array
    {
        $reflection = $this->reflect($callable);
        $file = $reflection->getFileName();
        if ($file === false || !is_file($file)) {
            throw new RuntimeException('Internal or source-less callables cannot be executed as sandbox source. Define them as sandbox functions instead.');
        }

        $source = file_get_contents($file);
        if ($source === false) {
            throw new RuntimeException("Unable to read callable source file: $file");
        }

        $ast = (new ParserFactory())->createForHostVersion()->parse($source);
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $ast = $traverser->traverse($ast);
        $finder = new NodeFinder();
        $candidates = $finder->find($ast, function (Node $node) use ($reflection): bool {
            if (!$node instanceof Node\FunctionLike) {
                return false;
            }
            if ($node->getStartLine() !== $reflection->getStartLine() || $node->getEndLine() !== $reflection->getEndLine()) {
                return false;
            }
            if ($reflection instanceof ReflectionMethod) {
                return $node instanceof Node\Stmt\ClassMethod
                    && strcasecmp($node->name->toString(), $reflection->getName()) === 0;
            }
            if ($reflection->isClosure()) {
                return $node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction;
            }
            if (!$node instanceof Node\Stmt\Function_) {
                return false;
            }
            $declaredName = isset($node->namespacedName)
                ? $node->namespacedName->toString()
                : $node->name->toString();
            return strcasecmp($declaredName, ltrim($reflection->getName(), '\\')) === 0;
        });

        if (!$candidates) {
            throw new RuntimeException('Unable to locate callable source; pass PHP source text instead.');
        }

        // Reflection exposes lines but not columns. NodeFinder returns the outer
        // function-like first, which is the callable represented when nested
        // closures share a single source line.
        $node = $candidates[0];
        $printer = new Standard();
        $body = $node instanceof Node\Expr\ArrowFunction
            ? 'return ' . $printer->prettyPrintExpr($node->expr) . ';'
            : $printer->prettyPrint($node->getStmts() ?? []);

        [$receiver, $scope] = $this->binding($callable, $reflection);
        return [
            'body' => $body,
            'reflection' => $reflection,
            'receiver' => $receiver,
            'scope' => $scope,
        ];
    }

    private function reflect(callable $callable): ReflectionFunctionAbstract
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return new ReflectionFunction($callable);
        }
        if (is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        }
        return new ReflectionMethod($callable, '__invoke');
    }

    /** @return array{?object, ?string} */
    private function binding(callable $callable, ReflectionFunctionAbstract $reflection): array
    {
        if ($reflection instanceof ReflectionMethod) {
            $receiver = is_array($callable) && is_object($callable[0])
                ? $callable[0]
                : (!is_array($callable) && is_object($callable) ? $callable : null);
            return [$receiver, $reflection->getDeclaringClass()->getName()];
        }
        if ($reflection instanceof ReflectionFunction && $reflection->isClosure()) {
            return [
                $reflection->getClosureThis(),
                $reflection->getClosureScopeClass()?->getName(),
            ];
        }
        return [null, null];
    }
}
