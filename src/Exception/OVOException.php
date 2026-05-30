<?php
// src/exceptions/OVOException.php

namespace Svn\OVOID\Exceptions;

use Exception;

class OVOException extends Exception
{
    private $errorCode;
    
    public function __construct(string $message, int $code = 0, ?string $errorCode = null)
    {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
    }
    
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
