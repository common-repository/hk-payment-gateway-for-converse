<?php


add_action('plugins_loaded', 'hkd_init_converse_gateway_class');
function hkd_init_converse_gateway_class()
{
    global $pluginBaseNameConverse;
    load_plugin_textdomain('hk-payment-gateway-for-converse', false, $pluginBaseNameConverse . '/languages/');

    if (class_exists('WC_Payment_Gateway')) {
        class WC_HKD_Converse_Arca_Gateway extends WC_Payment_Gateway
        {

            private $api_url;
            private $ownerSiteUrl;
            private $pluginDirUrl;
            private $currencies = ['AMD' => '051', 'RUB' => '643', 'USD' => '840', 'EUR' => '978'];
            private $currency_code = '051';

            /**
             * WC_HKD_Converse_Arca_Gateway constructor.
             */
            public function __construct()
            {
                global $woocommerce;
                global $bankErrorCodesByDiffLanguageConverse;
                global $apiUrlConverse;
                global $pluginDirUrlConverse;

                $this->ownerSiteUrl = $apiUrlConverse;
                $this->pluginDirUrl = $pluginDirUrlConverse;

                /* Add support Refund orders */
                $this->supports = [
                    'products',
                    'refunds',
                    'subscriptions',
                    'subscription_cancellation',
                    'subscription_suspension',
                    'subscription_reactivation',
                    'subscription_amount_changes',
                    'subscription_date_changes',
                    'subscription_payment_method_change',
                    'subscription_payment_method_change_customer',
                    'subscription_payment_method_change_admin',
                    'multiple_subscriptions',
                    'gateway_scheduled_payments'
                ];

                $this->id = 'hkd_converse';
                $this->icon = $this->pluginDirUrl . 'assets/images/logo_converse.png';
                $this->has_fields = false;
                $this->method_title = 'Payment Gateway for Converse Bank';
                $this->method_description = 'Pay with Converse payment system. Please note that the payment will be made in Armenian Dram.';

                if (is_admin()) {
                    if (isset($_POST['hkd_converse_checkout_id']) && $_POST['hkd_converse_checkout_id'] != '') {
                        update_option('hkd_converse_checkout_id', esc_attr($_POST['hkd_converse_checkout_id']));
                        $this->update_option('title', __('Pay via credit card', 'hk-payment-gateway-for-converse'));
                        $this->update_option('description', __('Purchase by credit card. Please, note that purchase is going to be made by Armenian drams. ', 'hk-payment-gateway-for-converse'));
                        $this->update_option('save_card_button_text', __('Add a credit card', 'hk-payment-gateway-for-converse'));
                        $this->update_option('save_card_header', __('Purchase safely by using your saved credit card', 'hk-payment-gateway-for-converse'));
                        $this->update_option('save_card_use_new_card', __('Use a new credit card', 'hk-payment-gateway-for-converse'));
                    }
                }

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->language_payment_converse = !empty($this->get_option('language_payment_converse')) ? $this->get_option('language_payment_converse') : 'hy';

                $this->enabled = $this->get_option('enabled');
                $this->hkd_arca_checkout_id = get_option('hkd_converse_checkout_id');
                $this->language = $this->get_option('language');
                $this->secondTypePayment = 'yes' === $this->get_option('secondTypePayment');
                $this->empty_card = 'yes' === $this->get_option('empty_card');
                $this->testmode = 'yes' === $this->get_option('testmode');
                $this->user_name = $this->testmode ? $this->get_option('test_user_name') : $this->get_option('live_user_name');
                $this->password = $this->testmode ? $this->get_option('test_password') : $this->get_option('live_password');
                $this->binding_user_name = $this->get_option('binding_user_name');
                $this->binding_password = $this->get_option('binding_password');
                $this->debug = 'yes' === $this->get_option('debug');
                $this->save_card = 'yes' === $this->get_option('save_card');
                $this->save_card_button_text = !empty($this->get_option('save_card_button_text')) ? $this->get_option('save_card_button_text') : __('Add a credit card', 'hk-payment-gateway-for-converse');
                $this->save_card_header = !empty($this->get_option('save_card_header')) ? $this->get_option('save_card_header') : __('Purchase safely by using your saved credit card', 'hk-payment-gateway-for-converse');
                $this->save_card_use_new_card = !empty($this->get_option('save_card_use_new_card')) ? $this->get_option('save_card_use_new_card') : __('Use a new credit card', 'hk-payment-gateway-for-converse');
                $this->multi_currency = 'yes' === $this->get_option('multi_currency');
                $this->api_url = !$this->testmode ? 'https://ipay.arca.am/payment/rest/' : 'https://ipaytest.arca.am:8445/payment/rest/';
                if ($this->debug) {
                    if (version_compare(WOOCOMMERCE_VERSION, '2.1', '<')) $this->log = $woocommerce->logger(); else $this->log = new WC_Logger();
                }
                if ($this->multi_currency) {
                    $this->currencies = ['AMD' => '051', 'RUB' => '643', 'USD' => '840', 'EUR' => '978'];
                    $wooCurrency = get_woocommerce_currency();
                    $this->currency_code = $this->currencies[$wooCurrency];
                }

                // process the Change Payment "transaction"
                add_action('woocommerce_scheduled_subscription_payment', array($this, 'process_subscription_payment'), 10, 3);


                /**
                 * Success callback url for converse_bank payment api
                 */
                add_action('woocommerce_api_delete_binding_converse_bank', array($this, 'delete_binding_converse_bank'));


                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

                /**
                 * Success callback url for converse payment api
                 */
                add_action('woocommerce_api_converse_successful', array($this, 'webhook_converse_successful'));

                /**
                 * Failed callback url for converse payment api
                 */
                add_action('woocommerce_api_converse_failed', array($this, 'webhook_converse_failed'));
                /**
                 * styles and fonts for converse payment plugin
                 */
                add_action('admin_print_styles', array($this, 'enqueue_stylesheets'));

                /*
                * Add Credit Card Menu in My Account
                */
                if (is_user_logged_in() && $this->save_card && $this->binding_user_name != '' && $this->binding_password != '') {
                    add_filter('query_vars', array($this, 'queryVarsCards'), 0);
                    add_filter('woocommerce_account_menu_items', array($this, 'addCardLinkMenu'));
                    add_action('woocommerce_account_cards_endpoint', array($this, 'CardsPageContent'));
                }

                if (is_admin()) {
                    $this->checkActivation();
                }

                if ($this->secondTypePayment) {
                    add_filter('woocommerce_admin_order_actions', array($this, 'add_custom_order_status_actions_button'), 100, 2);
                    add_action('admin_head', array($this, 'add_custom_order_status_actions_button_css'));
                    add_action('woocommerce_order_status_changed', array($this, 'statusChangeHook'), 10, 3);
                    add_action('woocommerce_order_edit_status', array($this, 'statusChangeHookSubscription'), 10, 2);
                }

                $this->bankErrorCodesByDiffLanguage = $bankErrorCodesByDiffLanguageConverse;

                // WP cron
                add_action('cronCheckOrderConverseBank', array($this, 'cronCheckOrderConverseBank'));
            }

            public function cronCheckOrderConverseBank()
            {
                global $wpdb;
                $orders = $wpdb->get_results("
                        SELECT p.*
                        FROM {$wpdb->prefix}postmeta AS pm
                        LEFT JOIN {$wpdb->prefix}posts AS p
                        ON pm.post_id = p.ID
                        WHERE p.post_type = 'shop_order'
                        AND ( p.post_status = 'wc-on-hold' OR p.post_status = 'wc-pending')
                        AND pm.meta_key = '_payment_method'
                        AND pm.meta_value = 'hkd_converse'
                        ORDER BY pm.meta_value ASC, pm.post_id DESC
                    ");
                foreach ($orders as $order) {
                    $order = wc_get_order($order->ID);
                    $paymentID = get_post_meta($order->ID, 'PaymentID', true);
                    if($paymentID){
                        $response = wp_remote_post($this->api_url . '/getOrderStatus.do?orderId=' .$paymentID. '&language=' . $this->language . '&password=' . $this->password . '&userName=' . $this->user_name);
                        if (!is_wp_error($response)) {
                            $body = json_decode($response['body']);
                            if ($body->OrderStatus == 6){
                                $order->update_status('cancelled');
                                if ($this->debug) $this->log->add($this->id, 'Order status was changed to Failed #'.$order->ID);
                            }
                            if ($body->OrderStatus == 2){
                                $order->update_status('processing');
                                if ($this->debug) $this->log->add($this->id, 'Order status was changed to Processing via cron job #'.$order->ID);
                            }
                        }
                    }
                }
            }

            public function checkActivation()
            {
                try {
                    $today = date('Y-m-d');
                    if(get_option('hkd_check_activation_converse') !== $today) {
                        $payload = ['domain' => $_SERVER['SERVER_NAME'], 'enabled' => $this->enabled];
                        wp_remote_post($this->ownerSiteUrl . 'bank/converse/checkStatusPluginActivation', array(
                            'sslverify' => false,
                            'method' => 'POST',
                            'headers' => array('Accept' => 'application/json'),
                            'body' => $payload
                        ));
                        update_option('hkd_check_activation_converse', $today );
                    }
                } catch (Exception $e) {

                }
            }

            public function statusChangeHookSubscription($order_id, $new_status)
            {
                $order = wc_get_order($order_id);
                if ($this->getPaymentGatewayByOrder($order)->id == 'hkd_converse') {
                    if ($order->get_parent_id() > 0) {
                        if ($new_status == 'active') {
                            return $this->confirmPayment($order_id, $new_status);
                        } else if ($new_status == 'cancelled') {
                            return $this->cancelPayment($order_id);
                        }
                    }
                }
            }

            public function statusChangeHook($order_id, $old_status, $new_status)
            {
                $order = wc_get_order($order_id);
                if ($this->getPaymentGatewayByOrder($order)->id == 'hkd_converse') {
                    if ($new_status == 'completed' ) {
                        return $this->confirmPayment($order_id, $new_status);
                    } else if ($new_status == 'cancelled') {
                        return $this->cancelPayment($order_id);
                    }
                }
            }

            private function getPaymentGatewayByOrder($order)
            {
                return wc_get_payment_gateway_by_order($order);
            }


            public function add_custom_order_status_actions_button_css()
            {
                echo '<style>.column-wc_actions a.cancel::after { content: "\2716" !important; color: red; }</style>';
            }

            public function add_custom_order_status_actions_button($actions, $order)
            {
                if (isset($this->getPaymentGatewayByOrder($order)->id) && $this->getPaymentGatewayByOrder($order)->id == 'hkd_converse') {
                    if ($order->has_status(array('processing'))) {
                        $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
                        $actions['cancelled'] = array(
                            'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=cancelled&order_id=' . $order_id), 'woocommerce-mark-order-status'),
                            'name' => __('Cancel Order', 'woocommerce'),
                            'action' => "cancel custom",
                        );
                    }
                }
                return $actions;
            }


            public function confirmPayment($order_id, $new_status)
            {
                /* $reason */
                $order = wc_get_order($order_id);
                if (!$order->has_status('processing')) {
                    $PaymentID = get_post_meta($order_id, 'PaymentID', true);
                    $isBindingOrder = get_post_meta($order_id, 'isBindingOrder', true);
                    $requestParams = [];
                    $amount = floatval($order->get_total()) * 100;
                    array_push($requestParams, 'amount=' . (int)$amount);
                    array_push($requestParams, 'currency=' . $this->currency_code);
                    array_push($requestParams, 'orderId=' . $PaymentID);
                    if ($isBindingOrder) {
                        array_push($requestParams, 'password=' . $this->binding_password);
                        array_push($requestParams, 'userName=' . $this->binding_user_name);
                    } else {
                        array_push($requestParams, 'password=' . $this->password);
                        array_push($requestParams, 'userName=' . $this->user_name);
                    }
                    array_push($requestParams, 'language=' . $this->language);
                    $response = wp_remote_post(
                        $this->api_url . '/deposit.do?' . implode('&', $requestParams)
                    );
                    if (!is_wp_error($response)) {
                        $body = json_decode($response['body']);

                        if ($body->errorCode == 0) {
                            if ($new_status == 'completed') {
                                $order->update_status('completed');
                            } else {
                                $order->update_status('active');
                            }
                            return true;
                        } else {
                            if ($this->debug) $this->log->add($this->id, 'Order confirm paymend #' . $order_id . '  failed.');
                            if ($new_status == 'completed') {
                                $order->update_status('processing', $body->errorMessage);
                            } else {
                                $order->update_status('on-hold', $body->errorMessage);
                            }
                            die($body->errorMessage);
                        }
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'Order confirm paymend #' . $order_id . '  failed.');
                        if ($new_status == 'completed') {
                            $order->update_status('processing');
                        } else {
                            $order->update_status('on-hold');
                        }
                        die('Order confirm paymend #' . $order_id . '  failed.');
                    }
                }
            }

            /**
             * Process a Cancel Payment if supported.
             *
             * @param int $order_id Order ID.
             * @return bool|WP_Error
             */
            public function cancelPayment($order_id)
            {
                /* $reason */
                $order = wc_get_order($order_id);
                if (!$order->has_status('processing')) {
                    $PaymentID = get_post_meta($order_id, 'PaymentID', true);
                    $isBindingOrder = get_post_meta($order_id, 'isBindingOrder', true);
                    $requestParams = [];
                    array_push($requestParams, 'orderId=' . $PaymentID);
                    if ($isBindingOrder) {
                        array_push($requestParams, 'password=' . $this->binding_password);
                        array_push($requestParams, 'userName=' . $this->binding_user_name);
                    } else {
                        array_push($requestParams, 'password=' . $this->password);
                        array_push($requestParams, 'userName=' . $this->user_name);
                    }
                    $response = wp_remote_post(
                        $this->api_url . '/reverse.do?' . implode('&', $requestParams)
                    );
                    if (!is_wp_error($response)) {
                        $body = json_decode($response['body']);
                        if ($body->errorCode == 0) {
                            $order->update_status('cancelled');
                            return true;
                        } else {
                            if ($this->debug) $this->log->add($this->id, 'Order Cancel paymend #' . $order_id . '  failed.');
                            $order->update_status('processing');
                            die($body->errorMessage);
                        }
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'Order Cancel paymend #' . $order_id . '  failed.');
                        $order->update_status('processing');
                        die('Order Cancel paymend #' . $order_id . '  failed.');
                    }
                }
            }

            /* Refund order process */
            public function process_refund($order_id, $amount = null, $reason = '')
            {
                /* $reason */
                $order = wc_get_order($order_id);
                $requestParams = [];
                array_push($requestParams, 'amount=' . (int)$amount);
                array_push($requestParams, 'currency=' . $this->currency_code);
                array_push($requestParams, 'orderNumber=' . $order_id);
                array_push($requestParams, 'password=' . $this->password);
                array_push($requestParams, 'userName=' . $this->user_name);
                array_push($requestParams, 'language=' . $this->language);
                $response = wp_remote_post(
                    $this->api_url . '/refund.do?' . implode('&', $requestParams)
                );
                if (!is_wp_error($response)) {
                    $body = json_decode($response['body']);
                    if ($body->errorCode == 0) {
                        $order->update_status('refund');
                        return true;
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'Order refund paymend #' . $order_id . ' canceled or failed.');
                        return false;
                    }
                } else {
                    if ($this->debug) $this->log->add($this->id, 'Order refund paymend #' . $order_id . ' canceled or failed.');
                    return false;
                }

            }

            public function queryVarsCards($vars)
            {
                $vars[] = 'cards';
                return $vars;
            }

            public function CardsPageContent()
            {
                $plugin_url = $this->pluginDirUrl;
                wp_enqueue_style('hkd-front-style', $plugin_url . "assets/css/cards.css");
                wp_enqueue_script('hkd-front-js', $plugin_url . "assets/js/cards.js");
                $html = '<div id="hkdigital_binding_info_converse_bank">';
                $bindingInfo = get_user_meta(get_current_user_id(), 'bindingInfo_converse_bank');
                if (is_array($bindingInfo) && count($bindingInfo) > 0) {
                    $html .= '<h4 class="card_payment_title card_page">' . __('Your card list', 'hk-payment-gateway-for-converse') . '</h4>
                              <h2 class="card_payment_second card_page">' . __('You can Delete Cards', 'hk-payment-gateway-for-converse') . '</h2>
                                <ul class="card_payment_list">';
                    foreach ($bindingInfo as $key => $bindingItem) {
                        $html .= '<li class="card_item">
                                        <span class="card_subTitile">
                                        ' . __($bindingItem['cardAuthInfo']['cardholderName'] . ' |  &#8226; &#8226; &#8226; &#8226; ' . $bindingItem['cardAuthInfo']['panEnd'] . ' (expires ' . $bindingItem['cardAuthInfo']['expiration'] . ')', 'hk-payment-gateway-for-converse') . '
                                         </span>
                                         <img src="' . $this->pluginDirUrl . 'assets/images/card_types/' . $bindingItem['cardAuthInfo']['type'] . '.png" class="card_logo big_img" alt="card"/>
                                         <svg  class="svg-trash-converse-bank" data-id="' . $bindingItem['bindingId'] . '" style="display: none" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                            <path fill="#ed2353"
                                                  d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                        </svg>
                                    </li>';
                    }
                    $html .= '</ul>
                            </div>';
                } else {
                    $html .= '<div class="check-box noselect">
                                    <span>
                                      ' . __('No Saved Cards', 'hk-payment-gateway-for-converse') . '
                                    </span>
                                 </div>';
                }
                echo $html;
            }

            public function addCardLinkMenu($items)
            {
                $items['cards'] = 'Credit Cards';
                return $items;
            }


            /*
             * Delete Saved Card AJAX
             */
            public function delete_binding_converse_bank()
            {
                try {
                    $bindingIdForDelete = $_REQUEST['bindingId'];
                    $bindingInfo = get_user_meta(get_current_user_id(), 'bindingInfo_converse_bank');
                    if (is_array($bindingInfo) && count($bindingInfo) > 0) {
                        foreach ($bindingInfo as $key => $item) {
                            if ($item['bindingId'] == $bindingIdForDelete) {
                                unset($bindingInfo[$key]);
                            }
                        }
                        delete_user_meta(get_current_user_id(), 'bindingInfo_converse_bank');
                        if (count($bindingInfo) > 0)
                            add_user_meta(get_current_user_id(), 'bindingInfo_converse_bank', array_values($bindingInfo));
                        $payload = [
                            'userName' => $this->user_name,
                            'password' => $this->password,
                            'bindingId' => $bindingIdForDelete
                        ];
                        wp_remote_post($this->api_url . 'unBindCard.do', array(
                            'method' => 'POST',
                            'body' => http_build_query($payload),
                            'sslverify' => is_ssl(),
                            'timeout' => 60
                        ));
                        $response = ['status' => true];
                    } else {
                        $response = ['status' => false];
                    }
                } catch (Exception $e) {
                    $response = ['status' => false];
                }
                echo json_encode($response);
                exit;
            }

            public function payment_fields()
            {
                $plugin_url = $this->pluginDirUrl;
                wp_enqueue_style('hkd-front-style', $plugin_url . "assets/css/cards.css");
                wp_enqueue_script('hkd-front-js', $plugin_url . "assets/js/cards.js");
                $description = $this->get_description();
                if ($description) {
                    echo wpautop(wptexturize($description));  // @codingStandardsIgnoreLine.
                }
                if (is_user_logged_in() && $this->save_card && $this->binding_user_name != '' && $this->binding_password != '') {
                    $html = '<div id="hkdigital_binding_info_converse_bank">';
                    $bindingInfo = get_user_meta(get_current_user_id(), 'bindingInfo_converse_bank');
                    if (is_array($bindingInfo) && count($bindingInfo) > 0) {
                        $html .= '<h4 class="card_payment_title"> ' . $this->save_card_header . ' </h4>
                                <ul class="card_payment_list">';

                        foreach ($bindingInfo as $key => $bindingItem) {
                            $html .= '<li class="card_item">
                                        <input   id="' . $bindingItem['bindingId'] . '" name="bindingType" value="' . $bindingItem['bindingId'] . '" type="radio" class="input-radio" name="payment_card" >
                                        <label for="' . $bindingItem['bindingId'] . '">
                                        ' . __($bindingItem['cardAuthInfo']['cardholderName'] . ' |  &#8226; &#8226; &#8226; &#8226; ' . $bindingItem['cardAuthInfo']['panEnd'] . ' (expires ' . $bindingItem['cardAuthInfo']['expiration'] . ')') . '
                                         </label>';
                            if ($bindingItem['cardAuthInfo']['type'] != '') {
                                $html .= '<img src="' . $this->pluginDirUrl . 'assets/images/card_types/' . $bindingItem['cardAuthInfo']['type'] . '.png" class="card_logo" alt="card">';
                            }
                            $html .= '<svg  class="svg-trash-converse-bank" data-id="' . $bindingItem['bindingId'] . '" style="display: none" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                            <path fill="#ed2353"
                                                  d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
                                        </svg>
                                    </li>';
                        }
                        $html .= '<li class="card_item">
                                        <input id="payment_newCard" type="radio" class="input-radio" name="bindingType" value="saveCard">
                                        <label for="payment_newCard">
                                         ' . $this->save_card_use_new_card . '
                                         </label>
                                    </li>';
                        $html .= '</ul>
                            </div>';
                    } else {
                        $html .= '<div class="check-box noselect">
                                    <input type="checkbox" id="saveCard_converse_bank" name="bindingType" value="saveCard"/>
                                    <label for="saveCard_converse_bank"> <span class="check"><svg class="svg-check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#ffffff" d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
                                    </svg> </span>
                                      ' . $this->save_card_button_text . '
                                    </label>
                                 </div>';
                    }
                    echo $html;
                }
            }


            public function init_form_fields()
            {
                $debug = __('Log HKD ARCA Gateway events, inside <code>woocommerce/logs/converse.txt</code>', 'hk-payment-gateway-for-converse');
                if (!version_compare(WOOCOMMERCE_VERSION, '2.0', '<')) {
                    if (version_compare(WOOCOMMERCE_VERSION, '2.2.0', '<'))
                        $debug = str_replace('converse', $this->id . '-' . date('Y-m-d') . '-' . sanitize_file_name(wp_hash($this->id)), $debug);
                    elseif (function_exists('wc_get_log_file_path')) {
                        $debug = str_replace('woocommerce/logs/converse.txt', '<a href="/wp-admin/admin.php?page=wc-status&tab=logs&log_file=' . $this->id . '-' . date('Y-m-d') . '-' . sanitize_file_name(wp_hash($this->id)) . '-log" target="_blank">' . __('here', 'hk-payment-gateway-for-converse') . '</a>', $debug);
                    }
                }

                $this->form_fields = array(
                    'language_payment_converse' => array(
                        'title' => __('Plugin language', 'hk-payment-gateway-for-converse'),
                        'type' => 'select',
                        'options' => [
                            'hy' => 'Հայերեն',
                            'ru_RU' => 'Русский',
                            'en_US' => 'English',
                        ],
                        'description' => __('Here you can change the language of the plugin control panel.', 'hk-payment-gateway-for-converse'),
                        'default' => 'hy',
                        'desc_tip' => true,
                    ),
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'hk-payment-gateway-for-converse'),
                        'label' => __('Enable payment gateway', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'description' => '',
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __('Title', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'description' => __('User (website visitor) sees this title on order registry page as a title for purchase option.', 'hk-payment-gateway-for-converse'),
                        'default' => __('Pay via credit card', 'hk-payment-gateway-for-converse'),
                        'desc_tip' => true,
                        'placeholder' => __('Type the title', 'hk-payment-gateway-for-converse')
                    ),
                    'description' => array(
                        'title' => __('Description', 'hk-payment-gateway-for-converse'),
                        'type' => 'textarea',
                        'description' => __('User (website visitor) sees this description on order registry page in bank purchase option.', 'hk-payment-gateway-for-converse'),
                        'default' => __('Purchase by  credit card. Please, note that purchase is going to be made by Armenian drams. ', 'hk-payment-gateway-for-converse'),
                        'desc_tip' => true,
                        'placeholder' => __('Type the description', 'hk-payment-gateway-for-converse')
                    ),
                    'language' => array(
                        'title' => __('Language', 'hk-payment-gateway-for-converse'),
                        'type' => 'select',
                        'options' => [
                            'hy' => 'Հայերեն',
                            'ru' => 'Русский',
                            'en' => 'English',
                        ],
                        'description' => __('Here interface language of bank purchase can be regulated', 'hk-payment-gateway-for-converse'),
                        'default' => 'hy',
                        'desc_tip' => true,
                    ),
                    'multi_currency' => array(
                        'title' => __('Multi-Currency', 'hk-payment-gateway-for-converse'),
                        'label' => __('Enable Multi-Currency', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'description' => __('This action, if permitted by the bank, enables to purchase by multiple currencies', 'hk-payment-gateway-for-converse'),
                        'default' => 'no',
                        'desc_tip' => true,
                    ),
                    'debug' => array(
                        'title' => __('Debug Log', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'label' => __('Enable debug mode', 'hk-payment-gateway-for-converse'),
                        'default' => 'no',
                        'description' => $debug,
                    ),
                    'testmode' => array(
                        'title' => __('Test mode', 'hk-payment-gateway-for-converse'),
                        'label' => __('Enable test Mode', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'description' => __('To test the testing version login and password provided by the bank should be typed', 'hk-payment-gateway-for-converse'),
                        'default' => 'yes',
                        'desc_tip' => true,
                    ),
                    'test_user_name' => array(
                        'title' => __('Test User Name', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'class' => 'testModeInfoConverse hiddenValueConverse'
                    ),
                    'test_password' => array(
                        'title' => __('Test Password', 'hk-payment-gateway-for-converse'),
                        'type' => 'password',
                        'placeholder' => __('Enter password', 'hk-payment-gateway-for-converse'),
                        'class' => 'testModeInfoConverse hiddenValueConverse'
                    ),
                    'secondTypePayment' => array(
                        'title' => __('Two-stage Payment', 'hk-payment-gateway-for-converse'),
                        'label' => __('Enable payment confirmation function', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'description' => __('two-stage: when the payment amount is first blocked on the buyer’s account and then at the second stage is withdrawn from the account', 'hk-payment-gateway-for-converse'),
                        'default' => 'no',
                        'desc_tip' => true,
                    ),
                    'save_card' => array(
                        'title' => __('Save Card Admin', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'label' => __('Enable "Save Card" function', 'hk-payment-gateway-for-converse'),
                        'default' => 'no',
                        'desc_tip' => true,
                        'description' => __('Enable Save Card', 'hk-payment-gateway-for-converse'),
                    ),
                    'save_card_button_text' => array(
                        'title' => __('New binding card text', 'hk-payment-gateway-for-converse'),
                        'placeholder' => __('Type the save card checkbox text', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'default' => __('Add a credit card', 'hk-payment-gateway-for-converse'),
                        'desc_tip' => true,
                        'description' => ' ',
                        'class' => 'saveCardInfoConverse hiddenValueConverse',
                    ),
                    'save_card_header' => array(
                        'title' => __('Save card description text', 'hk-payment-gateway-for-converse'),
                        'placeholder' => __('Type the save card description text', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'default' => __('Purchase safely by using your saved credit card', 'hk-payment-gateway-for-converse'),
                        'desc_tip' => true,
                        'description' => ' ',
                        'class' => 'saveCardInfoConverse hiddenValueConverse',
                    ),
                    'save_card_use_new_card' => array(
                        'title' => __('Use new card text', 'hk-payment-gateway-for-converse'),
                        'placeholder' => __('Type the use new card text', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'default' => __('Use a new credit card', 'hk-payment-gateway-for-converse'),
                        'desc_tip' => true,
                        'description' => ' ',
                        'class' => 'saveCardInfoConverse hiddenValueConverse'
                    ),
                    'binding_user_name' => array(
                        'title' => __('Binding User Name', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'class' => 'saveCardInfoConverse hiddenValueConverse'
                    ),
                    'binding_password' => array(
                        'title' => __('Binding Password', 'hk-payment-gateway-for-converse'),
                        'type' => 'password',
                        'placeholder' => __('Enter password', 'hk-payment-gateway-for-converse'),
                        'class' => 'saveCardInfoConverse hiddenValueConverse'
                    ),
                    'live_settings' => array(
                        'title' => __('Live Settings', 'hk-payment-gateway-for-converse'),
                        'type' => 'hidden'
                    ),
                    'live_user_name' => array(
                        'title' => __('User Name', 'hk-payment-gateway-for-converse'),
                        'type' => 'text',
                        'placeholder' => __('Type the user name', 'hk-payment-gateway-for-converse')
                    ),
                    'live_password' => array(
                        'title' => __('Password', 'hk-payment-gateway-for-converse'),
                        'type' => 'password',
                        'placeholder' => __('Type the password', 'hk-payment-gateway-for-converse')
                    ),
                    'useful_functions' => array(
                        'title' => __('Useful functions', 'hk-payment-gateway-for-converse'),
                        'type' => 'hidden'
                    ),
                    'empty_card' => array(
                        'title' => __('Cart totals', 'hk-payment-gateway-for-converse'),
                        'label' => __('Activate shopping cart function', 'hk-payment-gateway-for-converse'),
                        'type' => 'checkbox',
                        'description' => __('This feature ensures that the contents of the shopping cart are available at the time of order registration if the site buyer decides to change the payment method.', 'hk-payment-gateway-for-converse'),
                        'default' => 'no',
                        'desc_tip' => true,
                    ),
                    'links' => array(
                        'title' => __('Links', 'hk-payment-gateway-for-converse'),
                        'type' => 'hidden'
                    ),
                );
            }

            public function process_payment($order_id)
            {
                global $woocommerce;
                if (isset($_REQUEST['bindingType'])) $bindingType = $_REQUEST['bindingType'];
                $order = wc_get_order($order_id);
                $amount = floatval($order->get_total()) * 100;
                $requestParams = [];
                array_push($requestParams, 'amount=' . (int)$amount);
                array_push($requestParams, 'currency=' . $this->currency_code);
                array_push($requestParams, 'orderNumber=' . $order_id);
                array_push($requestParams, 'language=' . $this->language);
                if (isset($bindingType) && $bindingType != 'saveCard') {
                    array_push($requestParams, 'password=' . $this->binding_password);
                    array_push($requestParams, 'userName=' . $this->binding_user_name);
                } else {
                    array_push($requestParams, 'password=' . $this->password);
                    array_push($requestParams, 'userName=' . $this->user_name);
                }
                array_push($requestParams, 'description=order number ' . $order_id);
                array_push($requestParams, 'returnUrl=' . get_site_url() . '/wc-api/converse_successful?order=' . $order_id);
                array_push($requestParams, 'failUrl=' . get_site_url() . '/wc-api/converse_failed?order=' . $order_id);
                array_push($requestParams, 'jsonParams={"FORCE_3DS2":"true"}');
                if (isset($bindingType) && $bindingType != 'saveCard') {
                    array_push($requestParams, 'clientId=' . get_current_user_id());
                    $response = ($this->secondTypePayment) ? wp_remote_post(
                        $this->api_url . '/registerPreAuth.do?' . implode('&', $requestParams)
                    ) : wp_remote_post(
                        $this->api_url . '/register.do?' . implode('&', $requestParams)
                    );

                    $body = json_decode($response['body']);
                    $payload = [
                        'userName' => $this->binding_user_name,
                        'password' => $this->binding_password,
                        'mdOrder' => $body->orderId,
                        'bindingId' => $_REQUEST['bindingType']
                    ];
                    $response = wp_remote_post($this->api_url . '/paymentOrderBinding.do', array(
                        'method' => 'POST',
                        'body' => http_build_query($payload),
                        'sslverify' => is_ssl(),
                        'timeout' => 60
                    ));
                    $body = json_decode($response['body']);
                    if ($body->errorCode == 0) {
                        $order->update_status('processing');
                        $parts = parse_url($body->redirect);
                        parse_str($parts['query'], $query);
                        update_post_meta($order_id, 'PaymentID', $query['orderId']);
                        update_post_meta($order_id, 'isBindingOrder', 1);
                        wc_reduce_stock_levels($order_id);
                        if(!$this->empty_card) {
                            $woocommerce->cart->empty_cart();
                        }
                        return array('result' => 'success', 'redirect' => $body->redirect);
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'Order payment #' . $order_id . ' canceled or failed.');
                        $order->update_status('failed', $body->errorMessage);
                        wc_add_notice(__('Please try again.', 'hk-payment-gateway-for-converse'), 'error');
                    }
                }
                update_post_meta($_REQUEST['order'], 'isBindingOrder', 0);
                if (($this->save_card && $this->binding_user_name != '' && $this->binding_password != '' && is_user_logged_in() && isset($bindingType) && $bindingType == 'saveCard') || (function_exists('wcs_get_subscriptions_for_order') && !empty(wcs_get_subscriptions_for_order($order_id, array('order_type' => 'any'))))) {
                    array_push($requestParams, 'clientId=' . get_current_user_id());
                }
                $response = ($this->secondTypePayment) ? wp_remote_post(
                    $this->api_url . '/registerPreAuth.do?' . implode('&', $requestParams)
                ) : wp_remote_post(
                    $this->api_url . '/register.do?' . implode('&', $requestParams)
                );
                if (!is_wp_error($response)) {
                    $body = json_decode($response['body']);
                    if ($body->errorCode == 0) {
                        $order->update_status('pending');
                        wc_reduce_stock_levels($order_id);
                        if(!$this->empty_card) {
                            $woocommerce->cart->empty_cart();
                        }
                        return array('result' => 'success', 'redirect' => $body->formUrl);
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'Order payment #' . $order_id . ' canceled or failed.');
                        $order->update_status('failed', $body->errorMessage);
                        wc_add_notice(__('Please try again.', 'hk-payment-gateway-for-converse'), 'error');
                    }
                } else {
                    if ($this->debug) $this->log->add($this->id, 'Order payment #' . $order_id . ' canceled or failed.');
                    $order->update_status('failed');
                    wc_add_notice(__('Connection error.', 'hk-payment-gateway-for-converse'), 'error');
                }
            }

            public function enqueue_stylesheets()
            {
                $plugin_url = $this->pluginDirUrl;
                wp_enqueue_script('hkd-converse-front-admin-js', $plugin_url . "assets/js/admin.js");
                wp_localize_script('hkd-converse-front-admin-js', 'myScriptConverse', array(
                    'pluginsUrl' => $plugin_url,
                ));
                wp_enqueue_style('hkd-converse-style', $plugin_url . "assets/css/style.css");
                wp_enqueue_style('hkd-style-awesome', $plugin_url . "assets/css/font_awesome.css");
            }

            public function process_subscription_payment($order_id)
            {
                $order = wc_get_order($order_id);
                if ($this->getPaymentGatewayByOrder($order)->id == 'hkd_converse') {
                    $bindingInfo = get_user_meta($order->get_user_id(), 'recurringChargeCONVERSE' . (int)$order->get_parent_id());

                    $amount = floatval($order->get_total()) * 100;
                    $requestParams = [];
                    array_push($requestParams, 'amount=' . (int)$amount);
                    array_push($requestParams, 'currency=' . $this->currency_code);
                    array_push($requestParams, 'orderNumber=' .  rand(10000000, 99999999));
                    array_push($requestParams, 'language=' . $this->language);
                    array_push($requestParams, 'password=' . $this->binding_password);
                    array_push($requestParams, 'userName=' . $this->binding_user_name);
                    array_push($requestParams, 'description=order number' . $order_id);
                    array_push($requestParams, 'returnUrl=' . get_site_url() . '/wc-api/converse_successful?order=' . $order_id);
                    array_push($requestParams, 'failUrl=' . get_site_url() . '/wc-api/converse_failed?order=' . $order_id);
                    array_push($requestParams, 'clientId=' . get_current_user_id());
                    array_push($requestParams, 'jsonParams={"FORCE_3DS2":"true"}');
                    $response = ($this->secondTypePayment) ? wp_remote_post(
                        $this->api_url . '/registerPreAuth.do?' . implode('&', $requestParams)
                    ) : wp_remote_post(
                        $this->api_url . '/register.do?' . implode('&', $requestParams)
                    );
                    $body = json_decode($response['body']);
                    update_post_meta($order_id, 'PaymentID', $body->orderId);
                    $payload = [
                        'userName' => $this->binding_user_name,
                        'password' => $this->binding_password,
                        'mdOrder' => $body->orderId,
                        'bindingId' => $bindingInfo[0]['bindingId']
                    ];
                    $response = wp_remote_post($this->api_url . '/paymentOrderBinding.do', array(
                        'method' => 'POST',
                        'body' => http_build_query($payload),
                        'sslverify' => is_ssl(),
                        'timeout' => 60
                    ));
                    if (!is_wp_error($response)) {
                        $body = json_decode($response['body']);
                        if ($body->errorCode == 0) {
                            if ($this->secondTypePayment) {
                                $order->update_status('on-hold');
                            } else {
                                $order->update_status('active');
                            }
                            $parts = parse_url($body->redirect);
                            parse_str($parts['query'], $query);
                            update_post_meta($order_id, 'isBindingOrder', 1);
                            return true;
                        } else {
                            if ($this->debug) $this->log->add($this->id, 'Order payment #' . $order_id . ' canceled or failed.');
                            $order->update_status('pending-cancel');
                            echo "<pre>";
                            print_r($body);
                            echo "error";
                            exit;
                        }
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'something went wrong with Arca callback.');
                        $order->update_status('pending-cancel', 'WP Error binding payment');
                        echo "error";
                        exit;
                    }
                }
            }

            public function admin_options()
            {
                $validate = $this->validateFields();

                if (!$validate['success']) {
                    $message = $validate['message'];
                }

                if (!empty($message)) { ?>
                    <div id="message" class="<?= ($validate['success']) ? 'updated' : 'error' ?> fade">
                        <p><?php echo $message; ?></p>
                    </div>
                <?php } ?>
                <div class="wrap-converse wrap-content wrap-content-hkd"
                     style="width: 45%;display: inline-block;vertical-align: text-bottom;">
                    <h4><?= __('ONLINE PAYMENT GATEWAY', 'hk-payment-gateway-for-converse') ?></h4>
                    <h3><?= __('CONVERSE BANK', 'hk-payment-gateway-for-converse') ?></h3>
                    <?php if (!$validate['success']): ?>
                        <div style="width: 400px; padding-bottom: 60px">
                            <p style="padding-bottom: 10px"><?php echo __('Before using the plugin, please contact the bank to receive respective regulations.', 'hk-payment-gateway-for-converse'); ?></p>
                        </div>
                    <?php endif; ?>
                    <table class="form-table">
                        <?php if ($validate['success']) {
                            $this->generate_settings_html(); ?>
                            <tr valign="top">
                                <th scope="row">Converse callback Url Success</th>
                                <td><?= get_site_url() ?>/wc-api/converse_successful</td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Converse callback Url Failed</th>
                                <td><?= get_site_url() ?>/wc-api/converse_failed</td>
                            </tr>
                        <?php } else { ?>
                            <tr valign="top">
                                <td style="display: block;width: 100%;padding-left: 0 !important;">
                                    <label style="display: block;padding-bottom: 3px"
                                           for="woocommerce_hkd_converse_language_payment_converse"><?php echo __('Plugin language', 'hk-payment-gateway-for-converse') ?></label>
                                    <fieldset>
                                        <select class="select " name="woocommerce_hkd_converse_language_payment_converse"
                                                id="woocommerce_hkd_converse_language_payment_converse" style="">
                                            <option value="hy" <?php if ($this->language_payment_converse == 'hy'): ?> selected <?php endif; ?> >
                                                Հայերեն
                                            </option>
                                            <option value="ru_RU" <?php if ($this->language_payment_converse == 'ru_RU'): ?> selected <?php endif; ?> >
                                                Русский
                                            </option>
                                            <option value="en_US" <?php if ($this->language_payment_converse == 'en_US'): ?> selected <?php endif; ?> >
                                                English
                                            </option>
                                        </select>
                                    </fieldset>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td style="display: block;width: 100%;padding-left: 0 !important;">
                                    <label style="display: block;padding-bottom: 3px"><?php echo __('Identification password', 'hk-payment-gateway-for-converse'); ?></label>
                                    <input type="text" placeholder="<?php echo __('Example Armeconomgayudcsu14', 'wc-hkdigital-armeconombank-gateway')?>"
                                           name="hkd_converse_checkout_id" id="hkd_arca_checkout_id"
                                           value="<?php echo $this->hkd_arca_checkout_id; ?>"/>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                    <?php if (!$validate['success']): ?>
                        <div>
                            <div style="margin-top: 190px;margin-bottom: 15px;">
                                <i style="font-size: 18px" class="phone-icon-2 fa fa-info-circle"></i>
                                <span style="width: calc(400px - 25px);display: inline-block;vertical-align: middle;font-size: 14px;font-weight:600;font-style: italic;font-family: sans-serif;">
                                    <?php echo __('To see the identification terms, click', 'hk-payment-gateway-for-converse'); ?> <a
                                        class="informationLink" target="_blank"
                                        href="https://hkdigital.am"><?php echo __('here', 'hk-payment-gateway-for-converse'); ?></a>
                        </span>
                            </div>
                            <div style="font-size: 16px;font-weight: 600;margin-top: 30px;margin-bottom: 10px;">
                                <?php echo __('Useful links', 'hk-payment-gateway-for-converse'); ?>
                            </div>
                            <div class="converse_bank_info">
                                <ul style="list-style: none;margin: 0; padding: 0;font-size: 16px;font-weight: 600;font-style: italic;">
                                    <li>
                                        <i class="phone-icon-2 fa fa-link"></i>
                                        <a target="_blank" href="javascript:">
                                            <?php echo __('See bank offer', 'hk-payment-gateway-for-converse'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <i class="phone-icon-2 fa fa-link"></i>
                                        <a target="_blank" href="javascript:">
                                            <?php echo __('See plugin possibilities', 'hk-payment-gateway-for-converse'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <i class="phone-icon-2 fa fa-link"></i>
                                        <a target="_blank" href="javascript:">
                                            <?php echo __('See terms of usage', 'hk-payment-gateway-for-converse'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>


                </div>
                <div class="wrap-converse wrap-content wrap-content-hkd"
                     style="width: 29%;display: inline-block;position: absolute; padding-top: 75px;">
                    <div class="wrap-content-hkd-400px">
                        <img src="<?= $this->pluginDirUrl ?>assets/images/converse.png">
                        <div class="wrap-content-hkd-info">
                            <h2><?php echo __('Payment system', 'hk-payment-gateway-for-converse'); ?></h2>
                            <div class="wrap-content-info">
                                <div class="phone-icon icon"><i class="fa fa-phone"></i></div>
                                <p><a href="tel:+37455008298">055008298</a></p>
                                <div class="mail-icon icon"><i class="fa fa-envelope"></i></div>
                                <p><a href="mailto:onlinepayments@conversebank.am">onlinepayments@conversebank.am</a>
                                </p>
                                <div class="mail-icon icon"><i class="fa fa-link"></i></div>
                                <p><a target="_blank" href="https://www.conversebank.am">conversebank.am</a></p>
                            </div>
                        </div>
                    </div>
                    <div class="wrap-content-hkd-400px">
                        <img width="341" height="140"
                             src="<?= $this->pluginDirUrl ?>assets/images/hkserperator.png">
                    </div>
                    <div class=" wrap-content-hkd-400px">
                        <img src="<?= $this->pluginDirUrl ?>assets/images/logo_hkd.png">
                        <div class="wrap-content-hkd-info">
                            <div class="wrap-content-info">
                                <div class="phone-icon-2 icon"><i class="fa fa-phone"></i>
                                </div>
                                <p><a href="tel:+37460777999">060777999</a></p>
                                <div class="phone-icon-2 icon"><i class="fa fa-phone"></i>
                                </div>
                                <p><a href="tel:+37433779779">033779779</a></p>
                                <div class="mail-icon-2 icon"><i class="fa fa-envelope"></i></div>
                                <p><a href="mailto:support@hkdigital.am">support@hkdigital.am</a></p>
                                <div class="mail-icon-2 icon"><i class="fa fa-link"></i></div>
                                <p><a target="_blank" href="https://www.hkdigital.am">hkdigital.am</a></p>
                            </div>
                        </div>
                    </div>
                </div>

            <?php }

            /**
             * Init settings for gateways.
             */
            public function init_settings()
            {
                parent::init_settings();
                $this->enabled = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
            }


            /**
             * @return array|mixed|object
             */
            public function validateFields()
            {
                $go = get_option('hkdump');
                $wooCurrency = get_woocommerce_currency();

                if (!isset($this->currencies[$wooCurrency])) {
                    $this->update_option('enabled', 'no');
                    return ['message' => 'Դուք այժմ օգտագործում եք ' . $wooCurrency . ' արժույթը, այն չի սպասարկվում բանկի կողմից։
                                          Հասանելի արժույթներն են ՝  ' . implode(', ', array_keys($this->currencies)), 'success' => false,  'err_msg' => 'currency_error'];
                }
                if ($this->hkd_arca_checkout_id == '') {
                    if (!empty($go)) {
                        update_option('hkdump', 'no');
                    } else {
                        add_option('hkdump', 'no');
                    };
                    $this->update_option('enabled', 'no');
                    return ['message' => __('You must fill token', 'hk-payment-gateway-for-converse'), 'success' => false,];
                }
                $ch = curl_init($this->ownerSiteUrl .
                    'bank/converse/checkApiConnection');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['checkIn' => true]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                    ]
                );
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                $res = curl_exec($ch);
                curl_close($ch);
                if ($res) {
                    $response = wp_remote_post($this->ownerSiteUrl .
                        'bank/converse/checkActivation', [  'headers'     => array('Accept' => 'application/json'),'sslverify' => false,'body' => ['domain' => $_SERVER['SERVER_NAME'], 'checkoutId' => $this->hkd_arca_checkout_id, 'lang' => $this->language_payment_converse]]);
                    if (!is_wp_error($response)) {
                        if (!empty($go)) {
                            update_option('hkdump', 'yes');
                        } else {
                            add_option('hkdump', 'yes');
                        };
                        return json_decode($response['body'], true);
                    } else {
                        if (!empty($go)) {
                            update_option('hkdump', 'no');
                        } else {
                            add_option('hkdump', 'no');
                        };
                        $this->update_option('enabled', 'no');
                        return ['message' => __('Token not valid', 'hk-payment-gateway-for-converse'), 'success' => false,];
                    }
                } else {
                    if (get_option('hkdump') == 'yes') {
                        return ['message' => '', 'status' => 'success'];
                    } else {
                        return ['message' => __('You must fill token', 'hk-payment-gateway-for-converse'), 'success' => false,];
                    }
                }

            }


            public function webhook_converse_successful()
            {
                global $woocommerce;
                if($this->empty_card) {
                    $woocommerce->cart->empty_cart();
                }
                if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
                    $isBindingOrder = get_post_meta($_REQUEST['order'], 'isBindingOrder', true);
                    if ($isBindingOrder) {
                        $response = wp_remote_post($this->api_url . '/getOrderStatusExtended.do?orderId=' . sanitize_text_field($_REQUEST['orderId']) . '&language=' . $this->language . '&password=' . $this->binding_password . '&userName=' . $this->binding_user_name);
                    } else {
                        $response = wp_remote_post($this->api_url . '/getOrderStatusExtended.do?orderId=' . sanitize_text_field($_REQUEST['orderId']) . '&language=' . $this->language . '&password=' . $this->password . '&userName=' . $this->user_name);
                    }
                    $body = json_decode($response['body']);
                    $user_meta_key = 'bindingInfo_converse_bank';
                    if(isset($body->bindingInfo->bindingId)){
                        add_user_meta(get_current_user_id(), 'recurringChargeCONVERSE' . $_REQUEST['order'], ['bindingId' => $body->bindingInfo->bindingId]);
                    }
                    if (isset($body->orderStatus) && $body->orderStatus == 2) {
                        if ($this->save_card && $this->binding_user_name != '' && $this->binding_password != '' && is_user_logged_in() && isset($body->bindingInfo) && isset($body->cardAuthInfo)) {

                            $bindingInfo = get_user_meta(get_current_user_id(), 'bindingInfo_converse_bank');
                            $findCard = false;
                            if (is_array($bindingInfo) && count($bindingInfo) > 0) {
                                foreach ($bindingInfo as $key => $bindingItem) {
                                    if ($bindingItem['cardAuthInfo']['expiration'] == substr($body->cardAuthInfo->expiration, 0, 4) . '/' . substr($body->cardAuthInfo->expiration, 4) && $bindingItem['cardAuthInfo']['panEnd'] == substr($body->cardAuthInfo->pan, -4)) {
                                        $findCard = true;
                                    }
                                }
                            }
                            if (!$findCard) {
                                $metaArray = array(
                                    'active' => true,
                                    'bindingId' => $body->bindingInfo->bindingId,
                                    'cardAuthInfo' => [
                                        'expiration' => substr($body->cardAuthInfo->expiration, 0, 4) . '/' . substr($body->cardAuthInfo->expiration, 4),
                                        'cardholderName' => $body->cardAuthInfo->cardholderName,
                                        'pan' => substr($body->cardAuthInfo->pan, 0, 4) . str_repeat('*', strlen($body->cardAuthInfo->pan) - 8) . substr($body->cardAuthInfo->pan, -4),
                                        'panEnd' => substr($body->cardAuthInfo->pan, -4),
                                        'type' => $this->getCardType($body->cardAuthInfo->pan)
                                    ],
                                );
                                $user_id = $body->bindingInfo->clientId;
                                add_user_meta($user_id, $user_meta_key, $metaArray);
                            }
                        }
                        update_post_meta($_REQUEST['order'], 'PaymentID', $_REQUEST['orderId']);

                        $order = wc_get_order(sanitize_text_field($_REQUEST['order']));
                        $order->update_status('processing');
                        if ($this->debug) $this->log->add($this->id, 'Order #' . sanitize_text_field($_REQUEST['order']) . ' successfully added to processing');
                        echo $this->get_return_url($order);
                        wp_redirect($this->get_return_url($order));
                        exit;
                    }else{
                        $order = wc_get_order(sanitize_text_field($_REQUEST['order']));
                        $order->update_status('failed');
                        $order->add_order_note($body->errorMessage, true);
                        if ($this->debug) $this->log->add($this->id, 'something went wrong with Arca callback: #' . sanitize_text_field($_GET['order']));
                    }
                }

                if (isset($_REQUEST['orderId']) && $_REQUEST['orderId'] !== '') {

                    $response = wp_remote_post($this->api_url . '/getOrderStatus.do?orderId=' . sanitize_text_field($_REQUEST['orderId']) . '&language=' . $this->language . '&password=' . $this->password . '&userName=' . $this->user_name);

                    if (!is_wp_error($response)) {
                        $body = json_decode($response['body']);
                        if ($body->errorCode == 0) {
                            $order = wc_get_order($body->OrderNumber);
                            $order->update_status('processing');
                            if ($this->debug) $this->log->add($this->id, 'Order #' . $body->OrderNumber . ' successfully added to processing.');
                            wp_redirect($this->get_return_url($order));
                            exit;
                        } else {
                            if ($this->debug) $this->log->add($this->id, 'something went wrong with Converse Bank callback: #' . sanitize_text_field($_REQUEST['orderId']) . '. Error: ' . $body->errorMessage);
                        }
                    } else {
                        if ($this->debug) $this->log->add($this->id, 'something went wrong with Converse Bank callback: #' . sanitize_text_field($_REQUEST['orderId']));
                    }
                }

                wc_add_notice(__('Please try again later.', 'hk-payment-gateway-for-converse'), 'error');
                wp_redirect(get_permalink(get_option('woocommerce_checkout_page_id')));
                exit;
            }

            public function webhook_converse_failed()
            {
                global $woocommerce;
                if($this->empty_card) {
                    $woocommerce->cart->empty_cart();
                }
                if (isset($_GET['orderId']) && $_GET['orderId'] !== '') {
                    $order = wc_get_order(sanitize_text_field($_GET['order']));
                    if ($this->debug) $this->log->add($this->id, 'Order #' . sanitize_text_field($_GET['order']) . ' failed.');
                    $response = wp_remote_post($this->api_url . '/getOrderStatus.do?orderId=' . sanitize_text_field($_GET['orderId']) . '&language=' . $this->language . '&password=' . $this->password . '&userName=' . $this->user_name);
                    if (!is_wp_error($response)) {
                        $body = json_decode($response['body']);
                        if (isset($this->bankErrorCodesByDiffLanguage[$this->language][$body->SvfeResponse])) {
                            $order = new WC_Order(sanitize_text_field($_GET['order']));
                            $errMessage = $this->bankErrorCodesByDiffLanguage[$this->language][$body->SvfeResponse];
                            $order->add_order_note($errMessage, true);
                            $order->update_status('failed');
                            if ($this->debug) $this->log->add($this->id, 'something went wrong with Arca callback: #' . sanitize_text_field($_GET['orderId']) . '. Error: ' . $this->bankErrorCodesByDiffLanguage[$this->language][$body->SvfeResponse]);
                            update_post_meta(sanitize_text_field($_GET['order']), 'FailedMessageConverse', $this->bankErrorCodesByDiffLanguage[$this->language][$body->SvfeResponse]);
                        } else {
                            $order->update_status('failed');
                            update_post_meta(sanitize_text_field($_GET['order']), 'FailedMessageConverse', __('Please try again later.', 'hk-payment-gateway-for-converse'));
                        }
                        if ($this->debug) $this->log->add($this->id, 'something went wrong with Arca callback: #' . sanitize_text_field($_GET['orderId']) . '. Error: ' . $body->errorMessage);
                        wp_redirect($this->get_return_url($order));
                        exit;
                    } else {
                        $order->update_status('failed');
                        wc_add_notice(__('Please try again later.', 'hk-payment-gateway-for-converse'), 'error');
                        if ($this->debug) $this->log->add($this->id, 'something went wrong with Arca callback: #' . sanitize_text_field($_GET['orderId']));
                    }
                }
                wp_redirect(get_permalink(get_option('woocommerce_checkout_page_id')));
                exit;
            }

            public function getCardType($cardNumber)
            {
                $explodedCardNumber = explode('*', $cardNumber);
                $explodedCardNumber[1] = mt_rand(100000, 999999);
                $cardNumber = implode('', $explodedCardNumber);
                $type = '';
                $regex = [
                    'electron' => '/^(4026|417500|4405|4508|4844|4913|4917)\d+$/',
                    'maestro' => '/^(5018|5020|5038|5612|5893|6304|6759|6761|6762|6763|0604|6390)\d+$/',
                    'dankort' => '/^(5019)\d+$/',
                    'interpayment' => '/^(636)\d+$/',
                    'unionpay' => '/^(62|88)\d+$/',
                    'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
                    'master_card' => '/^5[1-5][0-9]{14}$/',
                    'amex' => '/^3[47][0-9]{13}$/',
                    'diners' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
                    'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
                    'jcb' => '/^(?:2131|1800|35\d{3})\d{11}$/'
                ];
                foreach ($regex as $key => $item) {
                    if (preg_match($item, $cardNumber)) {
                        $type = $key;
                        break;
                    }
                }
                return $type;
            }
        }
    }

}
