# PHPSandbox

PHPSandbox is an in-process PHP policy-enforcement toolkit for PHP 8.1–8.5. It parses code with `nikic/php-parser`, validates the resulting AST against an explicit policy, rewrites sensitive operations through runtime guards, and executes the generated code in the current PHP process.

> [!IMPORTANT]
> PHPSandbox is not a security boundary. It does not provide process, operating-system, container, filesystem, network, CPU, or memory isolation. A parser, validator, rewrite, runtime, or configuration defect can expose all privileges of the host PHP process. Use it to enforce application policy on code you already have reason to trust; use a separate least-privileged process/container/VM when executing hostile code.

## Current support

- PHP 8.1 through PHP 8.5
- PHP-Parser 5.7 and later compatible 5.x releases
- Attributes, enums, named arguments, first-class callables, arrow functions, match expressions, nullsafe access, union/intersection/DNF types, readonly syntax, property hooks, asymmetric visibility, clone-with, and the PHP 8.5 pipe operator
- Granular policy for functions, types, enums, attributes, methods, properties, and class constants
- Fail-closed rejection of AST node kinds that the installed parser introduces without an explicit coverage decision
- Restricted sandboxed includes with canonical roots, wrapper allowlists, depth limits, and byte limits

The [manual](documentation/index.md), [security model](documentation/security.md), [configuration reference](documentation/configuration.md), [API guide](documentation/api.md), [toolkit guide](documentation/toolkit.md), and [v4 migration guide](documentation/migration-v4.md) are maintained in this repository.

## Installation

```bash
composer require corveda/php-sandbox:^4.0
```

PHP must have the JSON and tokenizer functionality normally included with PHP distributions.

## Basic use

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PHPSandbox\PHPSandbox;

function greet(string $name): string {
    return "Hello, {$name}";
}

$sandbox = new PHPSandbox();
$sandbox->whitelistFunc('greet');

$result = $sandbox->execute('return greet(name: "world");');
```

External object members are denied by default. Grant only the members the policy needs:

```php
$sandbox
    ->defineVar('service', $service)
    ->whitelistType($service::class)
    ->whitelistMethod($service::class, 'lookup')
    ->whitelistProperty($service::class, 'status')
    ->whitelistClassConstant($service::class, 'VERSION');

$result = $sandbox->execute('return $service->lookup(id: 42);');
```

Include access requires both the feature and a canonical root:

```php
$sandbox
    ->setOption('allow_includes', true)
    ->allowIncludePath(__DIR__ . '/sandbox-modules');
```

## Development

```bash
composer install
composer test
composer audit
```

CI resolves the dependency set and runs the suite on every supported PHP version. The PHP 8.5 job also exercises syntax that older runtimes cannot parse.

## License

BSD-3-Clause. See [LICENSE](LICENSE).
