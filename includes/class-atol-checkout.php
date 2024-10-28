<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class that handles atol checkout payment method.
 *
 * @extends WC_Payment_Gateway
 *
 */
class Eh_Atol_Checkout extends WC_Payment_Gateway
{
    /** @var string $atol_token */
    private $atol_token;

    /** @var string $env */
    private $env = 'dev';

    /** @var string $atol_send_receipt */
    private $atol_send_receipt;

    /** @var string $atol_send_receipt */
    private $eh_atol_pay_sno;

    /** @var string */
    private $eh_atol_pay_tax;

    /** @var string */
    private $eh_atol_pay_shipment_tax;

    /** @var string */
    private $eh_atol_payment_method;

    /** @var string */
    private $eh_atol_payment_subject;

    public function __construct()
    {
        $this->id = 'eh_atol_checkout';
        $this->method_title = 'ATOL PAY';
        $this->method_description = 'Принимать платежи через платежную форму АТОЛ PAY';


        // Load the form fields.
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->title = esc_attr($this->get_option('eh_atol_checkout_title'));
        $this->description = esc_attr($this->get_option('eh_atol_checkout_description'));

        $this->enabled = $this->get_option('enabled');
        $this->atol_token = esc_attr($this->get_option('eh_atol_pay_token'));

        $this->env = esc_attr($this->get_option('eh_atol_transaction_mode', 'dev'));

        $this->order_button_text = esc_attr($this->get_option('eh_atol_checkout_order_button'));

        /**
         * Send receipt section to ATOP checkout api
         */
        $this->atol_send_receipt = $this->get_option('eh_send_receipt');

        $this->eh_atol_pay_sno = esc_attr($this->get_option('eh_atol_pay_sno'));
        $this->eh_atol_pay_tax = esc_attr($this->get_option('eh_atol_pay_tax'));
        $this->eh_atol_pay_shipment_tax = esc_attr($this->get_option('eh_atol_pay_shipment_tax'));

        $this->eh_atol_payment_method = esc_attr($this->get_option('eh_atol_payment_method'));
        $this->eh_atol_payment_subject = esc_attr($this->get_option('eh_atol_payment_subject'));

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

        add_action('wc_ajax_eh_spg_atol_checkout_order', array($this, 'eh_spg_atol_checkout_order_callback'));
        add_action('woocommerce_api_eh_spg_atol_checkout_order', array($this, 'eh_spg_atol_checkout_order_callback'));

//        add_action( 'wc_ajax_eh_spg_atol_cancel_order', array( $this, 'eh_spg_atol_cancel_order' ) );
        add_action('set_logged_in_cookie', array($this, 'eh_set_cookie_on_current_request'));
    }

    /**
     * Initialize form fields in atol checkout payment settings page.
     * @since 3.3.4
     */
    public function init_form_fields()
    {
        $modeOptions = [
            'test' => 'Test mode',
            'live' => 'Live mode'
        ];

        if (ATOL_PLUGIN_DEV_MODE) {
            $modeOptions['dev'] = 'Dev mode';
        }

        $this->form_fields = array(

            'eh_atol_checkout_form_title' => array(
                'type' => 'title',
                'class' => 'eh-css-class',
            ),

            'enabled' => array(
                'title' => 'ATOL Checkout',
                'label' => 'Включить',
                'type' => 'checkbox',
                'default' => 'no',
                'desc_tip' => 'Включить прием платежей через ATOL Checkout.'
            ),

            'eh_atol_transaction_mode' => array(
                'title' => 'Режим транзакций',
                'type' => 'select',
                'options' => $modeOptions,
                'class' => 'wc-enhanced-select',
                'default' => 'test',
                'desc_tip' => 'Выберите тестовый режим для пробного запуска с использованием тестовых ключей API.\n
                 Переключитесь в режим реального времени, чтобы начать принимать платежи с помощью ATOL с использованием действующих ключей API.'),

            'eh_atol_pay_token' => array(
                'title' => 'API Токен',
                'description' => 'Тoken из ЛК ATOL PAY для взаимодействия по АПИ',
                'type' => 'text',
                'desc_tip' => true,
                'default' => '',
            ),

            'eh_atol_checkout_title' => array(
                'title' => 'Заголовок',
                'type' => 'text',
                'description' => 'Название платежного шлюза, отображаемого при оформлении заказа.',
                'default' => 'ATOL Checkout',
                'desc_tip' => true,
            ),
            'eh_atol_checkout_description' => array(
                'title' => 'Описание',
                'type' => 'textarea',
                'css' => 'width:25em',
                'description' => 'Input texts for the payment gateway displayed at the checkout.',
                'default' => 'Secure payment via ATOL Checkout.',
                'desc_tip' => true
            ),

            'eh_atol_checkout_order_button' => array(
                'title' => 'Текст кнопки заказа',
                'type' => 'text',
                'description' => 'Input a text that will appear on the order button to place order at the checkout.',
                'default' => 'Оплата через ATOL Pay',
                'desc_tip' => true
            ),

            'eh_checkout_webhook_desc' => array(
                'type' => 'title',
                'description' => sprintf(
                    '%1$sДля отправки чеков через <b>АТОЛ Онлайн</b> необходимо включить функцию <b>Фискализация чека</b> и настроить секцию налогообложения%4$s',
                    '<div class="wt_info_div"><p>', '<a target="_blank" href="https://www.webtoffee.com/setting-up-webhooks-and-supported-webhooks/">', '</a>', '</p></div>'),
            ),

            'eh_send_receipt' => array(
                'title' => 'Фискализация чека',
                'label' => 'Включить для автоматической регистрации чеков через АТОЛ Онлайн.',
                'type' => 'checkbox',
                'default' => 'no',
            ),

            'eh_atol_pay_sno' => array(
                'title' => 'Система налогооблажения',
                'type' => 'select',
                'options' => array(
                    0 => 'Общая СН',
                    1 => 'Упрощенная СН (доходы)',
                    2 => 'Упрощенная СН (доходы минус расходы)',
                    3 => 'Единый налог на вмененный доход',
                    4 => 'Единый сельскохозяйственный налог',
                    5 => 'Патентная СН',
                ),
                'class' => 'wc-enhanced-select',
                'default' => 0,
                'desc_tip' => 'Выберите систему налогооблажения для отправки в АТОЛ, в режиме отправки чеков.'
            ),

            // VAT RATE
            'eh_atol_pay_tax' => array(
                'title' => 'Ставка НДС',
                'type' => 'select',
                'options' => array(
                    0 => 'Ставка НДС 20%',
                    1 => 'Ставка НДС 10%',
                    2 => 'Ставка НДС расч. 20/120',
                    3 => 'Ставка НДС расч. 10/110',
                    4 => 'Ставка НДС 0%',
                    5 => 'НДС не облагается'
                ),
                'class' => 'wc-enhanced-select',
                'default' => 0,
                'desc_tip' => 'Выберите систему налогооблажения для отправки в АТОЛ, в режиме отправки чеков.'
            ),

            'eh_atol_pay_shipment_tax' => array(
                'title' => 'Ставка НДС для доставки',
                'type' => 'select',
                'options' => array(
                    0 => 'Ставка НДС 20%',
                    1 => 'Ставка НДС 10%',
                    2 => 'Ставка НДС расч. 20/120',
                    3 => 'Ставка НДС расч. 10/110',
                    4 => 'Ставка НДС 0%',
                    5 => 'НДС не облагается'
                ),
                'class' => 'wc-enhanced-select',
                'default' => 0,
                'desc_tip' => 'Выберите систему налогооблажения для отправки в АТОЛ, в режиме отправки чеков.'
            ),

            'eh_atol_payment_method' => array(
                'title' => 'Признак способа расчета',
                'type' => 'select',
                'options' => array(
                    0 => 'Предоплата 100%',
                    1 => 'Частичная предоплата',
                    2 => 'Предварительная оплата (аванс)',
                    3 => 'Полный расчет',
                    4 => 'Частичный расчет и кредит',
                    5 => 'Передача в кредит',
                    6 => 'Оплата кредита',
                ),
                'class' => 'wc-enhanced-select',
                'default' => 0,
            ),

            'eh_atol_payment_subject' => array(
                'title' => 'Признак предмета расчета:',
                'type' => 'select',
                'options' => array(
                    0 => 'О реализуемом товаре, за исключением подакцизного товара',

                    1 => 'О реализуемом подакцизном товаре',
                    2 => 'О выполняемой работе (наименование и иные сведения, описывающие работу)',
                    3 => 'Об оказываемой услуге (наименование и иные сведения, описывающие услугу)',
                    4 => 'О приеме ставок при осуществлении деятельности по проведению азартных игр',
                    5 => 'О выплате денежных средств в виде выигрыша при осуществлении деятельности по проведению азартных игр',
                    6 => 'О приеме денежных средств при реализации лотерейных билетов, электронных лотерейных билетов,
    приеме лотерейных ставок при осуществлении деятельности по проведению лотерей',
                    7 => 'О выплате денежных средств в виде выигрыша при осуществлении деятельности по проведению лотерей',
                    8 => 'О предоставлении прав на использование результатов интеллектуальной деятельности или средств индивидуализации',
                    9 => 'Об авансе, задатке, предоплате, кредите',
                    10 => 'О вознаграждении пользователя, являющегося платежным агентом (субагентом), банковским платежным агентом (субагентом),
     комиссионером, поверенным или иным агентом',
                    11 => 'О взносе в счет оплаты, пени, штрафе, вознаграждении, бонусе и ином аналогичном предмете расчета',
                    12 => 'О предмете расчета, не относящемуся к другим перечисленным предметам расчета',
                    13 => 'О передаче имущественных прав',
                    14 => 'О внереализационном доходе',
                    15 => 'О суммах расходов, платежей и взносов, указанных в подпунктах 2 и 3 пункта Налогового кодекса Российской Федерации, уменьшающих сумму налога',
                    16 => 'О суммах уплаченного торгового сбора',
                    17 => 'О курортном сборе',
                    18 => 'О залоге',
                    19 => 'О суммах произведенных расходов в соответствии со статьей 346.16 Налогового кодекса Российской Федерации, уменьшающих доход',
                    20 => 'О страховых взносах на обязательное пенсионное страхование, уплачиваемых ИП, не производящими выплаты и иные вознаграждения физическим лицам',
                    21 => 'О страховых взносах на обязательное пенсионное страхование, уплачиваемых организациями и ИП, производящими выплаты
     и иные вознаграждения физическим лицам',
                    22 => 'О страховых взносах на обязательное медицинское страхование, уплачиваемых ИП, не производящими выплаты и иные вознаграждения физическим лицам',
                    23 => 'О страховых взносах на обязательное медицинское страхование, уплачиваемые организациями и ИП, производящими выплаты
     и иные вознаграждения физическим лицам',
                    24 => 'О страховых взносах на обязательное социальное страхование на случай временной нетрудоспособности и в связи с материнством,
     на обязательное социальное страхование от несчастных случаев на производстве и профессиональных заболеваний',
                    25 => 'О приеме и выплате денежных средств при осуществлении казино и залами игровых автоматов расчетов
     с использованием обменных знаков игорного заведения',
                ),
                'class' => 'wc-enhanced-select',
                'default' => 0,
            ),


        );
    }

    /**
     * Checks if gateway should be available to use.
     * @since 3.3.4
     */
    public function is_available()
    {
        if ('yes' === $this->enabled && $this->atol_token) {

            if (WC()->cart && 0 < $this->get_order_total() && 0 < $this->max_amount && $this->max_amount < $this->get_order_total()) {
                return false;
            }

            // если отправка яеков включена, но не настроены параметры
            if ($this->atol_send_receipt && ($this->eh_atol_pay_sno === '' || $this->eh_atol_pay_tax === '')) {
                return false;
            }

            return true;
        }
        return false;
    }


    /**
     * Payment form on checkout page
     * @since 3.3.4
     */
    public function payment_fields()
    {
        $user = wp_get_current_user();
        $total = WC()->cart->total;
        $description = $this->get_description();
        echo '<div class="status-box">';

//        if ($this->description) {
//            echo apply_filters('eh_stripe_desc', wpautop(wp_kses_post("<span>" . $this->description . "</span>")));
//        }
        echo "</div>";
    }

    /**
     * loads atol checkout scripts.
     * @since 3.3.4
     */
    public function payment_scripts()
    {
    }

    /**
     * Proceed with current request using new login session (to ensure consistent nonce).
     */
    public function eh_set_cookie_on_current_request($cookie)
    {
        $_COOKIE[LOGGED_IN_COOKIE] = $cookie;
    }


    public function pre_process_handler($order)
    {
        $id = AtolPayment::attachAtolOrderId($order);

        $response = $this->getAtolApiHandler()->paymentStatus($id);

        wc_get_logger()->info(wc_print_r(['pre_checkout_hook :: ', $response->toString()], true));

        if (($response->status !== 'success') && $response->body->errorCode !== 'PAYMENT_NOT_FOUND') {
            wc_get_logger()->error(__METHOD__ . ": " . $response->toString());
            return false;

        }

        if ($response->body->errorCode === 'PAYMENT_NOT_FOUND') {
            return false;
        }

        switch ($response->body->data->status) {
            // Платеж находится в обработке
            case 0:
                // 3DS подтверждение
            case 10:
                // Ошибка оплаты. Повторите попытку
            case 12:
                // генерим заказ заново, тк редирект блокируется по CORS
                AtolPayment::flushAtolCheckoutUrl($order);
                AtolPayment::flushAtolOrderId($order);
                AtolPayment::attachAtolOrderId($order);
//                }
                break;
            case 1:
                // complete order
                $this->completeAtolWpOrder($order);
                return true;

            // очистить детали заказа и отправить платеж заново
            default:
                AtolPayment::flushAtolCheckoutUrl($order);
                AtolPayment::flushAtolOrderId($order);
                AtolPayment::attachAtolOrderId($order);
                break;
        }
        return false;
    }

    /**
     * Process the payment
     */
    public function process_payment($order_id)
    {
        /** @var WC_Order $order */
        $order = wc_get_order($order_id);
        $currency = $order->get_currency();

        if ($currency !== 'RUB') {
            wc_add_notice('Поддерживаются продажи только в рублях.', 'error');
            return [
                'result' => 'false',
                'redirect' => wp_safe_redirect(esc_url_raw(wc_get_checkout_url()))
            ];
        }

        // хук для woocommerce_checkout_create_order не выполняется из формы myAccount
        if ($this->pre_process_handler($order) === true) {
            wc_get_logger()->info("PRE_CHECK -- SUCCESS");

            return [
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }

        $api = $this->getAtolApiHandler();

        $total = self::getAmountInCents($order->get_total(), $currency);

        //check customer token is existed for the logged-in user
        /** @var WP_User $user */
        $user = wp_get_current_user();
        $logged_in_userid = $user->ID;

        // может использоваться как buyerID ??
        $customer_id = get_user_meta($logged_in_userid, '_atol_ch_customer_id', true);

        $receipt = null;

        //only display total amount as default
        if ('yes' === $this->atol_send_receipt) {
            $email = (WC()->version < '2.7.0') ? $order->billing_email : $order->get_billing_email();

            $receipt = AtolReceipt::make()
                ->setBuyer(sanitize_email($email))
                ->setSno($this->eh_atol_pay_sno);

            foreach ($order->get_items() as $item_id => $item) {
                $product = wc_get_product($item->get_product_id());

//                if (WC()->cart->display_cart_ex_tax) {
//                    $item_amount = $item->get_subtotal();
//                } else {
//                    $item_amount = $item->get_subtotal() + $item->get_subtotal_tax();
//                }

                $receiptItem = AtolReceiptItem::make()
                    ->setName(esc_html($item->get_name()))
                    ->setPrice(self::getAmountInCents(wc_get_price_including_tax($product)))
                    ->setQuantity($item->get_quantity())
                    ->setTax($this->eh_atol_pay_tax)
                    ->setPaymentMethod($this->eh_atol_payment_method)
                    ->setPaymentSubject($this->eh_atol_payment_subject);

                $receipt->attachPosition($receiptItem);
            }
            // =============================================

            $receipt->attachPosition(AtolReceiptItem::make()
                ->setName('Доставка')
                ->setPrice(self::getAmountInCents(WC()->cart->get_shipping_total()))
                ->setQuantity(1)
                ->setTax($this->eh_atol_pay_shipment_tax ?: AtolReceiptItem::DEFAULT_SHIPMENT_TAX)
                ->setPaymentSubject(AtolReceiptItem::DEFAULT_SHIPMENT_PAYMENT_SUBJECT));
            // =============================================
        }

        try {
            $atolOrderId = AtolPayment::attachAtolOrderId($order);

            $response = $api->createPayment((int)$total, $order_id, $atolOrderId, $receipt);

            wc_get_logger()->info(__METHOD__ . '::' . esc_html($response->toString()));

            if (in_array($response->body->errorCode, ['VALIDATION_ERROR', 'PAYMENT_EXISTS'])) {

                // очищаем ордер ид и делать повторно запрос
                AtolPayment::flushAtolOrderId($order);

                throw new \RuntimeException(sprintf('Ошибка создания платежа. %s: %s',
                    $response->body->errorCode, $response->body->errorMessage));
            }

            AtolPayment::attachAtolCheckoutUrl($order, $response->body->data->paymentUrl);

            return [
                'result' => 'success',
                'redirect' => $response->body->data->paymentUrl,
            ];

        } catch (\Exception $e) {
            wc_add_notice(esc_html($e->getMessage()), 'error');
            wc_get_logger()->error(__METHOD__ . '::' . esc_html($e->getMessage()));
            return;
        }
    }

    function get_payment_session_checkout_url($session_id, $order)
    {
        return sprintf(
            '#response=%s',
            base64_encode(
                wp_json_encode(
                    array(
                        'session_id' => $session_id,
                        'order_id' => (WC()->version < '2.7.0') ? $order->id : $order->get_id(),
                        'time' => rand(
                            0,
                            999999
                        ),
                    )
                )
            )
        );

    }


    private function returnJson($message, $isSuccess = true, $code = 200)
    {
        $args = [
            "message" => $message,
            "code" => $code
        ];

        $isSuccess ? wp_send_json_success($args) : wp_send_json_error($args);
    }

    /**
     * creates order after checkout session is completed.
     * @since 3.3.4
     */
    public function eh_spg_atol_checkout_order_callback()
    {
        global $woocommerce;

        $isGetRequest = ($_SERVER['REQUEST_METHOD'] === 'GET');

        wc_get_logger()->info(__METHOD__ . ':: ' . $_SERVER['REQUEST_METHOD'] . ' :' . wc_print_r($_REQUEST, true));

        if ($isGetRequest && !$this->verify_nonce(ATOL_PLUGIN_NAME, 'eh_checkout_nonce')) {
            wc_add_notice('Session expired.', 'error');
            wp_safe_redirect(esc_url_raw(wc_get_checkout_url()));
            return;
        }

        $session_id = sanitize_text_field(wp_unslash($_REQUEST['sessionid']));
        $order_id = (int)sanitize_text_field(wp_unslash($_REQUEST['order_id']));
        $order = wc_get_order($order_id);

        if (!$order) {
            wc_add_notice('No order found.', 'error');
            wp_safe_redirect(esc_url_raw(wc_get_checkout_url()));
            return;
        }

        $atolOrderId = sanitize_text_field(wp_unslash($_REQUEST['atol_order_id']));
        if ($order->get_meta('atol_order_id') !== $atolOrderId) {
            wc_add_notice('Failed to verify order id hash.', 'error');
            wp_safe_redirect(wc_get_checkout_url());
            return;
        }
        // ----------------------------------------------

        if (in_array($order->get_status(), ['canceled', 'completed', 'failed', 'refunded'], true)) {
            wp_safe_redirect(esc_url_raw($this->get_return_url($order)));
        }

        $response = $this
            ->getAtolApiHandler()
            ->paymentStatus(
                sanitize_text_field(wp_unslash($_REQUEST['atol_order_id']))
            );

        wc_get_logger()->info('redirect to :: ' . wc_get_checkout_url());

        if ($response->httpCode >= 400) {
            wc_add_notice('Please try again.', 'error');

            wc_get_logger()->error(__METHOD__ . $response->toString());

            wp_safe_redirect(esc_url_raw(wc_get_checkout_url()));
            return;
        }

        // 1 - success
        if ($response->getStatusCode() === 1) {

            $this->completeAtolWpOrder($order, $isGetRequest, !$isGetRequest);

            // 0 - processing,
        } else if ($response->getStatusCode() === 0) {

            wp_safe_redirect(esc_url_raw(wc_get_checkout_url()));

        } else {

            if (!$isGetRequest) {
                $this->returnJson("Что-то пошло не так. Попробуйте еще раз.", false);
            }

            wc_add_notice('Что-то пошло не так. Попробуйте еще раз.', 'error');
            wp_safe_redirect(wc_get_checkout_url());
        }
    }

    /**
     * @param WC_Order $order
     * @return void
     */
    public function completeAtolWpOrder($order, $redirectToCheckout = false, $returnJson = false)
    {
        if (!in_array($order->get_status(), ['processing', 'completed', 'failed', 'refunded'], true)) {
            $order->payment_complete();
            // some notes to customer (replace true with false to make it private)
            $order->add_order_note('Заказ успешно оплачен! Спасибо!', true);

            wc_empty_cart();
        }

        wc_get_logger()->info(sprintf('Order complete id=%s, atolOrderId=%s, isCallback=%s',
                $order->get_id(), AtolPayment::getAtolOrderId($order), $returnJson ? 'true' : 'false')
        );

        if ($returnJson) {
            $this->returnJson("OK");
        }

        if ($redirectToCheckout) {
            wp_safe_redirect(esc_url_raw($this->get_return_url($order)));
        }
    }


    public function eh_spg_atol_cancel_order()
    {
        if (!$this->verify_nonce(ATOL_PLUGIN_NAME, 'eh_checkout_nonce')) {
            die(esc_html_e('Access Denied', 'wp-kamet-atol-gateway'));
        }

        wc_add_notice(esc_html__('You have cancelled ATOL Checkout Session. Please try to process your order again.', 'wp-kamet-atol-gateway'), 'notice');
        wp_redirect(wc_get_checkout_url());
        exit;
    }


    public static function getAmountInCents($total, $currency = '')
    {
        if (!$currency) {
            $currency = get_woocommerce_currency();
        }
        return (int)(round($total, 2) * 100); // In cents
    }

    /**
     * @return AtolApi
     */
    public function getAtolApiHandler()
    {
        return new AtolApi($this->atol_token, AtolEnv::getHost($this->env));
    }

    private function verify_nonce($plugin_id, $nonce_id = '')
    {
        $nonce = (isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '');
        $nonce = (is_array($nonce) ? $nonce[0] : $nonce); //in some cases multiple nonces are declared
        $nonce_id = ($nonce_id == "" ? $plugin_id : $nonce_id); //if nonce id not provided then uses plugin id as nonce id

        if (!(wp_verify_nonce($nonce, $nonce_id))) //verifying nonce
        {
            return false;
        }
        return true;

    }


}