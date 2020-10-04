<?php

namespace TokenAuth\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

/**
 * Class CodeInvalidException
 *
 * @package App\Exceptions
 */
class CodeInvalidException extends AuthorizationException
{
    /**
     * CodeInvalidException constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = "", Throwable $previous = null)
    {
        $message = $message ?? 'The code is either not valid or expired';
        $code = 500;

        parent::__construct($message, $code, $previous);
    }
}
