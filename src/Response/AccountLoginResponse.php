<?php
// Svn/Response/AccountLoginResponse.php

namespace Svn\Response;

class AccountLoginResponse extends BaseResponse
{
    public function getAuth()
    {
        return $this->data->auth ?? null;
    }
    
    public function getAccessToken()
    {
        return $this->data->auth->accessToken ?? null;
    }
    
    public function getRefreshToken()
    {
        return $this->data->auth->refreshToken ?? null;
    }
    
    public function getUser()
    {
        return $this->data->user ?? null;
    }
}