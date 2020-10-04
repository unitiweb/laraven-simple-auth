<?php

namespace TokenAuth\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

/**
 * Class AuthUserNotFoundException
 *
 * @package App\Exceptions
 */
class AuthorizationFailedException extends AuthorizationException
{
    /**
     * AuthorizationFailedException constructor.
     *
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct($message = null, Throwable $previous = null)
    {
        $message = $message ?? 'The user is not authorized.';

        parent::__construct($message, null, $previous);
    }
}
