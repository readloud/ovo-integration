<?php
// Svn/Exception/AmountException.php

namespace Svn\Exception;

use Exception;

class AmountException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}