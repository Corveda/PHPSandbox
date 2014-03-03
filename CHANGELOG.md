#CHANGELOG

##03/03/2014
- Implemented function checking mechanism for callbacks, which should now obey function whitelists and blacklists
- Replaced passed sandbox instance variable with static method call that allows the sandbox to be accessed for validation within sandboxed functions and closures
- Added overwritten var_dump, print_r and var_export methods that automatically hide the sandbox instance and return the string value of sandboxed strings

##01/30/2014
- Implemented overhaul of error handling system. Can now specify a custom error handler to intercept thrown exceptions.

##12/09/2013
- Added ability to define custom validation functions for fine-grained control of the sandbox elements
- Added capture_output flag to indicate whether to capture and return output of sandbox execution via output buffering
- Added restore_error_level flag to indicate whether the to restore error level after setting custom error level in sandbox

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