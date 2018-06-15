<?php

namespace App\Exceptions;

/**
 * type: not_authenticated
 * Thrown when the user making a request is not authenticated.
 */
class NotAuthenticatedException extends UserFacingException
{
    public function __construct(
        string $message = 'Not authenticated',
        string $type = 'not_authenticated',
        int    $httpStatus = 401
    ) {
        parent::__construct($message, $type);
    }
}
