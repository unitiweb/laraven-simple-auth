<?php

namespace TokenAuth\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

/**
 * Class AuthSignatureInvalidException
 *
 * @package App\Exceptions
 */
class AuthSignatureInvalidException extends AuthorizationException
{
    /**
     * CodeInvalidException constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = "", Throwable $previous = null)
    {
        $message = $message ?? 'The auth token signature is not valid.';
        $code = 401;

        parent::__construct($message, $code, $previous);
    }
}
