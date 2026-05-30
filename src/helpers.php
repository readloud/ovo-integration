<?php
// Svn/helpers.php

if (!function_exists('gen_uuid')) {
    /**
     * Generate UUID v4
     *
     * @return string
     */
    function gen_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('gen_mac')) {
    /**
     * Generate random MAC address
     *
     * @return string
     */
    function gen_mac()
    {
        return sprintf(
            '%02x:%02x:%02x:%02x:%02x:%02x',
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(0, 255)
        );
    }
}