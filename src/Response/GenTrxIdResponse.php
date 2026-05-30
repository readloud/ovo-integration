<?php
// Svn/Response/GenTrxIdResponse.php

namespace Svn\Response;

class GenTrxIdResponse extends BaseResponse
{
    public function getTrxId()
    {
        return $this->data->trxId ?? null;
    }
    
    public function getActionMark()
    {
        return $this->data->actionMark ?? null;
    }
}