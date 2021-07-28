<?php

namespace SmsclubApi\Http;

/**
 * Class SmsClient
 * @package SmsclubApi\Http
 */
class SmsClient extends Client
{
    /**
     * SmsClient constructor.
     * @param array $credentials
     * @throws \Exception
     */
    public function __construct($credentials)
    {
        $this->setCredentials($credentials);

        $this->request = $this->headers([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->getToken()
        ]);
    }

    /**
     * @return bool|string
     */
    public function getOriginators()
    {
        return $this->request->url(SMS_API_URL . '/originator')
            ->post()
            ->returnTransfer()
            ->execute();
    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function sendSms($data)
    {
        return $this->request->url(SMS_API_URL . '/send')
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
        return $this->request->url(SMS_API_URL . '/status')
            ->post()
            ->postFields($data)
            ->returnTransfer()
            ->execute();
    }

    /**
     * @return bool|string
     */
    public function getBalance()
    {
        return $this->request->url(SMS_API_URL . '/balance')
            ->post()
            ->returnTransfer()
            ->execute();
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getToken()
    {
        $credentials = $this->getCredentials();

        if (!isset($credentials['token']))
            throw new \Exception('Wrong credentials format');

        return $credentials['token'];
    }
}
