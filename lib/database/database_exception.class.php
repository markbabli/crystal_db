<?php
Direct_Call::isDirectCall();
class Database_Exception extends Exception
{
    public function __construct($message, $code=null, $previous=null)
    {
        parent::__construct($message, $code, $previous);
    }
}
