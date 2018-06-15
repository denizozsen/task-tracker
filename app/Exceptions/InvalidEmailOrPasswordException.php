<?php

namespace App\Exceptions;

/**
 * type: invalid_email_or_password
 * Thrown at login-time, when the user trying to login has supplied either an email not registered with the system
 * or a password that is not valid for the supplied email.
 */
class InvalidEmailOrPasswordException extends UserFacingException
{
    public function __construct(
        string $message    = 'Invalid email or password',
        string $type       = 'invalid_email_or_password',
        int    $httpStatus = 400
    ) {
        parent::__construct($message, $type, $httpStatus);
    }
}
