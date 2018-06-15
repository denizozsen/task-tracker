<?php

namespace App\Exceptions;

/**
 * Base class for exceptions that are meant to be displayed to users.
 */
class UserFacingException extends \Exception
{
    private $type;
    private $httpStatus;

    public function __construct(string $message = '', string $type = '', int $httpStatus = 500)
    {
        parent::__construct($message);
        $this->type       = $type;
        $this->httpStatus = $httpStatus;
    }

    /**
     * @return string the type that uniquely identifies the exception type, e.g. useful for sending to front end code
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int the HTTP status code that a web/API response should carry
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
