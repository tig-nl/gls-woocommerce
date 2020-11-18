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

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('GLS_Admin_Assets', false)) {
    /**
     * WC_Admin_Assets Class.
     */
    class GLS_Admin_Assets
    {
        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            // @formatter:off
            add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            // @formatter:on
        }

        /**
         * Enqueue admin styles
         */
        public function admin_styles()
        {
            $screen    = get_current_screen();
            $screen_id = $screen ? $screen->id : '';

            // @formatter:off
            wp_register_style('gls_admin_styles', GLS()->plugin_url('/assets/css/admin.min.css'), array('woocommerce_admin_styles'), GLS_VERSION);
            // @formatter:on

            if (in_array($screen_id, wc_get_screen_ids())) {
                wp_enqueue_style('gls_admin_styles');
            }
        }

        /**
         * Enqueue admin scripts.
         */
        public function admin_scripts()
        {
            global $pagenow;

            // @formatter:off
            wp_register_script('gls_admin', GLS()->plugin_url() . '/assets/js/admin/gls_admin.js', array('jquery', 'woocommerce_admin'), GLS_VERSION);
            wp_enqueue_script('gls_admin');
            // @formatter:on

            $params = array(
                'ajax_url'  => admin_url('admin-ajax.php'),
                'admin_url' => add_query_arg(array('gls_pdf_action' => 'view', '_wpnonce' => wp_create_nonce('view')), admin_url($pagenow)),
                'nonces'    => array(
                    'option_toggle' => wp_create_nonce('gls-toggle-delivery-option-enabled'),
                    'create_label'  => wp_create_nonce('create-label'),
                    'delete_label'  => wp_create_nonce('delete-label')
                )
            );

            wp_localize_script('gls_admin', 'gls_admin', $params);
        }
    }
}

return new GLS_Admin_Assets();
