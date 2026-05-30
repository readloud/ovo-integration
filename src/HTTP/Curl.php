<?php
// Svn/HTTP/Curl.php

namespace Svn\HTTP;

use Svn\HTTPClient;
use Svn\ParseResponse;

class Curl implements HTTPClient
{
    /**
     * @var resource
     */
    private $ch;
    
    /**
     * @var array
     */
    private $defaultOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ch = curl_init();
        curl_setopt_array($this->ch, $this->defaultOptions);
    }
    
    /**
     * Send POST request
     *
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return ParseResponse
     */
    public function post($url, $data, $headers)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        
        $response = curl_exec($this->ch);
        $error = curl_error($this->ch);
        
        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }
        
        return new ParseResponse($response, $url);
    }
    
    /**
     * Send GET request
     *
     * @param string $url
     * @param array|null $data
     * @param array $headers
     * @return ParseResponse
     */
    public function get($url, $data, $headers)
    {
        if ($data && is_array($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        
        $response = curl_exec($this->ch);
        $error = curl_error($this->ch);
        
        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }
        
        return new ParseResponse($response, $url);
    }
    
    /**
     * Format headers for CURL
     *
     * @param array $headers
     * @return array
     */
    private function formatHeaders($headers)
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->ch) {
            curl_close($this->ch);
        }
    }
}