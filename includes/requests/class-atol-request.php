<?php


abstract class AtolRequest
{
    /**
     * @var string $host
     */
    public $host;

    /**
     * @var string $token
     */
    public $token;

    /**
     * @var string $atolOrderId
     */
    public $atolOrderId;

    /**
     * @return string
     */
    abstract public function endpoint();

    public function __construct($token, $host, $atolOrderId)
    {
        $this->host = $host;
        $this->token = $token;
        $this->atolOrderId = $atolOrderId;
    }

    protected function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }
}