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

class GLS_Frontend_Scripts extends WC_Frontend_Scripts
{
    /**
     * Contains an array of script handles registered by GLS.
     *
     * @var array
     */
    private static $scripts = array();

    /**
     * Contains an array of script handles localized by GLS.
     *
     * @var array
     */
    private static $wp_localize_scripts = array();

    /**
     * Hook in methods.
     */
    public static function init()
    {
        // @formatter:off
        add_action('wp_enqueue_scripts', array(__CLASS__, 'load_scripts'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_styles'));
        add_action('wp_print_scripts', array(__CLASS__, 'localize_printed_scripts'), 5);
        add_action('wp_print_footer_scripts', array(__CLASS__, 'localize_printed_scripts'), 5);
        // @formatter:on
    }

    /**
     * Enqueue Front-end styles
     */
    public static function frontend_styles()
    {
        wp_register_style('tig_gls_frontend_styles', GLS()->plugin_url('/assets/css/frontend.min.css'), plugins_url('style.css',GLS_PLUGIN_FILE ));
        wp_enqueue_style('tig_gls_frontend_styles');
    }

    /**
     * Register all GLS scripts.
     */
    private static function register_scripts()
    {
        $register_scripts = array(
            'gls-checkout' => array(
                'src'     => self::get_asset_url('assets/js/frontend/checkout.js'),
                'deps'    => array(
                    'jquery',
                    'woocommerce',
                    'wc-country-select',
                    'wc-address-i18n'
                ),
                'version' => GLS_VERSION,
            )
        );

        foreach ($register_scripts as $name => $properties) {
            self::register_script($name, $properties['src'], $properties['deps'], $properties['version']);
        }
    }

    /**
     * Return asset URL.
     *
     * @param string $path Assets path.
     *
     * @return string
     */
    private static function get_asset_url($path)
    {
        return apply_filters('tig_gls_get_asset_url', plugins_url($path, GLS_PLUGIN_FILE), $path);
    }

    /**
     * Register a script for use.
     */
    private static function register_script($handle, $path, $deps = array('jquery'), $version = GLS_VERSION, $in_footer = true)
    {
        self::$scripts[] = $handle;
        wp_register_script($handle, $path, $deps, $version, $in_footer);
    }

    /**
     * Register and enqueue a script for use.
     */
    private static function enqueue_script($handle, $path = '', $deps = array('jquery'), $version = GLS_VERSION, $in_footer = true)
    {
        if (!in_array($handle, self::$scripts, true) && $path) {
            self::register_script($handle, $path, $deps, $version, $in_footer);
        }
        wp_enqueue_script($handle);
    }

    /**
     * Register/queue frontend scripts.
     */
    public static function load_scripts()
    {
        self::register_scripts();

        if (is_checkout()) {
            self::enqueue_script('gls-checkout');
        }
    }

    /**
     * Localize a GLS script once.
     *
     * @since 2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
     *
     * @param string $handle Script handle the data will be attached to.
     */
    private static function localize_script($handle)
    {
        if (!in_array($handle, self::$wp_localize_scripts, true) && wp_script_is($handle)) {
            $data = self::get_script_data($handle);

            if (!$data) {
                return;
            }

            $name                        = str_replace('-', '_', $handle) . '_params';
            self::$wp_localize_scripts[] = $handle;
            wp_localize_script($handle, $name, apply_filters($name, $data));
        }
    }

    /**
     * Return data for script handles.
     *
     * @param string $handle Script handle the data will be attached to.
     *
     * @return array|bool
     */
    private static function get_script_data($handle)
    {
        switch ($handle) {
            case 'gls-checkout':
                $params = array(
                    'ajax_url'                      => GLS()->ajax_url(),
                    'wc_ajax_url'                   => WC_AJAX::get_endpoint('%%endpoint%%'),
                    'update_delivery_options_nonce' => wp_create_nonce('update-delivery-options'),
                    'update_parcel_shops_nonce'     => wp_create_nonce('update-parcel-shops')
                );
                break;
            default:
                $params = false;
        }

        $params = apply_filters_deprecated($handle . '_params', array($params), '1.0.0', 'gls_get_script_data');

        return apply_filters('gls_get_script_data', $params, $handle);
    }

    /**
     * Localize scripts only when enqueued.
     */
    public static function localize_printed_scripts()
    {
        foreach (self::$scripts as $handle) {
            self::localize_script($handle);
        }
    }
}

GLS_Frontend_Scripts::init();
