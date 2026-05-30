<?php
// Svn/Response/BaseResponse.php

namespace Svn\Response;

class BaseResponse
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function toArray()
    {
        return (array) $this->data;
    }
    
    public function toJson()
    {
        return json_encode($this->data);
    }
}