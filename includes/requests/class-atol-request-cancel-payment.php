<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @property string $host
 * @property string $token
 * @property string $orderId
 */
class AtolRequestCancelPayment extends AtolRequest
{
    const METHOD = 'GET';

    /**
     * @return string
     */
    public function endpoint()
    {
        return sprintf('%s/v1/ecom/payments/%s/cancel', $this->host, $this->orderId);
    }

    /**
     * @param int $amount
     * @return AtolResponse
     */
    public function sendRequest($amount)
    {
        $response = wp_remote_request($this->endpoint(), [
            'headers' => $this->getHeaders(),
            'method' => self::METHOD,
            'body' => json_encode(["amount" => $amount], JSON_NUMERIC_CHECK)
        ]);

        return (new AtolResponse($response, $this->endpoint()))->getResponse();
    }
}