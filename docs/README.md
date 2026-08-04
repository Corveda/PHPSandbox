# Historical generated API pages

The HTML files in this directory were generated for the pre-v4 implementation and are retained only as historical artifacts. They are not the current API contract.

The maintained v4 API consists of:

- [`documentation/api.md`](../documentation/api.md), the semantic guide;
- [`documentation/api-reference.md`](../documentation/api-reference.md), the generated signature index;
- source docblocks under `src/`.

Regenerate the signature index with `composer docs:api`. CI verifies that the committed result matches the source.
