# Public API guide

This guide groups the stable application-facing API. Source docblocks remain the detailed signature reference.

The complete [generated signature index](api-reference.md) is rebuilt with `composer docs:api`; CI rejects drift.

## Lifecycle

- `PHPSandbox::create()` creates an instance; `PHPSandbox::getSandbox($name)` resolves a live named instance without keeping it alive indefinitely.
- `prepare($code, $skipValidation = false)` returns generated PHP source.
- `execute($code = null, $skipValidation = false, $executingFile = null)` prepares when necessary and returns the sandbox result.
- `getParsedAST()`, `getPreparedAST()`, `getPreparedCode()`, and `getGeneratedCode()` expose pipeline artifacts for diagnostics.
- `getPreparedTime()`, `getExecutionTime()`, `getTime()`, and `getMemoryUsage()` expose measurements, not isolation limits.

## Policy and options

- `setOption()`, `setOptions()`, and `getOption()` configure flags and numeric include/time limits.
- `whitelist($family, $names)` and `blacklist($family, $names)` are generic compatibility entry points.
- Family helpers include `whitelistFunc`, `whitelistType`, `whitelistEnum`, `whitelistAttribute`, `whitelistMethod`, `whitelistProperty`, and `whitelistClassConstant`, with matching `blacklist*`, `check*`, custom-validator, query, and clear APIs where supplied.
- Member helper signatures are `(class-string|object $class, string $member)`; template form uses one canonical `Class::member` string.

## Host definitions

`defineVar`, `defineFunc`, `defineSuperglobal`, `defineConst`, `defineMagicConst`, `defineNamespace`, `defineAlias`, `defineClass`, `defineInterface`, and `defineTrait` expose controlled host definitions. Executable source supplied through definition APIs is trusted host configuration. Corresponding `getDefined*`, `isDefined*`, and `undefine*` methods support inspection and removal.

## Trusted code

`prepend()`, `append()`, `clearTrustedCode()`, and auto-whitelist options manage host-controlled setup/teardown source. Never construct these values from untrusted input. Template imports require `IMPORT_TRUSTED_CODE` before executable definition or prepend/append content is accepted.

## Includes

- `allowIncludePath($absolutePath)` adds a canonical root.
- `clearAllowedIncludePaths()` removes all roots.
- `allowStreamWrapper($scheme)` and `disallowStreamWrapper($scheme)` control schemes.
- `max_include_depth` and `max_include_bytes` are set through `setOption()`.

## Handlers and diagnostics

`setErrorHandler`, `setExceptionHandler`, and `setValidationErrorHandler` register host callbacks. `getLastError`, `getLastException`, and `getLastValidationError` return the most recent captured value or `null`. Handlers execute with host privilege.

## Internal runtime helpers

Public methods prefixed with `_` are generated-code runtime hooks. They must remain callable for generated closures but are not application extension points; compatibility is driven by generated code rather than the normal public API.
