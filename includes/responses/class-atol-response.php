<?php
if (!defined('ABSPATH')) {
    exit;
}

class AtolResponse
{
    /**
     * @var array|WP_Error|null $response
     */
    private $response;

    /**
     * @var stdClass|null $body
     */
    public $body;

    /**
     * @var string|null $errorCode
     */
    public $errorCode;

    /**
     * @var string|null $errorMessage
     */
    public $errorMessage = null;

    /**
     * @var string|null $status
     */
    public $status = null;

    /**
     * @var int
     */
    public $httpCode;

    /**
     * @var string
     */
    public $url;

    public function __construct($response, $url)
    {
        $this->response = $response;
        $this->url = $url;

        if ($response instanceof WP_Error) {
            wc_get_logger()->error(
                sprintf("%s, msg=%s, url=%s", __METHOD__, $response->get_error_message(), $this->url)
            );

            throw new \RuntimeException($response->get_error_message());
        }
    }

    /**
     * @return AtolResponse
     */
    public function getResponse()
    {
        $this->body = json_decode(wp_remote_retrieve_body($this->response), false);
        $this->httpCode = wp_remote_retrieve_response_code($this->response);

        $this->status = $this->body->status;

        if ($this->status === 'error') {
            $this->errorMessage = $this->body->errorMessage;
            $this->errorCode = $this->body->errorCode;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->body->data->status;
    }

    /**
     * @return false|string
     */
    public function toString()
    {
        $this->body->http_code = $this->httpCode;
        $this->body->url = $this->url;

        return json_encode($this->body, JSON_UNESCAPED_UNICODE);
    }

}