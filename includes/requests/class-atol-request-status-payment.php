<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @property string $host
 * @property string $token
 * @property string $orderId
 */
class AtolRequestStatusPayment extends AtolRequest
{
    const METHOD = 'GET';

    /**
     * @return string
     */
    public function endpoint()
    {
        return sprintf('%s/v1/ecom/payments/%s/status', $this->host, $this->atolOrderId);
    }

    /**
     * @return AtolResponse
     */
    public function sendRequest()
    {
        $response = wp_remote_request($this->endpoint(), [
            'headers' => $this->getHeaders(),
            'method' => self::METHOD
        ]);

        return (new AtolResponse($response, $this->endpoint()))->getResponse();
    }
}