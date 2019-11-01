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

if (class_exists('GLS_Settings', false)) {
    return new GLS_Settings();
}

class GLS_Settings extends WC_Settings_Page
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'tig_gls';
        $this->label = _x('GLS', 'Settings tab label', 'gls-woocommerce');

        add_action(
            'woocommerce_admin_field_payment_gateways', array(
                $this,
                'gls_setting'
            )
        );
        parent::__construct();
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => __('GLS Configuration', 'gls-woocommerce'),
        );

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Section being shown.
     *
     * @return array
     */
    public function get_settings($current_section = '')
    {
        $settings = array();

        if ('' === $current_section) {
            $settings = apply_filters(
                'woocommerce_tig_gls_settings',
                array(
                    array(
                        'title' => __('GLS Configuration', 'gls-woocommerce'),
                        'desc'  => __('Add your API credentials to connect WooCommerce to the GLS API. Available delivery options are listed below and can be enabled/disabled to control their visibility on the frontend.', 'gls-woocommerce'),
                        'type'  => 'title',
                        'id'    => 'delivery_options_options',
                    ),
                    array(
                        'type' => 'delivery_options',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id'   => 'delivery_options_options',
                    ),
                )
            );
        }

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
    }

    /**
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        // Load gateways so we can show any global options they may have.
        $gls_delivery_options = GLS()->delivery_options->delivery_options();

        if ($current_section) {
            foreach ($gls_delivery_options as $delivery_option) {
                if (in_array(
                    $current_section, array(
                    $delivery_option->id,
                    sanitize_title(get_class($delivery_option))
                ), true
                )) {
                    if (isset($_GET['toggle_enabled'])) { // WPCS: input var ok, CSRF ok.
                        $enabled = $delivery_option->get_option('enabled');

                        if ($enabled) {
                            $delivery_option->settings['enabled'] = wc_string_to_bool($enabled) ? 'no' : 'yes';
                        }
                    }
                    $delivery_option->admin_options();
                    break;
                }
            }
        }

        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }
}

return new GLS_Settings();
