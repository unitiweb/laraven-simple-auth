<?php

namespace TokenAuth\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

/**
 * Class RefreshTokenExpiredException
 *
 * @package App\Exceptions
 */
class RefreshTokenExpiredException extends AuthorizationException
{
    /**
     * CodeInvalidException constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = "", Throwable $previous = null)
    {
        $message = $message ?? 'The auth refresh token has expired.';
        $code = 401;

        parent::__construct($message, $code, $previous);
    }
}
