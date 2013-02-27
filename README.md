##A full-scale PHP 5.3.2+ sandbox class that utilizes [PHP-Parser](https://github.com/nikic/PHP-Parser) to prevent sandboxed code from running unsafe code.

It also utilizes [FunctionParser](https://github.com/jeremeamia/FunctionParser) to disassemble callables passed to the sandbox, so that PHP callables can also be run in sandboxes without first converting them into strings.

##Features:

- Finegrained whitelisting and blacklisting, with sensible defaults configured.
- Can redefine internal PHP and other functions to make them more secure for sandbox usage.
- Can redefine superglobals and magic constants to expose your own values to sandboxed code.
- Can overwrite the get_defined_* and get_declared_* functions to show only allowed functions, classes, etc. to the sandboxed code.
- Can selectively allow and disallow function creation, class declarations, constant definitions, keywords, and much more.
- Can prepend and append trusted code to setup and tear the sandbox, and automatically whitelist the classes, functions, variables, etc. they define for the sandbox.
- Can retrieve the generated sandbox code for later usage.
- Can pass arguments directly to the sandboxed code through the execute method to reveal chosen outside variables to the sandbox.

##Example usage:

    function test($string){
        return 'Hello ' . $string;
    }

    $sandbox = new PHPSandbox\PHPSandbox;
    $sandbox->whitelist_func('test');
    $result = $sandbox->execute(function(){
        return test('world');
    });

    var_dump($result);  //Hello world