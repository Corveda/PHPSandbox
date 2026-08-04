# PHPSandbox manual

PHPSandbox is a configurable PHP language-policy layer. It accepts source text or a reflectable callable, parses it, validates every known AST node, rewrites operations that need runtime information, prints a generated closure, and evaluates that closure in the host PHP process.

It is useful for plugin systems, constrained expressions, educational tools, and application-specific scripting where the code is already within an established trust relationship. It is not a replacement for process isolation when the code is hostile. Start with the [security model](security.md).

## Execution pipeline

1. `prepare()` extracts callable source when needed and parses PHP with PHP-Parser.
2. `AstPolicyVisitor` rejects any parser node kind that has not received an explicit coverage decision.
3. `ValidatorVisitor` applies declaration, keyword, operator, primitive, type, symbol, attribute, and member policy. Sensitive member and include operations are rewritten to runtime guards.
4. Trusted prepend/append code may be merged only through an explicit trusted-code API or import flag.
5. The AST is printed into an internal closure. Host variables are closure parameters, preserving identity without serializing values into PHP source.
6. `execute()` installs configured error handling, invokes the closure, and restores handlers, error reporting, buffers, and nested-execution state in `finally` blocks.

## Minimal policy

```php
use PHPSandbox\PHPSandbox;

$sandbox = new PHPSandbox();
$sandbox->whitelistFunc('strlen');
$length = $sandbox->execute('return strlen("policy");');
```

An empty allowlist does not mean that every external symbol is allowed. Symbol families with validation enabled require a definition, allowlist entry, blacklist decision, or custom validator according to that family's existing rules. Modern external object members are deliberately denied until configured.

## Inputs and results

`defineVar($name, $value)` exposes a host value as a generated-closure parameter. Names must be legal PHP identifiers; `$this` and internal names are rejected. Objects are passed by identity. `execute()` returns the value returned by sandboxed code. Set `capture_output` to collect output as the result where required by the existing API.

## Modern PHP syntax

The policy recognizes PHP 8.1–8.5 syntax. Syntax availability still depends on the PHP runtime that parses and executes the code. For example, a property hook cannot run on PHP 8.3, even though the library's PHP-Parser dependency understands the node. See [PHP version coverage](php-versions.md).

## Next references

- [Configuration](configuration.md)
- [Public API](api.md)
- [Template schema](templates.md)
- [Toolkit](toolkit.md)
- [Migration from v3](migration-v4.md)

