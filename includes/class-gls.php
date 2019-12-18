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
        $this->define('GLS_ABSPATH', dirname(GLS_PLUGIN_FILE) . '/');
        $this->define('GLS_VERSION', $this->version);
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
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
     * Get available delivery options from API.
     *
     * @return GLS_Api
     * @throws Exception
     */
    public function api_delivery_options()
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
        $cutoff_time   = get_option('tig_gls_services')['cutoff_time'];
        $shipping_date = $date_time;

        if ($processingTime = get_option('tig_gls_services')['processing_time']) {
            $shipping_date->modify("+ $processingTime days");
        }

        if ($current_time > $cutoff_time) {
            $shipping_date->modify("+ 1 days");
        }

        $body = array(
            'countryCode'  => wc_clean(wp_unslash($_POST['country'])),
            'langCode'     => 'nl',
            'zipCode'      => wc_clean(wp_unslash($_POST['postcode'])),
            'shippingDate' => $shipping_date->format('Y-m-d')
        );

        return GLS_Api::instance('DeliveryOptions/GetDeliveryOptions', $body);
    }
}
