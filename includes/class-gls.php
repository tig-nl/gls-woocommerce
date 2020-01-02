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
 * Class GLS
 *
 * @class GLS
 */
final class GLS
{
    /**
     * GLS for WooCommerce version.
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * The single instance of the class.
     *
     * @var GLS
     */
    protected static $_instance = null;

    /**
     * Main GLS Instance.
     *
     * Ensures only one instance of WooCommerce is loaded or can be loaded.
     *
     * @static
     * @return GLS - Main instance.
     * @see WC()
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
     */
    public function __clone()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Auto-load in-accessible properties on demand.
     *
     * @param mixed $key Key name.
     *
     * @return mixed|void
     */
    public function __get($key)
    {
        if (in_array($key, array('delivery_options'), true)) {
            return $this->$key();
        }
    }

    /**
     * WooCommerce Constructor.
     */
    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define GLS Constants.
     */
    private function define_constants()
    {
        define('GLS_ABSPATH', dirname(GLS_PLUGIN_FILE) . '/');
        define('GLS_VERSION', $this->version);
    }

    /**
     * Returns true if the request is a non-legacy REST API request.
     *
     * Legacy REST requests should still run some extra code for backwards compatibility.
     *
     * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
     *
     * @return bool
     */
    public function is_rest_api_request()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $rest_prefix         = trailingslashit(rest_get_url_prefix());
        $is_rest_api_request = (false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix)); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        return apply_filters('tig_gls_is_rest_api_request', $is_rest_api_request);
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON') && !$this->is_rest_api_request();
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes()
    {
        /**
         * Composer autoloader.
         */
        if (file_exists(GLS_ABSPATH . '/vendor/autoload.php')) {
            require_once GLS_ABSPATH . '/vendor/autoload.php';
        }

        /**
         * Class autoloader.
         */
        include_once GLS_ABSPATH . 'includes/class-gls-autoloader.php';

        /**
         * Abstract classes.
         */
        include_once GLS_ABSPATH . 'includes/abstracts/abstract-gls-delivery-option.php';

        /**
         * Core classes.
         */
        include_once GLS_ABSPATH . 'includes/class-gls-ajax.php';

        /**
         * Admin classes.
         */
        if ($this->is_request('admin')) {
            include_once GLS_ABSPATH . 'includes/admin/class-gls-admin.php';
        }

        /**
         * Frontend classes.
         */
        if ($this->is_request('frontend')) {
            $this->frontend_includes();
        }
    }

    /**
     * Include required frontend files.
     */
    public function frontend_includes()
    {
        include_once GLS_ABSPATH . 'includes/class-gls-frontend-scripts.php';
    }

    /**
     *
     */
    private function init_hooks()
    {
        add_action('woocommerce_cart_calculate_fees', array('GLS_Delivery_Options', 'update_shipping_rate'));
        add_action('woocommerce_checkout_create_order', array('GLS_Delivery_Options', 'add_option_to_order'), 100, 2);
        add_action('woocommerce_init', array('GLS_Pdf', 'gls_pdf_callback'));
    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', GLS_PLUGIN_FILE));
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(GLS_PLUGIN_FILE));
    }

    /**
     * Cleans and returns any POST-data.
     *
     * @param null $key
     *
     * @return array|string
     */
    public function post($key = null)
    {
        if (!$key) {
            return wc_clean(wp_unslash($_POST));
        }

        return wc_clean(wp_unslash($_POST[$key]));
    }

    /**
     * Get Ajax URL.
     *
     * @return string
     */
    public function ajax_url()
    {
        return admin_url('admin-ajax.php', 'relative');
    }

    /**
     * Get delivery options class.
     *
     * @return GLS_Delivery_Options
     */
    public function delivery_options()
    {
        return GLS_Delivery_Options::instance();
    }

    /**
     * @param $shipping_method
     *
     * @return bool
     */
    public function is_gls_selected($shipping_method)
    {
        if (strpos($shipping_method, 'tig_gls')) {
            return true;
        }

        return false;
    }

    /**
     * Get available delivery options from API.
     *
     * @return GLS_Api_Get_Delivery_Options
     * @throws Exception
     */
    public function api_delivery_options()
    {
        return new GLS_Api_Get_Delivery_Options();
    }

    /**
     * @return GLS_Api_Get_Parcel_Shops
     * @throws Exception
     */
    public function api_pickup_locations()
    {
        return new GLS_Api_Get_Parcel_Shops();
    }

    /**
     * @return GLS_Api_Validate_Login
     */
    public function api_validate_login()
    {
        return new GLS_Api_Validate_Login();
    }

    /**
     * @param $order_id
     * @return GLS_Api_Label_Create
     */
    public function api_create_label($order_id)
    {
        return new GLS_Api_Label_Create($order_id);
    }

    /**
     * @return GLS_Api_Label_Delete
     */
    public function api_delete_label()
    {
        return new GLS_Api_Label_Delete();
    }
}
