<?php

namespace SmsclubApi\Http;

/**
 * Class Client
 * @package SmsclubApi\Http
 */
class Client
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $credentials;

    /**
     * @var self
     */
    protected $request;

    /**
     * @param int $key
     * @param string|int|bool $value
     */
    protected function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @param int $key
     * @return string|int|bool
     */
    protected function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * @param array $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @return self
     */
    protected function post()
    {
        $this->setOption(CURLOPT_POST, true);
        return $this;
    }

    /**
     * @return self
     */
    protected function returnTransfer()
    {
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        return $this;
    }

    /**
     * @param array $headers
     * @return self
     */
    protected function headers($headers)
    {
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    protected function url($url)
    {
        $this->setOption(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * @param string $data
     * @return self
     */
    protected function postFields($data)
    {
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        return $this;
    }

    /**
     * @param string $data
     * @return self
     */
    protected function auth($data)
    {
        $this->setOption(CURLOPT_USERPWD, $data);
        return $this;
    }

    /**
     * @return bool|string
     */
    protected function execute()
    {
        $ci = curl_init();

        foreach ($this->options as $key => $value) {
            curl_setopt($ci, $key, $value);
        }

        $response = json_decode(curl_exec($ci), JSON_OBJECT_AS_ARRAY);
        curl_close($ci);

        return $response;
    }
}
