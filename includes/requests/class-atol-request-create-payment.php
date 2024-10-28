<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @property string $host
 * @property string $token
 */
class AtolRequestCreatePayment extends AtolRequest
{
    const METHOD = 'POST';

    /**
     * @return string
     */
    public function endpoint()
    {
        return sprintf('%s/v1/ecom/payments', $this->host);
    }

    /**
     * @param int $amount
     * @param string $internalOrderId
     * @param AtolReceipt $receipt
     * @return AtolResponse
     */
    public function sendRequest($amount, $internalOrderId, $receipt = null)
    {
        $params = [
            "amount" => $amount,
            "orderId" => $this->atolOrderId,
            "sessionType" => "oneStep",
            "additionalProps" => [
                'returnUrl' => $this->createReturnUrl($internalOrderId, $this->atolOrderId, home_url()),
                'notificationUrl' => $this->createReturnUrl($internalOrderId, $this->atolOrderId, home_url()),
                'sourceName' => 'wp',
                'sourceVersion' => ATOL_PLUGIN_VERSION,
            ],
        ];

        $email = wp_get_current_user()->user_email;
        if ($email) {
            $params["buyerId"] = md5($email);
        }

        if ($receipt) {
            $params['receipt'] = $receipt->toArray();
        }

        $response = wp_remote_request($this->endpoint(),
            [
                'headers' => $this->getHeaders(),
                'method' => self::METHOD,
                'body' => json_encode($params, JSON_NUMERIC_CHECK)
            ]);

        return (new AtolResponse($response, $this->endpoint()))->getResponse();
    }

    /**
     * @param string $internalOrderId
     * @param string $atolOrderId
     * @return string
     */
    private function createReturnUrl($internalOrderId, $atolOrderId, $callbackUrl)
    {
        return wp_sanitize_redirect($callbackUrl)
            . '/?wc-api=eh_spg_atol_checkout_order' . '&sessionid=' . wp_get_session_token()
            . '&order_id=' . $internalOrderId
            . '&atol_order_id=' . $atolOrderId
            . '&_wpnonce=' . wp_create_nonce('eh_checkout_nonce');
    }
}