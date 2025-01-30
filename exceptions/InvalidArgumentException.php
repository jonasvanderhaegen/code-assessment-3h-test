<?php

namespace App\Exceptions;

class InvalidArgumentException extends \Exception {
    
    private $argumentName;

    public function __construct(string $message, string $argumentName = '', int $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->argumentName = $argumentName;
    }

    public function getArgumentName(): string {
        return $this->argumentName;
    }
}
