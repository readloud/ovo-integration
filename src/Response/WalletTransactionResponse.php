<?php
// Svn/Response/WalletTransactionResponse.php

namespace Svn\Response;

class WalletTransactionResponse extends BaseResponse
{
    public function getTransactions()
    {
        return $this->data->data ?? [];
    }
    
    public function getPage()
    {
        return $this->data->page ?? 1;
    }
    
    public function getLimit()
    {
        return $this->data->limit ?? 10;
    }
    
    public function getTotal()
    {
        return $this->data->total ?? 0;
    }
}