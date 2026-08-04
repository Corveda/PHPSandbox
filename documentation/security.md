# Security model

## What the library enforces

PHPSandbox enforces policy over the PHP source and AST that it receives. It can reject names and syntax, guard object/static member access, replace selected operations, limit which include paths and stream wrappers it will read, and restore PHP runtime state after execution.

The default policy denies external methods, properties, and class constants. Dynamic member names are separately disabled because static validation cannot determine their target. Unknown AST node kinds fail closed.

## What it does not isolate

Sandboxed code executes with the identity and privileges of the current PHP process. PHPSandbox does not isolate:

- the operating system, process, user, environment, filesystem, or network;
- CPU time or memory with a hard external limit;
- native extensions, engine defects, parser/pretty-printer defects, or application objects that expose unsafe capabilities;
- declarations and other PHP engine state from the lifetime of the current process in every possible configuration.

Allowed global `const`, function, class, interface, trait, and enum declarations can create process-wide engine state. In particular, validated global constants are deliberately hoisted to file scope so PHP 8.5 constant attributes and constant-expression closures remain valid.

`time_limit` uses PHP's in-process execution limit and is not a dependable hostile-code resource boundary. Include path restrictions are policy checks, not filesystem isolation.

## Trust boundaries

Treat these inputs as trusted host configuration:

- custom validation, error, and exception handlers;
- function/class/interface/trait definitions supplied as executable code;
- prepend, append, and toolkit setup code;
- imports using `IMPORT_TRUSTED_CODE`;
- objects exposed with `defineVar()` and every member explicitly granted on them.

Do not enable `skip_validation` for untrusted input. Do not enable dynamic members unless a custom validator provides an equivalent decision using trustworthy runtime information.

## Layered deployment

For hostile code, run the policy layer inside a disposable, least-privileged process or container with a read-only/minimal filesystem, no secrets, restricted network, external CPU/memory/wall-clock limits, and a narrow IPC contract. Those controls are intentionally outside this project's scope.

## Reporting defects

A bypass should be treated as a security-sensitive correctness defect even though this package is not itself a security boundary. Reports should include the PHP version, PHP-Parser version, policy configuration, input source, generated source where safe, and the capability that became reachable.
