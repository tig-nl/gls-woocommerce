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
 * This source file is subject to the GPLv3 or later.
 * It is available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
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
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 */
defined( 'ABSPATH' ) || exit;

class GLS_Admin_Api_Notice
{
    /**
     * GLS_Admin_Api_Notice constructor.
     */
    public function __construct()
    {
        // @formatter:off
        $this->api_key_field_check();
        // @formatter:on
    }

    public function api_key_field_check()
    {
        $string = GLS_Admin::GLS_SETTINGS_API;
        $options = get_option($string);
        if (!$options['subscription_key']) {
            $this->api_keys_missing();
        }
    }

    public function api_keys_missing()
    {
        $css = 'notice';
        $name = 'setting';
        $plugin_name = esc_html__( 'GLS', 'gls-woocommerce' );
        $link = esc_html__(
            'Please register or provide an API key to start shipping with GLS.',
            'gls-woocommerce'
        );
        $message = sprintf(
            '<a href="admin.php?page=wc-settings&tab=tig_gls">%s</a>', $link
        );

        add_action( 'admin_notices',
            function() use ( $css, $name, $plugin_name, $message ) {
                echo '<div class="' . $css . '" data-name="' . $name . '">' .
                '<img src="' . GLS()->plugin_url('/assets/images/gls-logo.png') . '"><p>' .
                    $message . '</p></div>';
            }
        );
    }

}

new GLS_Admin_Api_Notice();
