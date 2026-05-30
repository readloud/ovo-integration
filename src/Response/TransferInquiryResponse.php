<?php

namespace Svn\Response;

class TransferInquiryResponse
{
    private $response;

    public function __construct($data)
    {
        $this->response = $data;
    }

    public function getTransferInquiryResponse()
    {
        return $this->response;
    }
}
