##A full-scale PHP 5.3+ sandbox class that utilizes [PHP-Parser](https://github.com/nikic/PHP-Parser) to prevent sandboxed code from running unsafe code.

It also utilizes [FunctionParser](https://github.com/jeremeamia/FunctionParser) to disassemble callables passed to the sandbox, so that PHP callables can also be run in sandboxes without first converting them into strings.

##Features:

- Finegrained whitelisting and blacklisting, with sensible defaults configured.
- Can redefine internal PHP functions to make them more secure for sandbox usage.
- Can redefine magic constants to expose your own values to sandboxed code.
- Can selectively allow and disallow function creation, class declarations, constant definitions, and even keywords!

##Example usage:
    $sandbox = PHPSandbox\PHPSandbox::create();
    $sandbox->execute(function(){
        eval('evil code'); //this will generate an exception and fail
    });