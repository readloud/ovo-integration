<?php
// Svn/Response/isOVOResponse.php

namespace Svn\Response;

class isOVOResponse extends BaseResponse
{
    public function isOVO()
    {
        return $this->data->isOVO ?? false;
    }
    
    public function getName()
    {
        return $this->data->name ?? null;
    }
    
    public function getAvatar()
    {
        return $this->data->avatar ?? null;
    }
}