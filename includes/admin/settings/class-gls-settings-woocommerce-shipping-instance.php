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

defined( 'ABSPATH' ) || exit;

/**
 * Class GLS_Settings_Woocommerce_Shipping_Instance
 */
class GLS_Settings_Woocommerce_Shipping_Instance
{
    public function __construct()
    {
        // @formatter:off
        add_filter('woocommerce_shipping_instance_form_fields_tig_gls', array(__CLASS__, 'filter_woocommerce_shipping_instance_form_fields_tig_gls'), 10, 1);
        // @formatter:on
    }

    /**
     * Add extra formfields to GLS Shipping options instance in WooCommerce Shipping Settings
     * @param $form_fields
     * @return mixed
     */
    public static function filter_woocommerce_shipping_instance_form_fields_tig_gls ($form_fields)
    {

        $form_field_home_delivery_label = array(
            'title'         => __('Home Delivery Label','gls-woocommerce'),
            'type'          => 'text',
            'description'   =>  __('This controls the home delivery label that the user sees with the delivery options','gls-woocommerce'),
            'desc_tip'      => true,
            'default'       => 'Home delivery'
        );

        $form_field_parcelshop_label = array(
            'title'         => __('ParcelShop Label','gls-woocommerce'),
            'type'          => 'text',
            'description'   =>  __('This controls the ParcelShop label that the user sees with the delivery options','gls-woocommerce'),
            'desc_tip'      => true,
            'default'       => 'ParcelShop'
        );

        $form_field_freeshipping_enabled = array(
            'title'         => __('Enable free shipping','gls-woocommerce'),
            'type'          => 'select',
            'description'   =>  __('Enable free shipping above an order amount','gls-woocommerce'),
            'desc_tip'      => true,
            'options'       => array(0 => __('No','gls-woocommerce'), 1 => __('Yes','gls-woocommerce')),
            'default'       => '0'
        );

        $form_field_freeshipping = array(
            'title'         => __('Apply free shipping above order amount','gls-woocommerce'),
            'type'          => 'price',
            'disabled'      => false,
            'description'   =>  __('Enter the order amount (excl. tax) above (and equal) which free shipping will be applied.','gls-woocommerce'),
            'desc_tip'      => true,
            'default'       => '0'
        );

        $form_field_freeshipping_extra = array(
            'title'         => __('Apply free shipping to extra services','gls-woocommerce'),
            'type'          => 'checkbox',
            'description'   =>  __('Enable this when extra service costs are also free when free shipping applies.','gls-woocommerce'),
            'default'       => 'no',
            'desc_tip'      => true
        );

        $form_field_business_personal = array(
            'title'         => __('Set shipping type fixed for B2B or B2C','gls-woocommerce'),
            'type'          => 'select',
            'description'   =>  __('Enable this when sending only to Business or Consumer.','gls-woocommerce'),
            'default'       => GLS_Delivery_Options::GLS_MAP_ADDRESS_AUTO,
            'options'       => array(
                GLS_Delivery_Options::GLS_MAP_ADDRESS_AUTO     => __('Autodetect', 'gls-woocommerce'),
                GLS_Delivery_Options::GLS_MAP_ADDRESS_BUSINESS => __('B2B', 'gls-woocommerce'),
                GLS_Delivery_Options::GLS_MAP_ADDRESS_PERSONAL => __('Personal', 'gls-woocommerce'),
            ),
            'desc_tip'      => true
        );


        $form_fields['home_delivery_label'] = $form_field_home_delivery_label;
        $form_fields['parcelshop_label'] = $form_field_parcelshop_label;
        $form_fields['freeshipping_enabled'] = $form_field_freeshipping_enabled;
        $form_fields['freeshipping'] = $form_field_freeshipping;
        $form_fields['freeshipping_extra'] = $form_field_freeshipping_extra;
        $form_fields[GLS_Delivery_Options::GLS_MAP_ADDRESS_SETTING] = $form_field_business_personal;

        return $form_fields;
    }
}

new GLS_Settings_Woocommerce_Shipping_Instance();
