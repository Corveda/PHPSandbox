# Migrating from v3 to v4

## Runtime and dependency requirements

v4 requires PHP 8.1 or newer and PHP-Parser 5.7 or newer. The abandoned FunctionParser dependency has been removed; reflectable callable extraction now uses PHP reflection plus the same PHP-Parser AST model as string input.

## Intentional policy changes

- External methods, properties, and class constants are denied until configured. Add the matching member allowlist entries; allowing a class/type alone is not a member grant.
- Dynamic member names default to disabled. Enable the narrow dynamic option only with a suitable custom validator.
- Unknown parser nodes fail closed.
- Includes require a canonical root configured with `allowIncludePath()` and are subject to wrapper, depth, and byte limits.
- Template JSON is strict. Executable definitions and prepend/append imports require `IMPORT_TRUSTED_CODE`.
- Invalid host variable names are rejected. Values are closure parameters rather than serialized into generated source.
- Nested execution restores output buffers, handlers, error reporting, executing-file state, and generated-code state after completion or failure.

## Compatibility

Existing v3 method names remain available. Schema v3 templates are accepted, but should be resaved as schema v4 and reviewed for member grants. APIs are retained rather than silently renamed; future removals should first receive a documented deprecation cycle.

## Recommended migration sequence

1. Move CI to PHP 8.1+ and resolve with the new Composer constraints.
2. Run tests with the default member policy and add the smallest necessary method/property/constant grants.
3. Configure include roots explicitly or keep includes disabled.
4. Mark only host-controlled template sections with `IMPORT_TRUSTED_CODE`.
5. Run the suite on PHP 8.1 and 8.5 to cover both the minimum runtime and newest syntax path.

