<?php
// Svn/Response/NotificationAllResponse.php

namespace Svn\Response;

class NotificationAllResponse extends BaseResponse
{
    public function getNotifications()
    {
        return $this->data->notifications ?? [];
    }
    
    public function getTotal()
    {
        return $this->data->total ?? 0;
    }
}