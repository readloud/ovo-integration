<?php
// Svn/Response/NotificationUnreadResponse.php

namespace Svn\Response;

class NotificationUnreadResponse extends BaseResponse
{
    public function getTotal()
    {
        return $this->data->total ?? 0;
    }
}