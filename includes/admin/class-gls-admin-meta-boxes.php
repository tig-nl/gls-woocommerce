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

class GLS_Admin_Meta_Boxes
{
    public function __construct()
    {
        // @formatter:off
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 40);
        // @formatter:on
    }

    /**
     * Add GLS Meta boxes to all order types.
     */
    public function add_meta_boxes() {
        global $post;
        $order = wc_get_order($post->ID);

        if ($order === false || !$order->get_meta('_gls_delivery_option')) {
            return;
        }

        if (!$order instanceof WC_Abstract_Order) {
            return;
        }

        $has_gls_shipping = false;
        foreach($order->get_shipping_methods() as $shippingMethod){
            if (GLS::instance()->is_gls_selected($shippingMethod['method_id'])) {
                $has_gls_shipping = true;
                break;
            }
        }

        if (!$has_gls_shipping) {
            return;
        }

        foreach (wc_get_order_types('order-meta-boxes') as $type) {
            add_meta_box('gls-order-label', '<img src="' . GLS()->plugin_url('/assets/images/gls-logo.png') . '">' . '&nbsp;&nbsp;' . __('Create labels', 'gls-woocommerce'), 'GLS_Admin_Meta_Box_Order_Label::output', $type, 'side', 'high');
        }
    }
}

new GLS_Admin_Meta_Boxes();
