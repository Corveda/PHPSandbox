![PHPSandbox](http://www.fieryprophet.com/frontend/files/userfiles/images/phpsandbox-logo.png)

##A full-scale PHP 5.3.2+ sandbox class that utilizes [PHP-Parser](https://github.com/nikic/PHP-Parser) to prevent sandboxed code from running unsafe code.

It also utilizes [FunctionParser](https://github.com/jeremeamia/FunctionParser) to disassemble callables passed to the sandbox, so that PHP callables can also be run in sandboxes without first converting them into strings.

**Manual:** [http://fieryprophet.com/phpsandbox-docs/MANUAL.html](http://fieryprophet.com/phpsandbox-docs/MANUAL.html)

**Online API Documentation:** [http://fieryprophet.com/phpsandbox-docs/](http://fieryprophet.com/phpsandbox-docs/)

[![Build Status](https://travis-ci.org/fieryprophet/php-sandbox.png?branch=master)](https://travis-ci.org/fieryprophet/php-sandbox) [![Latest Stable Version](https://poser.pugx.org/fieryprophet/php-sandbox/v/stable.png)](https://packagist.org/packages/fieryprophet/php-sandbox) [![Total Downloads](https://poser.pugx.org/fieryprophet/php-sandbox/downloads.png)](https://packagist.org/packages/fieryprophet/php-sandbox) [![Latest Unstable Version](https://poser.pugx.org/fieryprophet/php-sandbox/v/unstable.png)](https://packagist.org/packages/fieryprophet/php-sandbox) [![License](https://poser.pugx.org/fieryprophet/php-sandbox/license.png)](https://packagist.org/packages/fieryprophet/php-sandbox)

##Features:

- Finegrained whitelisting and blacklisting, with sensible defaults configured.
- **Includes dynamic demonstration system that allows for local testing of custom sandbox configurations**
- Can redefine internal PHP and other functions to make them more secure for sandbox usage.
- Can redefine superglobals and magic constants to expose your own values to sandboxed code.
- Can overwrite the get_defined_* and get_declared_* functions to show only allowed functions, classes, etc. to the sandboxed code.
- Can selectively allow and disallow function creation, class declarations, constant definitions, keywords, and much more.
- Can prepend and append trusted code to setup and tear down the sandbox, and automatically whitelist the classes, functions, variables, etc. they define for the sandbox.
- Can retrieve the generated sandbox code for later usage.
- Can pass arguments directly to the sandboxed code through the execute method to reveal chosen outside variables to the sandbox.
- Can access the parsed, prepared and generated code ASTs for further analysis or for serialization.
- Can define custom validation functions for fine-grained control of every element of the sandbox.
- Can specify a custom error handler to intercept PHP errors and handle them with custom logic.
- Can specify a custom exception handler to intercept thrown exceptions and handle them with custom logic.
- Can specify a validation error handler to intercept thrown validation errors and handle them with custom logic.
- **Can intercept callbacks and validate them against function whitelists and blacklists, even if they are called as strings**

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

##Custom validation example:

    function custom_func(){
        echo 'I am valid!';
    }

    $sandbox = new PHPSandbox\PHPSandbox;
    //this will mark any function valid that begins with "custom_"
    $sandbox->set_func_validator(function($function_name, PHPSandbox\PHPSandbox $sandbox){
        return (substr($function_name, 0, 7) == 'custom_');  //return true if function is valid, false otherwise
    });
    $sandbox->execute(function(){
        custom_func();
    });
    //echoes "I am valid!"

##Custom validation error handler example:

    $sandbox = new PHPSandbox\PHPSandbox;
    //this will intercept parser validation errors and quietly exit, otherwise it will throw the validation error
    $sandbox->set_validation_error_handler(function(PHPSandbox\Error $error, PHPSandbox\PHPSandbox $sandbox){
        if($error->getCode() == PHPSandbox\Error::PARSER_ERROR){ //PARSER_ERROR == 1
            exit;
        }
        throw $error;
    });
    $sandbox->execute('<?php i am malformed PHP code; ?>');
    //does nothing

##Disable validation example:

    $sandbox = new PHPSandbox\PHPSandbox;
    //this will disable function validation
    $sandbox->set_option('validate_functions', false); // or $sandbox->validate_functions = false;
    $sandbox->execute('<?php echo system("ping google.com"); ?>');
    //Pinging google.com. . .

##Requirements

- PHP 5.3.2+
- [PHP-Parser](https://github.com/nikic/PHP-Parser)
- [FunctionParser](https://github.com/jeremeamia/FunctionParser) (if you wish to use closures)
- PHP should be compiled with *--enable-tokenizer* option (it typically is)

##Installation

To install using [composer](http://getcomposer.org/), simply add the following to your composer.json file in the root of your project:

    {
        "minimum-stability": "dev",
        "require": {
            "fieryprophet/php-sandbox": "1.3.*"
        }
    }

Then run *composer install --dry-run* to check for any potential problems, and *composer install* to install.

##LICENSE

    Copyright (c) 2013-2014 by Elijah Horton (fieryprophet [at] yahoo.com)

    Some rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are
    met:

        * Redistributions of source code must retain the above copyright
          notice, this list of conditions and the following disclaimer.

        * Redistributions in binary form must reproduce the above
          copyright notice, this list of conditions and the following
          disclaimer in the documentation and/or other materials provided
          with the distribution.

        * The names of the contributors may not be used to endorse or
          promote products derived from this software without specific
          prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
    "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
    LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
    A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
    OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
    SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
    LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
    THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
    OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.