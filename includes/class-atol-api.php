<?php
if (!defined('ABSPATH')) {
    exit;
}

class AtolApi
{
    private $token;
    private $host;

    public function __construct($token, $host)
    {
        $this->token = $token;
        $this->host = $host;
    }

    /**
     * @param int $amount
     * @param string $internalOrderId
     * @param string $atolOrderId
     * @param AtolReceipt|null $receipt
     * @return AtolResponse
     */
    public function createPayment($amount, $internalOrderId, $atolOrderId, $receipt = null)
    {
            return (new AtolRequestCreatePayment($this->token, $this->host, $atolOrderId))
                ->sendRequest($amount, $internalOrderId, $receipt);
    }

    /**
     * @param string $orderId
     * @return AtolResponse
     */
    public function paymentStatus($orderId)
    {
        return (new AtolRequestStatusPayment($this->token, $this->host, $orderId))->sendRequest();
    }

    /**
     * @deprecated
     * @param $orderId
     * @param $amount
     * @return mixed|null
     */
    public function cancel($orderId, $amount)
    {
        return (new AtolRequestCancelPayment($this->token, $this->host, $orderId))->sendRequest($amount);
    }

    /**
     * @param Throwable $e
     * @return void
     */
    private function log($e)
    {
        $logger = wc_get_logger();
        $logger->error('ERROR in ' . __CLASS__ . ": " . $e->getMessage());

        wc_add_notice( $e->getMessage(), 'error' );
    }


    /**
     * @example '0d4d2259db-e9964f6dc7-1c0bb63d5f-2e2'
     * @example 'ORDER_ID_0d4d2259db4e9964f6dc721c0bb'
     *
     * @return string
     * @throws Exception
     */
    public static function generateOrderId($orderId = null)
    {
        $transactionId = wc_rand_hash();

        if ($orderId){
            $transactionId = implode("_", [$orderId, $transactionId]);
        }

       return str_split($transactionId, 36)[0];
    }

}