<?php
// Svn/Response/OTPResponse.php

namespace Svn\Response;

class OTPResponse extends BaseResponse
{
    public function getOtp()
    {
        return $this->data->otp ?? null;
    }
    
    public function getOtpRefId()
    {
        return $this->data->otp->otpRefId ?? null;
    }
    
    public function getMessage()
    {
        return $this->data->message ?? null;
    }
}