<?php

/**
 * Plugin Name: TIG GLS for WooCommerce
 * Plugin URI: https://tig.nl/
 * Description: Plugin from to TIG to add GLS shipping to Woocommerce
 * Version: 1.0.0
 * Author: TIG
 * Author URI: https://tig.nl/
 */

if (!defined('WPINC')) {

    die;

}

add_action('woocommerce_checkout_before_order_review_heading', 'tig_gls_delivery_options', 10, 1);

function tig_gls_delivery_options()
{
    _e("Shipping options ", "");
    ?>
    <br>
    <input type="text" name="add_delivery_date" class="add_delivery_date" placeholder="Delivery Date">
    <?php
}

/*
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function GLS_shipping_method()
    {
        if (!class_exists('GlS_Shipping_Method')) {
            class GLS_Shipping_Method extends WC_Shipping_Method
            {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 *
                 * @param int $instance_id
                 */
                public function __construct($instance_id = 0)
                {
                    $this->id                 = 'TIG';
                    $this->method_title       = __('GLS Shipping', 'TIG');
                    $this->method_description = __('Custom Shipping Method for GLS', 'TIG');

                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries    = array(
                        'US',
                        // Unites States of America
                        'CA',
                        // Canada
                        'DE',
                        // Germany
                        'GB',
                        // United Kingdom
                        'IT',
                        // Italy
                        'ES',
                        // Spain
                        'HR'
                        // Croatia
                    );

                    $this->init();

                    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
                    $this->title   = isset($this->settings['title']) ? $this->settings['title'] : __('GLS Shipping', 'TIG');

                    parent::__construct($instance_id);
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init()
                {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action(
                        'woocommerce_update_options_shipping_' . $this->id, array(
                        $this,
                        'process_admin_options'
                    )
                    );
                }

                /**
                 * Define settings field for this shipping
                 * @return void
                 */
                function init_form_fields()
                {

                    $this->form_fields = array(

                        'enabled' => array(
                            'title'       => __('Enable', 'TIG'),
                            'type'        => 'checkbox',
                            'description' => __('Enable this shipping.', 'TIG'),
                            'default'     => 'yes'
                        ),

                        'title' => array(
                            'title'       => __('Title', 'TIG'),
                            'type'        => 'text',
                            'description' => __('Title to be display on site', 'TIG'),
                            'default'     => __('GLS Shipping', 'TIG')
                        ),

                        'weight' => array(
                            'title'       => __('Weight (kg)', 'GLS'),
                            'type'        => 'number',
                            'description' => __('Maximum allowed weight', 'GLS'),
                            'default'     => 100
                        ),

                    );

                }

                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 *
                 * @param mixed $package
                 *
                 * @return void
                 */
                public function calculate_shipping($package = array())
                {

                    $weight  = 0;
                    $cost    = 0;
                    $country = $package["destination"]["country"];

                    foreach ($package['contents'] as $item_id => $values) {
                        $_product = $values['data'];
                        $weight   = $weight + $_product->get_weight() * $values['quantity'];
                    }

                    $weight = wc_get_weight($weight, 'kg');

                    if ($weight <= 10) {

                        $cost = 0;

                    } elseif ($weight <= 30) {

                        $cost = 5;

                    } elseif ($weight <= 50) {

                        $cost = 10;

                    } else {

                        $cost = 20;

                    }

                    $countryZones = array(
                        'HR' => 0,
                        'US' => 3,
                        'GB' => 2,
                        'CA' => 3,
                        'ES' => 2,
                        'DE' => 1,
                        'IT' => 1
                    );

                    $zonePrices = array(
                        0 => 10,
                        1 => 30,
                        2 => 50,
                        3 => 70
                    );

                    $zoneFromCountry = $countryZones[$country];
                    $priceFromZone   = $zonePrices[$zoneFromCountry];

                    $cost += $priceFromZone;

                    $rate = array(
                        'id'    => $this->id,
                        'label' => $this->title,
                        'cost'  => $cost
                    );

                    $this->add_rate($rate);

                }
            }
        }
    }

    add_action('woocommerce_shipping_init', 'GLS_shipping_method');

    function add_GLS_shipping_method($methods)
    {
        $methods[] = 'GLS_Shipping_Method';

        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_GLS_shipping_method');

    function GLS_validate_order($posted)
    {

        $packages = WC()->shipping->get_packages();

        $chosen_methods = WC()->session->get('chosen_shipping_methods');

        if (is_array($chosen_methods) && in_array('GLS', $chosen_methods)) {

            foreach ($packages as $i => $package) {

                if ($chosen_methods[$i] != "GLS") {

                    continue;

                }

                $GLS_Shipping_Method = new GLS_Shipping_Method();
                $weightLimit         = (int) $GLS_Shipping_Method->settings['weight'];
                $weight              = 0;

                foreach ($package['contents'] as $item_id => $values) {
                    $_product = $values['data'];
                    $weight   = $weight + $_product->get_weight() * $values['quantity'];
                }

                $weight = wc_get_weight($weight, 'kg');

                if ($weight > $weightLimit) {

                    $message = sprintf(__('Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'GLS'), $weight, $weightLimit, $GLS_Shipping_Method->title);

                    $messageType = "error";

                    if (!wc_has_notice($message, $messageType)) {

                        wc_add_notice($message, $messageType);

                    }
                }
            }
        }
    }

    add_action('woocommerce_review_order_before_cart_contents', 'GLS_validate_order', 10);
    add_action('woocommerce_after_checkout_validation', 'GLS_validate_order', 10);
}
