<?php

namespace TokenAuth\Exceptions;

use Exception;
use Throwable;

/**
 * Class UserModelMisconfiguredException
 *
 * @package App\Exceptions
 */
class UserModelMisconfiguredException extends Exception
{
    /**
     * CodeInvalidException constructor.
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct($message = "", Throwable $previous = null)
    {
        $message = $message ?? 'The user model is not configured properly';
        $code = 500;

        parent::__construct($message, $code, $previous);
    }
}
