<?php
/**
 * @formatter:off
 * Plugin Name: GLS for WooCommerce
 * Plugin URI: https://tig.nl/
 * Description: Process and send orders in WooCommerce using GLS' shipping services.
 * Version: 1.0.0
 * Author: TIG
 * Author URI: https://tig.nl/
 * License: GPL2v2 or later
 * Text Domain: gls-woocommerce
 * @formatter:on
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Shipping Options before Order Review in WooCommerce Checkout.
 */
function tig_gls_delivery_options()
{
    _e("Shipping Options", "gls-woocommerce");
}
add_action('woocommerce_checkout_before_order_review_heading', 'tig_gls_delivery_options');

/**
 * Adds the GLS shipping method.
 *
 * TODO: Check if WooCommerce is active.
 */
function tig_gls_shipping_method()
{
    if (!class_exists('GLS_Shipping_Method')) {
        class GLS_Shipping_Method extends WC_Shipping_Flat_Rate
        {
            const GLS_SHIPPING_METHOD_ID = 'tig_gls';

            /**
             * GLS_Shipping_Method constructor.
             *
             * @param int $instance_id
             */
            public function __construct(
                $instance_id = 0
            ) {
                $this->id                 = self::GLS_SHIPPING_METHOD_ID;
                $this->instance_id        = absint($instance_id);
                $this->plugin_id          = self::GLS_SHIPPING_METHOD_ID;
                $this->method_title       = __('Ship with GLS', 'gls-woocommerce');
                $this->method_description = __('GLS shipping services for WooCommerce.', 'gls-woocommerce');
                $this->supports           = array(
                    'settings',
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal'
                );
                $this->init();
            }

            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init()
            {
                $this->instance_form_fields = include plugin_dir_path(WC_PLUGIN_FILE) . 'includes/shipping/flat-rate/includes/settings-flat-rate.php';
                $this->title                = $this->get_option('title');
                $this->tax_status           = $this->get_option('tax_status');
                $this->cost                 = $this->get_option('cost');
                $this->type                 = $this->get_option('type', 'class');

                // Save settings in admin if you have any defined
                add_action(
                    'woocommerce_update_options_shipping_' . $this->id, array(
                        $this,
                        'process_admin_options'
                    )
                );
            }
        }
    }
}
add_action('woocommerce_shipping_init', 'tig_gls_shipping_method');

/**
 * @param $methods
 *
 * @return array
 */
function tig_gls_add_shipping_method($methods)
{
    $methods[GLS_Shipping_Method::GLS_SHIPPING_METHOD_ID] = 'GLS_Shipping_Method';

    return $methods;
}
add_filter('woocommerce_shipping_methods', 'tig_gls_add_shipping_method');
