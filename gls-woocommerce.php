<?php
/**
 * @formatter:off
 * Plugin Name: GLS for WooCommerce
 * Plugin URI: https://gls-group.eu/NL/nl/home
 * Description: GLS offers shipping solutions nationally and internationally in Europe and worldwide. By using this plugin you can integrate GLS shipping methods in WooCommerce.
 * Version: 1.5.0
 * Author: TIG
 * Author URI: https://tig.nl/
 * Developer: TIG
 * Developer URI: https://tig.nl/
 * Text Domain: gls-woocommerce
 *
 * WC Requires at least: 3.8.1
 * WC Tested up to: 6.0.0
 *
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @formatter:on
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('GLS_PLUGIN_FILE')) {
    define('GLS_PLUGIN_FILE', __FILE__);
}

/**
 * Add Shipping Options before Order Review in WooCommerce Checkout if any items
 * in cart require shipping.
 */
function tig_gls_delivery_options()
{
    if (!GLS::is_gls_enabled_in_any_zone()) {
        return;
    }
    include_once dirname(__FILE__) . '/templates/checkout/delivery-options.php';
}

add_action('woocommerce_checkout_before_order_review_heading', 'tig_gls_delivery_options');

/**
 *
 */
function tig_gls_settings_tab($settings)
{
    $settings[] = include 'includes/admin/settings/class-gls-settings-delivery-options.php';

    return $settings;
}

add_filter('woocommerce_get_settings_pages', 'tig_gls_settings_tab');

/**
 * Adds the GLS shipping method.
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
                $this->init_settings();

                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

                parent::init();
            }

            public function init_form_fields()
            {
                $this->form_fields = array(

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

/**
 * We're wrapping the initialization in this function, which will be triggered
 * when all plugins (and WooCommerce) are loaded. This way we are sure that this
 * plugin is loaded after WooCommerce.
 */
function tig_gls_init_after_woocommerce()
{
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
}

add_action('woocommerce_loaded', 'tig_gls_init_after_woocommerce');
