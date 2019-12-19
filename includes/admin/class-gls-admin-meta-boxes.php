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

        if (!$order->get_meta('_gls_delivery_option')) {
            return;
        }

        foreach (wc_get_order_types('order-meta-boxes') as $type) {
            add_meta_box('gls-order-label', __('GLS Label', 'gls-woocommerce'), 'GLS_Admin_Meta_Box_Order_Label::output', $type, 'side', 'high');
        }
    }
}

new GLS_Admin_Meta_Boxes();
