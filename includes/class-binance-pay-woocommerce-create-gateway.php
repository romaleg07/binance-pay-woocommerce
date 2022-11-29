<?php

/**
 * The file that defines new payment Gateway creating class
 *
 *
 * @link       https:///github.com/romaleg07
 * @since      1.0.0
 *
 * @package    Binance_Pay_Woocommerce
 * @subpackage Binance_Pay_Woocommerce/includes
 */

/**
 * The payment Gateway class.
 *
 *
 * @since      1.0.0
 * @package    Binance_Pay_Woocommerce
 * @subpackage Binance_Pay_Woocommerce/includes
 * @author     Romaleg <romaleg.sky@yandex.ru>
 */



class WC_Gateway_BinancePay extends WC_Payment_Gateway {
    public $domain;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
    
        $this->domain             = 'binance_pay';
        $this->id                 = 'binance_pay';
        $this->icon               = apply_filters('woocommerce_binance_pay_icon', plugins_url( 'binance-pay-woocommerce/public/img/binance-logo.svg' ));
        $this->has_fields         = false;
        $this->method_title       = __( 'Binance Pay', $this->domain );
        $this->method_description = __( 'Allows payments with Binance Pay.', $this->domain );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();


        // Define user set variables
        $this->title        = $this->get_option( 'title' );
        // $this->description  = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->api_key = $this->get_option( 'api_key' );
	    $this->secret_key = $this->get_option( 'secret_key' );
        // $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->order_status = $this->get_option( 'order_status', 'completed' );

        ini_set( 'error_log', WP_CONTENT_DIR . '/debug-binance-pay.log' );

        /**
         * Add callback handler for callback-links
         */
        add_action( 'woocommerce_api_'. strtolower( get_class($this) ), array( $this, 'callback_handler' ) );
        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        // add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

        add_action('woocommerce_receipt_'.$this->id, array($this, 'receipt_page'));


        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }


    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', $this->domain ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Binance Payment', $this->domain ),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __( 'Title', $this->domain ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
                'default'     => __( 'Binance Pay', $this->domain ),
                'desc_tip'    => true,
            ),
            'order_status' => array(
                'title'       => __( 'Order Status', $this->domain ),
                'type'        => 'select',
                'class'       => 'wc-enhanced-select',
                'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
                'default'     => 'wc-completed',
                'desc_tip'    => true,
                'options'     => wc_get_order_statuses()
            ),
            'description' => array(
                'title'       => __( 'Description', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Payment method description that the customer will see on your checkout.', $this->domain ),
                'default'     => __('Payment Information', $this->domain),
                'desc_tip'    => true,
            ),
            'instructions' => array(
                'title'       => __( 'Instructions', $this->domain ),
                'type'        => 'textarea',
                'description' => __( 'Instructions that will be added to the thank you page and emails.', $this->domain ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'api_key' => array(
                'title'       => 'API Key',
                'type'        => 'text'
            ),
            'secret_key' => array(
                'title'       => 'Secret Key',
                'type'        => 'password'
            )
        );
    }

    /**
     * Generate payment form
     **/
    public function generate_data_for_binance($order_id){
 
        global $woocommerce;
 
        $order = new WC_Order($order_id);
 
        $redirect_url = ($this -> redirect_page_id=="" || $this -> redirect_page_id==0)?get_site_url() . "/":get_permalink($this -> redirect_page_id);
 
        $productinfo = "Order $order_id";



	    $currencies_rate = $this->get_current_rates();


        $currency = $order->get_currency();
        $total = $order->get_total();
        $totalUSDT = 0;

        if($currency == "EUR") {
            $totalUSD = (float)$total * (float)$currencies_rate['usd'];
            $totalUSDT = round((float)$totalUSD / (float)$currencies_rate['usdt'], 2);
        } else {
            $totalUSDT = round((float)$total / (float)$currencies_rate['usdt'], 2);
        }




        $arr = array('env' => array('terminalType' => 'WEB'), 'merchantTradeNo' => $order_id, 'orderAmount' => $totalUSDT, 'currency' => 'USDT');

        $arr['goods'] = array();

        $index = 0;
        $arr['goods']['goodsType'] = "02";
        $arr['goods']['goodsCategory'] = "6000";
        $referenceGoodsId = '';
        $goodsName = '';
        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = (string)$item->get_product_id();
            if($index == 0) {
                $referenceGoodsId = $product_id;
                $goodsName = $item->get_name();
            } else {
                $referenceGoodsId .= ', ' . $product_id;
                $goodsName .= ', ' . $item->get_name();
            }
            $index++;
        }
        $arr['goods']['referenceGoodsId'] =  $referenceGoodsId;
        $arr['goods']['goodsName'] =  $goodsName;
        $arr['webhookUrl'] = 'https://baxity.com/store/wc-api/wc_gateway_binancepay/';
        $arr['returnUrl'] = 'https://baxity.com/store/my-account/orders/';

        $json_arr = json_encode($arr);


        $nonce = $this->generateRandomString();
        $body = json_encode($arr);

        $secretKey = $this->secret_key;
        $apiKey = $this->api_key;


        $currencies_rate_json = json_encode($currencies_rate);

        $timestamp = round(microtime(true) * 1000);
        
        $payload = $timestamp . "\n" . $nonce . "\n" . $body . "\n";

        $signature = strtoupper(hash_hmac('sha512', $payload, $secretKey));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bpay.binanceapi.com/binancepay/openapi/v2/order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_arr,
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            "BinancePay-Timestamp: $timestamp",
            "BinancePay-Nonce: $nonce",
            "BinancePay-Certificate-SN: $apiKey",
            "BinancePay-Signature: $signature",
            ),
        ));

        $responseJson = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($responseJson, true);

        
        error_log("Ответ binance: " . $responseJson);

        
		return $response;
 
    }

    public function generateRandomString() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 32; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    /**
     * get rates
     **/
    public function get_current_rates() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'api.coincap.io/v2/rates/euro/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);

        $rateUSD = $response['data']['rateUsd'];

        $usd = $rateUSD;

        $curl2 = curl_init();

        curl_setopt_array($curl2, array(
            CURLOPT_URL => 'api.coincap.io/v2/rates/tether/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response2 = curl_exec($curl2);

        curl_close($curl2);
        $response2 = json_decode($response2, true);

        $rateUSDT = $response2['data']['rateUsd'];

        $usdt = $rateUSDT;

        return array("usd"=> $usd, "usdt" => $usdt);
    }


    /**
     * Receipt Page
     **/
    function receipt_page($order){
        $response = $this->generate_data_for_binance($order);
        if ($response['status'] == "SUCCESS") {
            $url = $response['data']['universalUrl'];
            error_log("Редирект на ссылку: " . $url);
            WC()->cart->empty_cart();
            header("Location: $url ");
        } else {
            echo '<p>'.__('Error! Please, report the error code to support: ', 'woocommerce'). $response['code'] .'</p>';
        }
    }


    /**
     * Create callback handler
     */
    public function callback_handler() {
        $raw_post = file_get_contents( 'php://input' );

        error_log("Ответ на коллбэк : " . $raw_post);
 
		$decoded  = json_decode( $raw_post );


    }

    /**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
    function process_payment($order_id){
        
        $order = new WC_Order($order_id);
        
        return array('result' => 'success', 'redirect' => add_query_arg(array(
                    'key' => $order->order_key,
                    'order' => $order->id
                ),
                wc_get_checkout_url()
            )
        );
        
    }

}

