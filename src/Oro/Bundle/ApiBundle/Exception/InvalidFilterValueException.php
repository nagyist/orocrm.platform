<?php

namespace Oro\Bundle\ApiBundle\Exception;

/**
 * This exception is thrown when a value of a filter is not valid.
 */
class InvalidFilterValueException extends RuntimeException implements ValidationExceptionInterface
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
