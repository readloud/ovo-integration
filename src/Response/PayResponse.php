<?php
namespace Svn\Response;

class PayResponse
{
    private $response;
    public function __construct($data)
    {
        $this->response = $data;
    }
    
    /**
     * response
     *
     * @return void
     */
    public function getPayResponse()
    {
        return $this->response;
    }
}
