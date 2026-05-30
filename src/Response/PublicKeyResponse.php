<?php
// Svn/Response/PublicKeyResponse.php

namespace Svn\Response;

class PublicKeyResponse extends BaseResponse
{
    public function getKeys()
    {
        return $this->data->keys ?? [];
    }
    
    public function getPublicKey()
    {
        if (!empty($this->data->keys) && isset($this->data->keys[0]->key)) {
            return $this->data->keys[0]->key;
        }
        return null;
    }
}