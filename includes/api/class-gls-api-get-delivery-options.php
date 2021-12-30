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

class GLS_Api_Get_Delivery_Options implements GlsApiCallInterface
{
    /** @var string $endpoint */
    public $endpoint = 'DeliveryOptions/GetDeliveryOptions';

    /** @var $body */
    public $body;

    /** @var $country string */
    private $country;

    /** @var $postcode string */
    private $postcode;

    /**
     * GLS_Api_Get_Delivery_Options constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->postcode = GLS()->post('postcode');
        $this->country  = GLS()->post('country');
        $this->body     = $this->setBody();
    }

    /**
     * @return array
     * @throws Exception
     */
    private function setBody()
    {
        $timezone_string = get_option('timezone_string');
        $gmt_offset      = get_option('gmt_offset');

        if (empty($timezone_string) && 0 != $gmt_offset && floor($gmt_offset) == $gmt_offset) {
            $offset_st       = $gmt_offset > 0 ? "-$gmt_offset" : '+' . absint($gmt_offset);
            $timezone_string = 'Etc/GMT' . $offset_st;
        }

        if (empty($timezone_string)) {
            $timezone_string = 'UTC';
        }

        $timezone      = new DateTimeZone($timezone_string);
        $date_time     = new DateTime(null, $timezone);
        $current_time  = $date_time->format('H:m:s');
        $cutoff_time   = get_option(GLS_Admin::GLS_SETTINGS_SERVICES)[GLS_Admin::SETTING_CUTOFF_TIME];
        $shipping_date = $date_time;

        if ($processingTime = get_option(GLS_Admin::GLS_SETTINGS_SERVICES)[GLS_Admin::SETTING_PROCESSING_TIME]) {
            $shipping_date->modify("+ $processingTime days");
        }

        if ($current_time > $cutoff_time) {
            $shipping_date->modify("+ 1 days");
        }

        return array(
            'countryCode'  => $this->country,
            // TODO: Detect language code dynamically when the GLS API supports it.
            'langCode'     => 'nl',
            'zipCode'      => $this->postcode,
            'shippingDate' => $shipping_date->format('Y-m-d')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        if (!$this->postcode || !$this->country) {
            wp_send_json_error(__('Postcode and/or country not specified.', 'gls-woocommerce'), 412);
        }
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCustomerNo()
    {
        return false;
    }
}
