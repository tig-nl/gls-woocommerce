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

defined('ABSPATH') || exit;

/**
 * Class GLS_Delivery_Options
 *
 * @class GLS_Delivery_Options
 */
class GLS_Delivery_Options
{
    /**
     * @var array $delivery_options
     */
    public $delivery_options = array();

    /**
     * @var null $_instance
     */
    protected static $_instance = null;

    /**
     * Main GLS_Delivery_Options Instance.
     *
     * Ensures only one instance of GLS_Delivery_Options is loaded or can be loaded.
     *
     * @return GLS_Delivery_Options Main instance
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
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Initialize delivery options
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Load options and hook in functions.
     */
    public function init()
    {
        $load_delivery_options = array(
            'GLS_Option_T9',
            'GLS_Option_T12',
            'GLS_Option_T17',
            'GLS_Option_S9',
            'GLS_Option_S12',
            'GLS_Option_S17',
            'GLS_Option_SaturdayService',
            'GLS_Option_ShopDelivery'
        );

        // Filter.
        $load_delivery_options = apply_filters('gls_delivery_options', $load_delivery_options);

        // Load options in order.
        foreach ($load_delivery_options as $option) {
            if (is_string($option) && class_exists($option)) {
                $option = new $option();
            }

            // Options need to be valid and extend GLS_Delivery_Option.
            if (!is_a($option, 'GLS_Delivery_Option')) {
                continue;
            }

            $this->delivery_options[$option->id] = $option;
        }
    }

    /**
     * @param $available
     * @param $enabled
     *
     * @return array
     */
    public function delivery_options($available, $enabled)
    {
        $delivery_options = [];

        foreach ($available as &$option) {
            // BusinessParcel (default)
            if (!isset($option->service)) {
                $option->fee = 0;
                $option->formatted_fee = '';
                $delivery_options[] = $option;

                continue;
            }

            $saturdayServiceEnabled = $this->any_express_services_enabled($enabled, ['gls_s9', 'gls_s12', 'gls_s17', 'gls_saturdayservice'])
                                      && $option->service == GLS_Delivery_Option::GLS_DELIVERY_OPTION_SATURDAY_LABEL;
            $expressServiceEnabled  = $this->any_express_services_enabled($enabled)
                                      && $option->service == GLS_Delivery_Option::GLS_DELIVERY_OPTION_EXPRESS_LABEL;
            $hasSubOptions          = isset($option->subDeliveryOptions);

            // SaturdayService
            if ($saturdayServiceEnabled) {
                $option->fee           = $this->additional_fee($option, $enabled) ?? 0;
                $option->formatted_fee = $this->format_fee($option->fee);
                $delivery_options[]    = $option;
            }

            /**
             * If no Express or Saturday Services are enabled, there's no need to render sub delivery options.
             */
            if ((!$hasSubOptions && $option->service == GLS_Delivery_Option::GLS_DELIVERY_OPTION_EXPRESS_LABEL && !$expressServiceEnabled)
                || (!$hasSubOptions && $option->service == GLS_Delivery_Option::GLS_DELIVERY_OPTION_SATURDAY_LABEL && !$saturdayServiceEnabled)
            ) {
                continue;
            }

            // (Saturday)ExpressServices
            if ($hasSubOptions) {
                $option->subDeliveryOptions = array_values(
                    $this->filter_sub_delivery_options(
                        $option->subDeliveryOptions, $enabled
                    )
                );

                if (count($option->subDeliveryOptions) > 0) {
                    $delivery_options[] = $option;
                }
            }
        };

        return $delivery_options;
    }

    /**
     * @param       $enabled_options
     * @param array $options
     *
     * @return bool
     */
    private function any_express_services_enabled($enabled_options, $options = ['gls_t9', 'gls_t12', 'gls_t17'])
    {
        return $this->is_service_enabled($enabled_options, $options);
    }

    /**
     * @param $enabled_options
     * @param $options
     *
     * @return bool|void
     */
    private function is_service_enabled($enabled_options, $options)
    {
        foreach ($enabled_options as $option) {
            if (in_array($option->id, $options)) {
                return true;
            }
        }
    }

    /**
     * @param $options
     * @param $enabled_options
     *
     * @return array
     */
    private function filter_sub_delivery_options(&$options, $enabled_options)
    {
        return array_filter(
            $options,
            function (&$option) use ($enabled_options) {
                $option->fee           = $this->additional_fee($option, $enabled_options);
                $option->formatted_fee = $this->format_fee($option->fee);

                return $this->is_express_service_enabled($enabled_options, $option);
            }
        );
    }

    /**
     * @param $option
     * @param $enabled_options
     *
     * @return string|float
     */
    private function additional_fee($option, $enabled_options)
    {
        $code = 'gls_' . strtolower($option->service);
        $fee  = isset($enabled_options[$code]) ? $enabled_options[$code]->additional_fee : '';

        return $fee;
    }

    /**
     * Shipping Costs are always entered excluding tax if tax is enabled in WooCommerce. Shipping Costs do not adapt to
     * settings like 'Enter prices including tax'.
     *
     * @param $fee
     *
     * @return string|void
     */
    private function format_fee($fee)
    {
        if (!$fee) {
            return;
        }

        $tax = WC_Tax::calc_tax((float) $fee, WC_Tax::get_rates(), false);
        $tax = reset($tax);

        if ($fee < 0) {
            return wc_price((float) $tax + (float) $fee);
        }

        return '+' . wc_price((float) $tax + (float) $fee);
    }

    /**
     * @param $enabled_options
     * @param $option
     *
     * @return bool
     */
    private function is_express_service_enabled($enabled_options, &$option)
    {
        return array_key_exists('gls_' . strtolower($option->service), $enabled_options);
    }

    /**
     * Get delivery options.
     *
     * @return array
     */
    public function available_delivery_options()
    {
        $available_delivery_options = array();

        if (count($this->delivery_options) > 0) {
            foreach ($this->delivery_options as $option) {
                $available_delivery_options[$option->id] = $option;
            }
        }

        return $available_delivery_options;
    }

    /**
     * Get enabled delivery options.
     *
     * @return array
     */
    public function enabled_delivery_options()
    {
        $enabled_delivery_options = array();

        if (count($this->delivery_options) <= 0) {
            return $enabled_delivery_options;
        }

        foreach ($this->delivery_options as $option) {
            if ($option->enabled === 'yes' && $option->id !== 'gls_shop_delivery') {
                $enabled_delivery_options[$option->id] = $option;
            }
        }

        return $enabled_delivery_options;
    }

    /**
     * @param $available
     *
     * @return mixed
     */
    public function parcel_shops(&$available)
    {
        $parcel_shops = array();

        if (count($this->delivery_options) == 0) {
            return $parcel_shops;
        }

        $fee = $this->delivery_options['gls_shop_delivery']->additional_fee;

        foreach ($available as &$shop) {
            $shop->fee           = $fee;
            $shop->formatted_fee = wc_price($fee);
        }

        return $available;
    }

    /**
     * Disable cache on packages to always trigger woocommerce_package_rates,
     * see: https://github.com/woocommerce/woocommerce/issues/22100
     *
     * @param $packages
     *
     * @return array
     */
    public static function disable_shipping_rates_cache($packages) {

        if (is_admin() && !defined('DOING_AJAX'))
            return $packages;

        $session         = WC()->session;
        $shipping_method = $session->get('chosen_shipping_methods');

        if (is_array($shipping_method) && !GLS()->is_gls_selected(reset($shipping_method))) {
            return $packages;
        }

        if (is_array($packages) && $packages[0]) {
            $packages[0]['rand'] = rand();
        }

        return $packages;
    }

    /**
     * Update shipping price when GLS Delivery Option or ParcelShop is selected.
     *
     * @param $rates
     * @return mixed
     */
    public static function adjust_shipping_rate($rates){

        if (is_admin() && !defined('DOING_AJAX'))
            return $rates;

        $session         = WC()->session;
        $shipping_method = $session->get('chosen_shipping_methods');

        if (is_array($shipping_method) && !GLS()->is_gls_selected(reset($shipping_method))) {
            return $rates;
        }

        // Free Shipping
        $freeshipping = self::calculate_gls_freeshipping();
        $freeshipping_extra = self::get_shipping_method_instance_setting('freeshipping_extra');

        $service = $session->get('gls_service');
        $details = $service['details'] ?? [];
        $fee     = $details['fee'] ?? '';

        foreach ($rates as &$rate) {
            if ($rate->get_method_id() == 'tig_gls') {
                if ($freeshipping) {
                    $rate->cost = 0;
                }

                if (!$freeshipping || $freeshipping_extra == "no") {
                    $rate->cost += (float)$fee;
                }

                if ($rate->cost <= 0) {
                    $rate->cost = 0;
                    $rate->label = $rate->label . __(' (free shipping)','gls-woocommerce');
                }

                $tax_array = WC_Tax::calc_tax($rate->cost, WC_Tax::get_rates(), false );

                $rate->set_taxes($tax_array);
            }
        }
        return $rates;
    }

    /**
     * @return bool
     */
    public static function calculate_gls_freeshipping()
    {
        $enabled = (int)self::get_shipping_method_instance_setting('freeshipping_enabled');

        if (!$enabled) {
            return false;
        }

        $freeshipping_amount = (float)self::get_shipping_method_instance_setting('freeshipping');

        if ($freeshipping_amount > 0) {
            $session = WC()->session;
            $cart_totals = $session->get('cart_totals');
            if ($freeshipping_amount <= ($cart_totals['subtotal'] + $cart_totals['subtotal_tax'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $setting_name
     * @return |null
     */
    public static function get_shipping_method_instance_setting($setting_name)
    {
        $session = WC()->session;
        $chosen_shipping_method = $session->get('chosen_shipping_methods');

        $shipping_method_id = explode(':',$chosen_shipping_method[0]);
        $current_shipping_methods = WC()->shipping->get_shipping_methods();
        $current_shipping_method = $current_shipping_methods[$shipping_method_id[1]];

        //get instance setting
        if ($current_shipping_method->instance_settings[$setting_name]) {
            return $current_shipping_method->instance_settings[$setting_name];
        }

        return null;
    }

    /**
     * @param $order
     * @param $data
     *
     * @return mixed
     */
    public static function add_option_to_order($order, $data)
    {
        if (!GLS()->is_gls_selected(reset($data['shipping_method']))) {
            return $order;
        }

        $service = WC()->session->get('gls_service');
        $order->update_meta_data('_gls_delivery_option', $service);

        return $order;
    }

    /**
     * @return float|void
     */
    public function format_shop_delivery_fee()
    {
        $shop_delivery  = GLS()->delivery_options()->delivery_options['gls_shop_delivery'];
        $additional_fee = $shop_delivery->additional_fee;

        if (!$additional_fee) {
            return;
        }

        $tax = WC_Tax::calc_tax((float) $additional_fee, WC_Tax::get_rates(), false);

        return wc_price((float) reset($tax) + (float) $additional_fee);
    }
}
