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

class GLS_Api_Get_Parcel_Shops
{
    /** @var string $endpoint */
    public $endpoint = 'ParcelShop/GetParcelShops';

    /** @var $body */
    public $body;

    /** @var $postcode string */
    private $postcode;

    /**
     * GLS_Api_Get_Delivery_Options constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->postcode = GLS()->post('postcode');
        $this->body     = $this->setBody();
    }

    /**
     * Trigger call to API.
     *
     * @return string
     */
    public function call()
    {
        if (!$this->postcode) {
            wp_send_json_error(__('No postcode specified.', 'gls-woocommerce'), 412);
        }

        $api = GLS_Api::instance($this->endpoint, $this->body);

        return $api->call();
    }

    /**
     * @return array
     */
    private function setBody()
    {
        return [
            'zipcode'       => $this->postcode,
            'amountOfShops' => get_option('tig_gls_services')['display_shops'] ?? 3
        ];
    }
}
