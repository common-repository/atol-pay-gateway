<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class that handles atol payment api params and response data.
 */
class AtolPayment
{
    const KEY_ORDER = 'atol_order';
    const KEY_ORDER_ID = 'atol_order_id';
    const KEY_CHECKOUT_URL = 'atol_url';


    /**
     * @param WC_Order $order
     * @return void
     * @throws Exception
     */
    public static function attachOrder($order)
    {
        $id = self::attachAtolOrderId($order);

        $atolOrder = WC()->session->get(self::KEY_ORDER);

        if (!$atolOrder){
            $atolOrder = [
                'order_id' => $order->get_id(),
                'atol_order_id' => $id,
            ];

            WC()->session->set(self::KEY_ORDER, $atolOrder);
        }
    }

    /**
     * @param WC_Order $order
     * @return string
     * @throws Exception
     */
    public static function attachAtolOrderId(WC_Order $order)
    {
        $id = self::getAtolOrderId($order);

        if (!$id) {
            $id = AtolApi::generateOrderId($order->get_id());
            $order->add_meta_data(self::KEY_ORDER_ID, $id, true);
            $order->apply_changes();
            $order->save_meta_data();
        }

        return $id;
    }

    /**
     * @param WC_Order $order
     * @return mixed
     */
    public static function getAtolOrderId($order)
    {
        return $order->get_meta(self::KEY_ORDER_ID);
    }

    /**
     * @param WC_Order $order
     * @return void
     */
    public static function flushAtolOrderId($order)
    {
        wc_get_logger()->notice(__METHOD__ ." order_id=". $order->get_id());

        $order->delete_meta_data(self::KEY_ORDER_ID);
        $order->apply_changes();
//        $order->save_meta_data();
    }

    /**
     * @param WC_Order $order
     * @param string $url
     * @throws Exception
     */
    public static function attachAtolCheckoutUrl($order, $url)
    {
        wc_get_logger()->notice(__METHOD__ ." order_id=". $order->get_id());

        $order->add_meta_data(self::KEY_CHECKOUT_URL, $url, true);
        $order->apply_changes();
        $order->save_meta_data();
    }

    /**
     * @param WC_Order $order
     * @throws Exception
     */
    public static function flushAtolCheckoutUrl($order)
    {
        wc_get_logger()->notice(__METHOD__ ." order_id=". $order->get_id());

        $order->delete_meta_data(self::KEY_CHECKOUT_URL);
        $order->apply_changes();
        $order->save_meta_data();
    }

    /**
     * @param WC_Order $order
     * @return null|string
     */
    public static function getAtolCheckoutUrl($order)
    {
        return $order->get_meta(self::KEY_CHECKOUT_URL);
    }
}