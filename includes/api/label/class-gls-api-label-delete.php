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

class GLS_Api_Label_Delete
{
    /** @var string $endpoint */
    public $endpoint = 'Label/Delete';

    /** @var array $body */
    public $body = [];

    /** @var GLS_Api $api */
    private $api;

    /**
     * GLS_Api_Label_Delete constructor.
     */
    public function __construct()
    {
        $this->body           = GLS_Api::add_shipping_information();
        $this->body['unitNo'] = $this->getUnitNo();
        $this->api            = GLS_Api::instance($this->endpoint, $this->body);
    }

    /**
     * Make the call!
     */
    public function call()
    {
        return $this->api->call();
    }

    /**
     * @return string|null
     */
    private function getUnitNo()
    {
        $order = wc_get_order($_POST['order_id']);
        $label = $order->get_meta('_gls_label');

        return $label->units[0] ? $label->units[0]->unitNo : null;
    }
}
