<?php
// Svn/Response/CustomerTransferResponse.php

namespace Svn\Response;

class CustomerTransferResponse extends BaseResponse
{
    public function getTrxId()
    {
        return $this->data->trxId ?? null;
    }
    
    public function getStatus()
    {
        return $this->data->status ?? null;
    }
    
    public function getMessage()
    {
        return $this->data->message ?? null;
    }
}