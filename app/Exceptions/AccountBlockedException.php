<?php

namespace App\Exceptions;

/**
 * type: account_blocked
 * Thrown at login-time, when the user trying to login has previously failed to login several times in a row.
 */
class AccountBlockedException extends UserFacingException
{
    public function __construct(
        string $message    = 'Your account is blocked, due to repeated failed login attempts. please contact an administrator.',
        string $type       = 'account_blocked',
        int    $httpStatus = 400
    ) {
        parent::__construct($message, $type, $httpStatus);
    }
}
