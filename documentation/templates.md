# Template schema v4

Exports and toolkit saves use `schema_version: 4`. Schema 3 remains importable for migration. Unknown versions are rejected.

```json
{
  "schema_version": 4,
  "code": "return $service->lookup(id: 42);",
  "options": {"allow_named_arguments": true},
  "whitelist": {
    "type": ["Vendor\\Service"],
    "method": ["Vendor\\Service::lookup"],
    "property": ["Vendor\\Service::$status"],
    "class_constant": ["Vendor\\Service::VERSION"],
    "attribute": ["Vendor\\Rule"],
    "enum": ["Vendor\\State"]
  },
  "blacklist": {},
  "definitions": {}
}
```

JSON decoding uses `JSON_THROW_ON_ERROR`; malformed data does not degrade to a partial configuration. Executable function definitions and prepend/append code are ignored or rejected unless the caller explicitly includes `PHPSandbox::IMPORT_TRUSTED_CODE` in the import flags.

```php
$flags = PHPSandbox::IMPORT_OPTIONS
    | PHPSandbox::IMPORT_WHITELIST
    | PHPSandbox::IMPORT_BLACKLIST;
$sandbox->import($json, $flags);
```

Add `IMPORT_DEFINITIONS` for data definitions. Add `IMPORT_TRUSTED_CODE` only when the template is controlled as host source code. `IMPORT_CODE` controls whether the template's main code becomes the prepared input.

