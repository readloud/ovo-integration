<?php
// Svn/Response/OTPValidationResponse.php

namespace Svn\Response;

class OTPValidationResponse extends BaseResponse
{
    public function getOtpToken()
    {
        return $this->data->otpToken ?? null;
    }
    
    public function getMessage()
    {
        return $this->data->message ?? null;
    }
}