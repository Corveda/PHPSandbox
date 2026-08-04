# PHP 8.1–8.5 coverage

The library and test suite support PHP 8.1, 8.2, 8.3, 8.4, and 8.5. PHP-Parser 5 represents syntax independently of the executing engine; the input must nevertheless be valid for the runtime executing it.

| Version / language area | Policy behavior |
| --- | --- |
| PHP 8.0 baseline: attributes, named arguments, match, nullsafe access, union types, promotion | Attributes and named arguments have dedicated switches; match/`?->` are explicit keyword/operator decisions; all type leaves and promoted properties are validated. |
| PHP 8.1: enums, first-class callables, intersection/`never`, readonly properties, initializer `new`, unpacking | Enums have declaration/symbol/member policy; callable targets are checked; types recurse; remaining expression syntax uses existing object/operator/primitive policy. Fibers are ordinary external classes and members. |
| PHP 8.2: DNF and standalone literal types, readonly classes, trait constants | Type trees recurse through intersections/unions; class/trait/constant and member policy applies normally. |
| PHP 8.3: typed constants, dynamic constant fetch, expression static initializers, final trait aliases, readonly amendments | Constant types are validated; dynamic constant names require their switch and still pass runtime member policy; static initializer calls are validated. |
| PHP 8.4: property hooks, asymmetric visibility, unparenthesized `new` chaining | Hooks have a dedicated switch; all property reads/writes remain guarded; class/member/object policy covers the other forms. Lazy objects are Reflection API capabilities and require the relevant external class/method grants. |
| PHP 8.5: pipe, clone-with, constant closures/callables/casts, constant attributes, static asymmetric visibility, final promotion, `(void)` | Pipe is operator policy; every clone-with key passes property policy; constant expressions remain compile-time expressions after validation; attribute/cast/class/property policy applies to the other forms. New core attributes are ordinary attribute-policy entries. |

Global `const` declarations are hoisted from the generated execution closure to PHP file scope after validation because PHP does not permit them inside a closure. Enabling them therefore creates process-wide PHP engine state; repeated names may conflict. This is another reason PHPSandbox is a policy layer rather than an isolation boundary.

The language inventory follows the official PHP migration guides for [8.1](https://www.php.net/manual/en/migration81.new-features.php), [8.2](https://www.php.net/manual/en/migration82.new-features.php), [8.3](https://www.php.net/manual/en/migration83.new-features.php), [8.4](https://www.php.net/manual/en/migration84.new-features.php), and [8.5](https://www.php.net/manual/en/migration85.new-features.php). Extension additions are exposed only when the host installs them and are governed as ordinary functions, constants, classes, and members.

The test `Php85PolicyTest::testEveryParserNodeHasAnExplicitCoverageDecision` compares the installed parser's node files with `AstPolicyVisitor`. A PHP-Parser upgrade that adds a node therefore fails CI until maintainers explicitly add and test its policy behavior.
