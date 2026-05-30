<?php
// Svn/Exception/OvoidException.php

namespace Svn\Exception;

use Exception;

class OvoidException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}