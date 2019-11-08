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

/**
 * WooCommerce GLS_AJAX. AJAX Event Handlers.
 *
 * @class   GLS_AJAX
 * @package GLS/Classes
 */

defined('ABSPATH') || exit;

/**
 * GLS_Ajax class.
 */
class GLS_AJAX extends WC_AJAX
{
    /**
     * Hook in ajax handlers.
     */
    public static function init()
    {
        add_action('init', array(__CLASS__, 'define_ajax'), 0);
        add_action('template_redirect', array(__CLASS__, 'do_wc_ajax'), 0);
        self::add_ajax_events();
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events()
    {
        $ajax_events_nopriv = array(// TODO: Update shipping costs using AJAX in checkout.
        );

        foreach ($ajax_events_nopriv as $ajax_event) {
            add_action(
                'wp_ajax_woocommerce_' . $ajax_event, array(
                    __CLASS__,
                    $ajax_event
                )
            );
            add_action(
                'wp_ajax_nopriv_woocommerce_' . $ajax_event, array(
                    __CLASS__,
                    $ajax_event
                )
            );

            // GLS AJAX can be used for frontend ajax requests.
            add_action(
                'wc_ajax_' . $ajax_event, array(
                    __CLASS__,
                    $ajax_event
                )
            );
        }

        $ajax_events = array(
            'toggle_option_enabled',
        );

        foreach ($ajax_events as $ajax_event) {
            add_action(
                'wp_ajax_woocommerce_' . $ajax_event, array(
                    __CLASS__,
                    $ajax_event
                )
            );
        }
    }

    /**
     * Toggle delivery option on or off via AJAX.
     *
     * @since 3.4.0
     */
    public static function toggle_option_enabled()
    {
        if (current_user_can('manage_woocommerce') && check_ajax_referer('gls-toggle-delivery-option-enabled', 'security') && isset($_POST['option_id'])) {
            $delivery_options = GLS()->delivery_options->delivery_options();
            $option_id = wc_clean(wp_unslash($_POST['option_id']));

            foreach ($delivery_options as $option) {
                if (!in_array($option_id, array($option->id, sanitize_title(get_class($option))), true)) {
                    continue;
                }
                
                $enabled = $option->get_option('enabled', 'no');

                if (!wc_string_to_bool($enabled)) {
                    if ($option->needs_setup()) {
                        wp_send_json_error('needs_setup');
                        wp_die();
                    } else {
                        $option->update_option('enabled', 'yes');
                    }
                } else {
                    // Disable the gateway.
                    $option->update_option('enabled', 'no');
                }

                wp_send_json_success(!wc_string_to_bool($enabled));
                wp_die();
            }
        }

        wp_send_json_error('invalid_option_id');
        wp_die();
    }
}

GLS_AJAX::init();
