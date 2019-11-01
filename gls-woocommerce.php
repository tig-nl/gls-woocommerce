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

if (!defined('GLS_PLUGIN_FILE')) {
    define('GLS_PLUGIN_FILE', __FILE__);
}

/**
 * Add Shipping Options before Order Review in WooCommerce Checkout.
 */
function tig_gls_delivery_options()
{
    include_once dirname(__FILE__) . '/templates/checkout/delivery-options.php';
}

add_action('woocommerce_checkout_before_order_review_heading', 'tig_gls_delivery_options');

/**
 *
 */
function tig_gls_settings_tab($settings)
{
    $settings[] = include 'includes/admin/settings/class-wc-settings-gls.php';

    return $settings;
}

add_filter('woocommerce_get_settings_pages', 'tig_gls_settings_tab');

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
                $this->method_title       = __('GLS', 'gls-woocommerce');
                $this->method_description = __('Connect WooCommerce to GLS to provide GLS delivery options in checkout.', 'gls-woocommerce');
                $this->supports           = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal'
                );

                $this->init();
            }

            public function init()
            {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                // Save settings in admin if you have any defined
                add_action(
                    'woocommerce_update_options_shipping_' . $this->id,
                    array(
                        $this,
                        'process_admin_options'
                    )
                );

                parent::init();
            }

            public function init_form_fields()
            {
                $this->form_fields = array(
                    'test_mode'        => array(
                        'title'   => __('Test mode', 'gls-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Use test mode in staging or development environments', 'gls-woocommerce'),
                        'default' => 'no'
                    ),
                    'username'         => array(
                        'title' => __('Username', 'gls-woocommerce'),
                        'type'  => 'text'
                    ),
                    'password'         => array(
                        'title' => __('Password', 'gls-woocommerce'),
                        'type'  => 'password'
                    ),
                    'subscription_key' => array(
                        'title' => __('Subscription key', 'gls-woocommerce'),
                        'type'  => 'password'
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

// Include the main GLS class.
if (!class_exists('GLS', false)) {
    include_once dirname(__FILE__) . '/includes/class-gls.php';
}

/**
 * Return the Main Instance of GLS
 *
 * @return GLS
 */
function GLS()
{ // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    return GLS::instance();
}

// Global for backwards compatibility.
$GLOBALS['tig_gls'] = GLS();

