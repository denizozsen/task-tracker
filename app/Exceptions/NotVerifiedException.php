<?php

namespace App\Exceptions;

/**
 * type: not_verified
 * Thrown at login-time, when the user trying to login has not yet completed the email verification step.
 */
class NotVerifiedException extends UserFacingException
{
    public function __construct(
        string $message    = 'You must verify your account, before you can log in',
        string $type       = 'not_verified',
        int    $httpStatus = 401
    ) {
        parent::__construct($message, $type, $httpStatus);
    }
}
