<?php

declare(strict_types=1);

namespace PhpCfdi\Sat49BScraper\Exceptions;

use Throwable;

class NetworkException extends Sat49BException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
