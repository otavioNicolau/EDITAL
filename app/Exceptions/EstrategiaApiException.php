<?php

namespace App\Exceptions;

use Exception;

class EstrategiaApiException extends Exception
{
    public static function fromMessage(string $message): self
    {
        return new self($message);
    }
}
