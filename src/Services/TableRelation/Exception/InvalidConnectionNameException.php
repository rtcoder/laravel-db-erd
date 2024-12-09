<?php

namespace Rtcoder\LaravelERD\Services\TableRelation\Exception;

use Exception;
use Throwable;

class InvalidConnectionNameException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
