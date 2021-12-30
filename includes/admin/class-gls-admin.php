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

/**
 * GLS_Admin class.
 */
class GLS_Admin
{
    const GLS_SETTINGS_SERVICES = 'tig_gls_services';
    const SETTING_NEW_ORDER_STATUS    = 'new_order_status';
    const SETTING_CHANGE_ORDER_STATUS = 'order_status_change';
    const SETTING_DISPLAY_SHOPS = 'display_shops';
    const SETTING_PROCESSING_TIME = 'processing_time';
    const SETTING_CUTOFF_TIME = 'cutoff_time';
    const SETTING_ENABLE_SHOP_RETURN = 'shop_return';
    const SETTING_ENABLE_FLEX_DELIVERY = 'flex_delivery';
    const SETTING_LABEL_FORMAT   = 'label_format';
    const SETTING_MARGIN_LEFT_A4 = 'label_margin_left_a4';
    const SETTING_MARGIN_TOP_A4  = 'label_margin_top_a4';

    const GLS_SETTINGS_API      = 'tig_gls_api';
    const API_TEST_MODE = 'test_mode';
    const API_USERNAME = 'username';
    const API_PASSWORD = 'password';
    const API_SUBSCRIPTION_KEY = 'subscription_key';
    const API_CUSTOMER_NUMBER  = 'customer_no';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $settings_link = plugin_basename(GLS_PLUGIN_FILE);
        add_action('init', array($this, 'includes'));
        add_filter("plugin_action_links_$settings_link", array($this, 'settings_link'));
        add_action('admin_notices', array($this, 'admin_notice_action'));
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes()
    {
        include_once dirname(__FILE__) . '/class-gls-admin-assets.php';
        include_once dirname(__FILE__) . '/class-gls-admin-meta-boxes.php';
        include_once dirname(__FILE__) . '/class-gls-admin-api-notice.php';
        include_once dirname(__FILE__) . '/settings/class-gls-settings-woocommerce-shipping-instance.php';
        include_once dirname(__FILE__) . '/class-gls-admin-bulk-actions.php';
        include_once dirname(__FILE__) . '/class-gls-admin-order-columns.php';
    }

    /**
     * Add settings link to plugin overview
     *
     * @param $links
     *
     * @return mixed
     */
    public function settings_link($links)
    {
        $admin_url     = admin_url() . 'admin.php?page=wc-settings&tab=tig_gls';
        $settings_link = "<a href='$admin_url'>" . __('Settings', 'gls-woocommerce') . "</a>";

        return ['settings' => $settings_link] + $links;
    }

    /**
     * Print GLS admin notices in wordpress
     */
    public function admin_notice_action() {
        GLS_Admin_Notice::print_notice();
    }
}

return new GLS_Admin();
