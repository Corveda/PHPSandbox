#CHANGELOG

##07/24/2014
- Fixed bug with prepare_vars()

##06/19/2014
- Added flags to enable/disable validation checks for every type (for example, this will help in situations where someone wants to enable all functions but not blacklist a fake function name to remove the function whitelist.) Disabling a validation check also ignores any custom validation check for that type!
- Fixed issue with toolkit giving incorrect error and exception messages
- Added ability for sandbox to overwrite static type hints that are redefined to other class names (NOTE: All statically referenced class/interface/trait names are treated as classes by the parser and the sandbox, therefore they must be redefined via define_class())
- Updated documentation

##05/06/2014
- Corrected defined alias case-sensitivity issue (see: issue #10)

##03/19/2014
- API CHANGE: set_error_handler() and its related methods have been changed to set_validation_error_handler(), etc. because that more closely explains their purpose of handling validation errors in the sandbox
- set_error_handler() its related methods now represent a new error handling mechanism that replicates PHP's set_error_handler() built-in functionality for the sandbox
- set_exception_handler() and its related methods represent a new exception handling mechanism that replicates PHP's set_exception_handler() built-in functionality for the sandbox
- A new convert_errors option flag has been added to the sandbox, which will automatically convert PHP errors in the sandbox to exceptions and send them to the sandbox's exception handler if it is set
- Revised SandboxedString insertion to more intelligently provide sandboxing of callbacks
- Changed method visibility to public for error() and exception() to resolve PHP 5.3's failure to use them in the proper context
- Addressed issue where functions expecting float or int values passed as strings would throw errors
- You can now specify an unlimited number of parameters for all whitelist_*, blacklist_*, etc. methods etc for _superglobal. This works the same as passing an array of parameters.

##03/10/2014
- Addressed potential vulnerabilities related to SandboxedStrings where sandboxed code could manipulate the strings in a way that could defeat their protection
- Solved errors from casting sandboxed strings to int
- Overwrote some internal PHP functions to further mask SandboxedStrings from the sandboxed code and prevent type-checking errors
- Added more tests related to SandboxedStrings
- Made some minor tweaks to PHPSandbox Toolkit

##03/05/2014
- Corrected an issue where the sandbox variable could be accessed from within the sandbox in PHP 5.4+

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