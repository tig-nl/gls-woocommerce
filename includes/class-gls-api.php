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
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
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
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

class GLS_Api
{
    const GLS_API_CONTROLLER_MODULE = 'WooCommerce';

    /** @var string $url */
    public $url = 'https://api.gls.nl/';

    /** @var string $endpoint */
    public $endpoint;

    /** @var array $body */
    public $body;

    /** @var string $http */
    public $http;

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
    public static function instance($endpoint, $body)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($endpoint, $body);
        }

        return self::$_instance;
    }

    /**
     * GLS_Api constructor.
     *
     * @param $endpoint
     * @param $body
     */
    public function __construct(
        $endpoint,
        $body
    ) {
        $this->endpoint = $endpoint;
        $this->body     = $body;
        $this->options  = get_option(GLS_Admin::GLS_SETTINGS_API);
        $this->http     = $this->options['test_mode'] == 'yes' ? $this->url . 'Test/V1/api/' : $this->url . 'V1/api/';

        $this->init();
    }

    /**
     * Add needed authentication to calls.
     */
    public function init()
    {
        $this->body['username'] = $this->options['username'];
        $this->body['password'] = $this->options['password'];
    }

    /**
     * @return string
     */
    public function call()
    {
        $args = array(
            'body'        => json_encode($this->body),
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                'Accept'                    => 'application/json',
                'Content-Type'              => 'application/json; charset=UTF-8',
                'User-Agent'                => 'GLSWooCommercePlugin',
                'Ocp-Apim-Subscription-Key' => $this->options['subscription_key']
            ),
            'cookies'     => array()
        );

        $response = wp_safe_remote_post($this->http . $this->endpoint . '?api-version=1.0', $args);

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
            "shippingSystemName"    => self::GLS_API_CONTROLLER_MODULE,
            "shippingSystemVersion" => GLS_VERSION,
            "shiptype"              => "p"
        ];
    }
}