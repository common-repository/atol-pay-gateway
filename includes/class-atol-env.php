<?php
if (!defined('ABSPATH')) {
    exit;
}

class AtolEnv
{
    public static function getHost($envName = 'dev')
    {
        switch ($envName) {
            case 'live':
                $host = 'https://new-api-mobile.atolpay.ru';
                break;
            case 'test':
                $host = 'https://croc-sandbox-api-mobile.atolpay.ru';
                break;
            case 'dev':
            default:
                $host = 'http://host.docker.internal:8085';
                break;
        }

        return $host;
    }

}