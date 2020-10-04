<?php

namespace TokenAuth\Exceptions;

use Exception;
use Throwable;

/**
 * Class TokenAuthConfigurationException
 *
 * @package App\Exceptions
 */
class TokenAuthConfigurationException extends Exception
{
    /**
     * TokenAuthConfigurationException constructor.
     *
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct($message = null, Throwable $previous = null)
    {
        $message = 'There is an issue with the token auth configuration: ' . $message;

        parent::__construct($message, 401, $previous);
    }
}
