<?php

namespace FunctionParser;

/**
 * FunctionParser
 *
 * The FunctionParser has the ability to take a reflected function or method and retrieve its code. In the case of a
 * Closure, it will also get the names and values of any closed upon variables (i.e. variables in the "use" statement).
 * It relies on PHP lexical scanner, so the PHP tokenizer must be enabled in order to use the library.
 *
 * @package FunctionParser
 * @author  Jeremy Lindblom
 * @license MIT
 */
class FunctionParser
{
    /**
     * @var \ReflectionFunctionAbstract The reflected function.
     */
    protected $reflection;

    /**
     * @var array An array of the function's parameter names.
     */
    protected $parameters;

    /**
     * @var Tokenizer The tokenizer holding the tokenized code of the function.
     */
    protected $tokenizer;

    /**
     * @var string The code of the entire function.
     */
    protected $code;

    /**
     * @var string The code of only the body of the function.
     */
    protected $body;

    /**
     * @var array An array of variables from the "use" statement of closure.
     */
    protected $context;

    /**
     * A factory method that creates a FunctionParser from any PHP callable.
     *
     * @param mixed $callable A PHP callable to be parsed.
     * @return FunctionParser An instance of FunctionParser.
     * @throws \InvalidArgumentException
     */
    public static function fromCallable($callable)
    {
        if (!is_callable($callable))
        {
            throw new \InvalidArgumentException('You must provide a vaild PHP callable.');
        }
        elseif (is_string($callable) && strpos($callable, '::') > 0)
        {
            $callable = explode('::', $callable);
        }

        if (is_array($callable))
        {
            list($class, $method) = $callable;
            $reflection = new \ReflectionMethod($class, $method);
        }
        else
        {
            $reflection = new \ReflectionFunction($callable);
        }

        return new static($reflection);
    }

    /**
     * Constructs a FunctionParser from a reflected function. Triggers all code parsing from the constructor.
     *
     * @param \ReflectionFunctionAbstract $reflection The reflected function or method.
     */
    public function __construct(\ReflectionFunctionAbstract $reflection)
    {
        if (!$reflection->isUserDefined())
        {
            throw new \InvalidArgumentException('You can only parse the code of user-defined functions.');
        }

        $this->reflection = $reflection;
        $this->tokenizer  = $this->fetchTokenizer();
        $this->parameters = $this->fetchParameters();
        $this->code       = $this->parseCode();
        $this->body       = $this->parseBody();
        $this->context    = $this->parseContext();
    }

    /**
     * Get the reflected method or function for this passer.
     *
     * @return \ReflectionFunctionAbstract The reflected function.
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * Returns the name of the function, if there is one.
     *
     * @return null|string The name of the function.
     */
    public function getName()
    {
        $name = $this->reflection->getName();

        if (strpos($name, '{closure}') !== false)
        {
            return null;
        }

        return $name;
    }

    /**
     * Returns a list of the parameter names of the function.
     *
     * @return array The array of parameter names.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the tokenizer used to parse the function.
     *
     * @return \FunctionParser\Tokenizer The tokenizer.
     */
    public function getTokenizer()
    {
        return $this->tokenizer;
    }

    /**
     * Returns the code that defines the function as a string.
     *
     * @return string The code defining the function.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the bosy of the code without the function signature or braces.
     *
     * @return string The body of the code.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns an array of variable names and values representing the context of the function. These variables are the
     * ones specified in the "use" statement which can only be used when defining closures. If the function being parsed
     * is not a closure, then getContext will return an empty array.
     *
     * @return array Array of "used" variables in the closure.
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns the name of the class where the method being parsed is defined. If the function bieing parsed is not a
     * method, then it will return null.
     *
     * @return null|string The parent class of the method.
     */
    public function getClass()
    {
        if (method_exists($this->reflection, 'getDeclaringClass'))
        {
            return $this->reflection->getDeclaringClass();
        }

        return null;
    }

    /**
     * Uses reflection to get the parameter names for the functions.
     *
     * @return array An array of the parameter names.
     */
    protected function fetchParameters()
    {
        return array_map(
            function(\ReflectionParameter $param) {
                return $param->name;
            },
            $this->reflection->getParameters()
        );
    }

    /**
     * Creates a tokenizer representing the code that is the best candidate for representing the function. It uses
     * reflection to find the file and lines of the code and then puts that code into the tokenizer.
     *
     * @return \FunctionParser\Tokenizer The tokenizer of the function's code.
     */
    protected function fetchTokenizer()
    {
        // Load the file containing the code for the function
        $file = new \SplFileObject($this->reflection->getFileName());

        // Identify the first and last lines of the code for the function
        $first_line = $this->reflection->getStartLine();
        $last_line  = $this->reflection->getEndLine();

        // Retrieve all of the lines that contain code for the function
        $code = '';
        $file->seek($first_line - 1);
        while ($file->key() < $last_line)
        {
            $code .= $file->current();
            $file->next();
        }

        // Setup the tokenizer with the code from the file
        $tokenizer = new Tokenizer($code);

        // Eliminate tokens that are definitely not a part of the function code
        $start     = $tokenizer->findToken(T_FUNCTION);
        $finish    = $tokenizer->findToken('}', -1);
        $tokenizer = $tokenizer->getTokenRange($start, $finish + 1);

        return $tokenizer;
    }

    /**
     * Parses the code using the tokenizer and keeping track of matching braces.
     *
     * @return string The code representing the function.
     * @throws \RuntimeException on invalid code.
     */
    protected function parseCode()
    {
        $brace_level      = 0;
        $parsed_code      = '';
        $parsing_complete = false;

        // Parse the code looking for the end of the function
        /** @var $token \FunctionParser\Token */
        foreach ($this->tokenizer as $token)
        {
            /***********************************************************************************************************
             * AFTER PARSING
             *
             * After the parsing is complete, we need to make sure there are no other T_FUNCTION tokens found, which
             * would indicate a possible ambiguity in the function code we retrieved. This should only happen in
             * situations where the code is minified or poorly formatted.
             */
            if ($parsing_complete)
            {
                if ($token->is(T_FUNCTION))
                {
                    throw new \RuntimeException('Cannot parse the function; multiple, non-nested functions were defined'
                        . ' in the code block containing the desired function.');
                }
                else
                {
                    continue;
                }
            }

            /***********************************************************************************************************
             * WHILE PARSING
             *
             * Scan through the tokens (while keeping track of braces) and reconstruct the code from the parsed tokens.
             */

            // Keep track of opening and closing braces
            if ($token->isOpeningBrace())
            {
                $brace_level++;
            }
            elseif ($token->isClosingBrace())
            {
                $brace_level--;

                // Once we reach the function's closing brace, mark as complete
                if ($brace_level === 0)
                {
                    $parsing_complete = true;
                }
            }

            // Reconstruct the code token by token
            $parsed_code .= $token->code;
        }

        /*
         * If all tokens have been looked at and the closing brace was not found, then there is a
         * problem with the code defining the Closure. This should probably never happen, but just
         * in case...
         */
        if (!$parsing_complete)
        {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Cannot parse the function because the code appeared to be invalid.');
            // @codeCoverageIgnoreEnd
        }

        return $parsed_code;
    }

    /**
     * Removes the function signature and braces to expose only the procedural body of the function.
     *
     * @return string The body of the function.
     */
    protected function parseBody()
    {
        // Remove the function signature and outer braces
        $start  = strpos($this->code, '{');
        $finish = strrpos($this->code, '}');
        $body   = ltrim(rtrim(substr($this->code, $start + 1, $finish - $start - 1)), "\n");

        return $body;
    }

    /**
     * Does some additional tokenizing and reflection to determine the names and values of variables included in the
     * closure (or context) via "use" statement. For functions that are not closures, an empty array is returned.
     *
     * @return array The array of "used" variables in the closure (a.k.a the context).
     */
    protected function parseContext()
    {
        $context = array();

        if ($this->reflection->isClosure())
        {
            $variable_names = array();
            $inside_use     = false;

            // Parse the variable names from the "use" contruct by scanning tokens
            /** @var $token \FunctionParser\Token */
            foreach ($this->tokenizer as $token)
            {
                if (!$inside_use && $token->is(T_USE))
                {
                    // Once we find the "use" construct, set the flag
                    $inside_use = true;
                }
                elseif ($inside_use && $token->is(T_VARIABLE))
                {
                    // For variables found in the "use" construct, get the name
                    $variable_names[] = trim($token->getCode(), '$ ');
                }
                elseif ($inside_use && $token->isClosingParenthesis())
                {
                    // Once we encounter a closing parenthesis at the end of the
                    // "use" construct, then we are finished parsing.
                    break;
                }
            }

            // Get the values of the variables that are closed upon in "use"
            $variable_values = $this->reflection->getStaticVariables();

            // Construct the context by combining the variable names and values
            foreach ($variable_names as $variable_name)
            {
                if (isset($variable_values[$variable_name]))
                {
                    $context[$variable_name] = $variable_values[$variable_name];
                }
            }
        }

        return $context;
    }
}
