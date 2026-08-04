# Configuration reference

Options may be public properties, supplied through `setOption($name, $value)`, or applied in bulk with `setOptions()`. Prefer setters in application code so invalid option names are not mistaken for configured behavior.

## Validation families

`validate_functions`, `validate_variables`, `validate_globals`, `validate_superglobals`, `validate_constants`, `validate_magic_constants`, `validate_namespaces`, `validate_aliases`, `validate_classes`, `validate_interfaces`, `validate_traits`, `validate_keywords`, `validate_operators`, `validate_primitives`, and `validate_types` retain their established meaning.

The modern families are:

| Option | Configuration methods | Canonical template value |
| --- | --- | --- |
| `validate_enums` | `whitelistEnum`, `blacklistEnum`, `setEnumValidator` | `Vendor\\State` |
| `validate_attributes` | `whitelistAttribute`, `blacklistAttribute`, `setAttributeValidator` | `Vendor\\Rule` |
| `validate_methods` | `whitelistMethod`, `blacklistMethod`, `setMethodValidator` | `Vendor\\Service::lookup` |
| `validate_properties` | `whitelistProperty`, `blacklistProperty`, `setPropertyValidator` | `Vendor\\Value::$status` |
| `validate_class_constants` | `whitelistClassConstant`, `blacklistClassConstant`, `setClassConstantValidator` | `Vendor\\Value::VERSION` |

Member policy resolves inherited instance methods/properties to their declaring class where reflection can do so. Method and class names are case-insensitive; property and constant names retain PHP's relevant case behavior.

## Language switches

New declaration/syntax switches are `allow_enums`, `allow_attributes`, `allow_property_hooks`, and `allow_named_arguments`. Dynamic access has separate switches: `allow_dynamic_methods`, `allow_dynamic_properties`, and `allow_dynamic_class_constants`. They default to false except named arguments.

Existing switches continue to govern functions, closures, variables, static variables, objects, constants, globals, namespaces, aliases, classes, interfaces, traits, generators, escaping, casts, error suppression, references, backticks, halting, and string rewriting.

When `allow_constants` and the constant symbol policy permit it, source-level global `const` declarations are supported and hoisted to file scope. This differs from a local closure variable: the declaration persists in the host process.

`auto_whitelist_enums` and `auto_whitelist_members` extend trusted-code discovery. They never make arbitrary external members safe.

Enabling a dynamic member switch permits the name to be computed; it does not bypass member policy. The resolved runtime `Class::member` value must still pass the matching allowlist/blacklist/custom validator.

## Includes

Includes are disabled by default. A useful policy needs all of:

```php
$sandbox->setOption('allow_includes', true);
$sandbox->setOption('sandbox_includes', true);
$sandbox->allowIncludePath('/absolute/canonical/module/root');
$sandbox->setOption('max_include_depth', 16);
$sandbox->setOption('max_include_bytes', 262144);
```

Only `file` is an allowed stream wrapper by default. Use `allowStreamWrapper()` deliberately; allowing a network or extension-provided wrapper expands host capability. Paths are resolved with `realpath()` and must remain below an allowed root.

## Unknown syntax

Unknown PHP-Parser AST nodes are rejected with `Error::UNKNOWN_NODE_ERROR`. This behavior is intentionally not configurable: parser upgrades must receive an explicit implementation and test decision.
