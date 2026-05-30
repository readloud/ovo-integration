<?php
// Svn/Response/TransferDirectResponse.php

namespace Svn\Response;

class TransferDirectResponse extends BaseResponse
{
    public function getTransactionId()
    {
        return $this->data->transactionId ?? null;
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