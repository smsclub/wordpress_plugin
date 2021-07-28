<?php

namespace SmsclubApi\Http;

/**
 * Class ViberClient
 * @package SmsclubApi\Http
 */
class ViberClient extends Client
{
    /**
     * ViberClient constructor.
     * @param array $credentials
     * @throws \Exception
     */
    public function __construct($credentials)
    {
        $this->setCredentials($credentials);

        $this->request = $this->headers(['Content-Type: application/json'])
            ->auth($this->getAuth());
    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function sendMessage($data)
    {
        return $this->request->url(VIBER_API_URL . '/send')
            ->post()
            ->postFields($data)
            ->returnTransfer()
            ->execute();
    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function getStatus($data)
    {
        return $this->request->url(VIBER_API_URL . '/status')
            ->post()
            ->postFields($data)
            ->returnTransfer()
            ->execute();
    }

    /**
     * @return bool|string
     */
    public function getOriginators()
    {
        return $this->request->url(VIBER_API_URL . '/originator')
            ->returnTransfer()
            ->execute();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getAuth()
    {
        $credentials = $this->getCredentials();

        if (!isset($credentials['login']) || !isset($credentials['password']))
            throw new \Exception('Wrong credentials format');

        return $credentials['login'] . ':' . $credentials['password'];
    }
}

