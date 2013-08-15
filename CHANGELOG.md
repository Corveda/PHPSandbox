#CHANGELOG



##08/15/2013
- Added support for PHP 5.5. and new allow_generators flag for configuring the sandbox to enable or disable PHP 5.5 generators

##03/04/2013
- Added the ability to redefine classes, interfaces and traits

##03/03/2013

- Major updates to dynamic demonstrations system, not know as the PHPSandbox Toolkit. Allows for almost unlimited configuration of PHPSandbox environments
- PHPSandbox class now supports importing JSON template configurations, serialization, and defining callables for superglobals and magic constants
- API documentation is complete, PHPUnit testing is initialized. Toolkit has its own help system, utilizing the fully written INSTRUCTIONS.html

##03/01/2013

- Early version of dynamic demonstration system added to /demos
- Supports configuring and testing sandboxes from local PHP server without the necessity of coding specific demos
- Can save personal templates of sandboxed code and configurations for future reference
- The /demos/templates folder will gradually have a full suite of demonstration files explaining the various features and configuration options available in PHPSandbox
- Initial commit of PHPUnit tests and added PHPUnit as a "require-dev" in composer.json