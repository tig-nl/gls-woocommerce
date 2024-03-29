<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GPLv3 or later.
 * It is available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 */

class GLS_Api
{
    const GLS_API_CONTROLLER_MODULE = 'TIG/Woocommerce';

    /** @var string $url */
    public $url = 'https://api.gls.nl/';

    /** @var string $http */
    public $http;

    /**
     * @var GLS_Encryption
     */
    public $encryption;

    /** @var array $options */
    public $options;

    /** @var null $_instance */
    protected static $_instance = null;

    /**
     * Main GLS_Api Instance.
     *
     * Ensures only one instance of GLS_Api is loaded or can be loaded.
     *
     * @return GLS_Api Main instance
     * @since 1.0.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * GLS_Api constructor.
     *
     * @param $endpoint
     * @param $body
     */
    public function __construct() {
        $this->options    = get_option(GLS_Admin::GLS_SETTINGS_API);
        $this->http       = $this->options[GLS_Admin::API_TEST_MODE] == 'yes' ? $this->url . 'Test/V1/api/' : $this->url . 'V1/api/';
        $this->encryption = GLS_Encryption::instance();
    }

    /**
     * Add needed authentication to calls.
     */
    protected function addAuthentication($body)
    {
        try {
            $body['shippingSystemName']    = self::GLS_API_CONTROLLER_MODULE;
            $body['shippingSystemVersion'] = GLS_VERSION;
            $body['username']              = $this->encryption->Decrypt($this->options[GLS_Admin::API_USERNAME]);
            $body['password']              = $this->encryption->Decrypt($this->options[GLS_Admin::API_PASSWORD]);
        }
        catch(Exception $exception) {
            return false;
        }
        return $body;
    }

    /**
     * Some calls need a customerNo
     * 2021-05-14: Calls needing customerNo: CreateLabel, CreatePickup, CreateShopReturn
     */
    protected function addCustomerNo($body)
    {
        try {
            $customerNo = $this->encryption->Decrypt($this->options['customer_no']);
            if (empty($customerNo)) {
                return $body;
            }
            $body['customerNo'] = $customerNo;
        }
        catch(Exception $exception) {

        }
        return $body;
    }

    /**
     * @return string
     */
    public function call(GlsApiCallInterface $apiCall)
    {
        try {
            $subscription_key = $this->encryption->Decrypt($this->options[GLS_Admin::API_SUBSCRIPTION_KEY]);
        }
        catch(Exception $exception) {
            //nothing
        }

        $body = $apiCall->getBody();
        $body = $this->addAuthentication($body);
        if($apiCall->hasCustomerNo() === true) {
            $body = $this->addCustomerNo($body);
        }

        $args = array(
            'body'        => json_encode($body),
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Accept'                    => 'application/json',
                'Content-Type'              => 'application/json; charset=UTF-8',
                'User-Agent'                => 'GLSWooCommercePlugin',
                'Ocp-Apim-Subscription-Key' => $subscription_key ?? ''
            ),
            'cookies'     => array()
        );

        $response = wp_safe_remote_post($this->http . $apiCall->getEndpoint() . '?api-version=1.0', $args);

        return json_decode(wp_remote_retrieve_body($response));
    }

    /**
     * @param $controllerModule
     * @param $version
     *
     * @return array
     */
    public static function add_shipping_information()
    {
        return [
            "shiptype"              => "p"
        ];
    }
}
