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

class GLS_Api_Label_Create
{
    /** @var string $endpoint */
    public $endpoint = 'Label/Create';

    /** @var $order_id */
    private $order_id;

    /** @var $options */
    private $options;

    /** @var $label_amount */
    private $label_amount;

    /** @var $body */
    public $body = [];

    /** @var GLS_Api $api */
    private $api;

    /**
     * GLS_Api_Label_Create constructor.
     *
     * @param $order_id
     */
    public function __construct($order_id)
    {
        $this->order_id     = $order_id;
        $this->options      = get_option(GLS_Admin::GLS_SETTINGS_SERVICES);
        $this->label_amount = GLS()->post('label_amount');
        $this->body         = $this->setBody();
        $this->api          = GLS_Api::instance($this->endpoint, $this->body);
    }

    /**
     * Trigger call to API.
     *
     * @return string
     */
    public function call()
    {
        return $this->api->call();
    }

    /**
     * @return array
     */
    public function setBody()
    {
        $order = wc_get_order($this->order_id);

        if ($order == false) {
            return;
        }

        $delivery_option  = $order->get_meta('_gls_delivery_option');
        $delivery_address = $delivery_option['delivery_address'];
        $labelType        = $this->get_label_type();
        $shipmentId       = $order->ID;

        $data                      = GLS_Api::add_shipping_information();
        $data["services"]          = $this->map_services($delivery_option['details'], $delivery_option['type'], $delivery_address['countryCode']);
        $data["trackingLinkType"]  = 'u';
        $data['labelType']         = $labelType;
        $data['notificationEmail'] = $this->prepare_notification_email();
        $data['returnRoutingData'] = false;
        $data['addresses']         = [
            'deliveryAddress' => $delivery_address,
            'pickupAddress'   => $this->prepare_pickup_address()
        ];
        $data['shippingDate']      = date("Y-m-d");
        $data['units']             = $this->prepare_shipping_unit($shipmentId, $this->label_amount);

        if (in_array(
            $labelType, [
                'pdf2A4',
                'pdf4A4'
            ]
        )) {
            $data['labelA4MoveYMm'] = $this->get_label_margin_top();
            $data['labelA4MoveXMm'] = $this->get_label_margin_left();
        }

        return $data;
    }

    /**
     * @param        $details
     * @param null   $type
     * @param string $countryCode
     *
     * @return array
     */
    private function map_services($details, $type = null, $countryCode = 'NL')
    {
        $service = [
            "shopReturnService" => (bool) ($this->options['shop_return'] == 'yes' && $countryCode == 'NL')
        ];
        switch ($type) {
            case 'ParcelShop':
                return $service + ["shopDeliveryParcelShopId" => $details['service']];
            case 'ExpressService':
                return $service + [$type => $details['service']];
            case 'SaturdayService':
                return $service + [$type => true];
            default:
                return $service;
        }
    }

    /**
     * @return string
     */
    private function get_label_type()
    {
        return $this->options['label_format'] ?? 'pdfA6S';
    }

    /**
     * @return int|mixed
     */
    private function get_label_margin_top()
    {
        return $this->options['label_margin_top_a4'] ?? 0;
    }

    /**
     * @return int|mixed
     */
    private function get_label_margin_left()
    {
        return $this->options['label_margin_left_a4'] ?? 0;
    }

    /**
     * @return array
     */
    private function prepare_notification_email()
    {
        $email = [
            "sendMail"           => $this->is_flex_delivery_enabled(),
            "senderName"         => get_option('woocommerce_email_from_name'),
            "senderReplyAddress" => get_option('woocommerce_email_from_address'),
            "senderContactName"  => get_option('woocommerce_email_from_name'),
            // TODO: Make subject configurable?
            "EmailSubject"       => __('Your order is sent.', 'gls-woocommerce')
        ];

        return $email;
    }

    /**
     * Booleans are translated to 'yes' and null in WordPress.
     *
     * @return bool
     */
    private function is_flex_delivery_enabled()
    {
        return $this->options['flex_delivery'] == 'yes';
    }

    /**
     * @return array
     */
    private function prepare_pickup_address()
    {
        $address = [
            "name1"       => get_option('blogname'),
            "street"      => get_option('woocommerce_store_address'),
            "houseNo"     => get_option('woocommerce_store_address_2'),
            "zipCode"     => get_option('woocommerce_store_postcode'),
            "city"        => get_option('woocommerce_store_city'),
            "countryCode" => substr(get_option('woocommerce_default_country'), 0, 2)
        ];

        return $address;
    }

    /**
     * @param $shipment_id
     *
     * @return array
     */
    private function prepare_shipping_unit($shipment_id, $label_amount)
    {
        $weight = 1;
        $labels = [];

        for ($i = 0; $i < $label_amount; $i++) {
            $labels[] = [
                "unitId"   => $shipment_id . "-$i",
                "unitType" => "cO",
                "weight"   => $weight
            ];
        }

        return $labels;
    }
}
