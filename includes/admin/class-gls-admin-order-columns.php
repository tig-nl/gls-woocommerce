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

class GLS_Admin_Order_Columns
{
    /**
     * GLS_Admin_Order_Columns constructor.
     */
    public function __construct()
    {
        add_filter('manage_edit-shop_order_columns', array($this, 'order_column'), 20);
        add_filter('woocommerce_admin_order_actions', array($this, 'add_gls_print_label_button'), 100, 2);
        add_action('manage_shop_order_posts_custom_column', array($this, 'column_content'));
        add_action('admin_head', array($this, 'add_gls_print_label_button_css'));
    }

    /**
     * @param $columns
     *
     * @return array
     */
    public function order_column($columns)
    {
        $new_columns = array();
        foreach ($columns as $column_name => $column_info) {
            $new_columns[ $column_name ] = $column_info;
            if ('order_status' === $column_name ) {
                $new_columns['gls_shipping_information'] = __('GLS Shipping Information', 'gls-woocommerce');
            }
        }
        return $new_columns;
    }

    /**
     * Adds 'GLS Shipping Information' column content to 'Orders' page immediately before 'Total' column.
     *
     * @param string[] $column name of column being displayed
     */
    public function column_content($column) {
        global $post, $pagenow;

        if ('gls_shipping_information' === $column) {
            $delivery_option = get_post_meta($post->ID, $key = '_gls_delivery_option');
            $label           = get_post_meta($post->ID, $key = '_gls_label');

            if (empty($delivery_option)) {
                echo '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __('GLS shipping information not available.', 'gls-woocommerce') . '</span>';
            }

            if ($delivery_option && isset($delivery_option[0]['details']['title'])) {
                echo $delivery_option[0]['details']['title'];
            }

            if ($label && $label[0] && $label[0]->units && $label[0]->units[0]) {
                $current_label = $label[0]->units[0];
                $pdf_link = add_query_arg(array('gls_pdf_action' => 'download', 'post' => $post->ID, '_wpnonce' => wp_create_nonce('download')), admin_url($pagenow));
                echo "<br><a href='$pdf_link'>" . __('Download Label') . '</a><br/>';
                echo __('Track ID', 'gls-woocommerce') . ": <a href='$current_label->unitTrackingLink' target='_blank'>" . $current_label->unitNo. '</a>';
            }
        }
    }

    public function add_gls_print_label_button($actions, $order) {
        if (!$order || !is_object($order)) {
            return $actions;
        }

        if (count($order->get_shipping_methods()) == 0) {
            return $actions;
        }

        $shipping_methods_array = $order->get_shipping_methods();
        $shipping_method = array_pop($shipping_methods_array)->get_data()['method_id'];
        if ($shipping_method === 'tig_gls') {
            $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
            $actions['gls_print_label'] = array(
                'url'    => wp_nonce_url(
                    admin_url('admin-ajax.php?action=create_label&order_id=' . $order_id),
                    'create_label'
                ),
                'name'   => __('GLS - Print Label', 'woocommerce-deposits'),
                'action' => 'create_label'
            );
        }
        return $actions;
    }

    public function add_gls_print_label_button_css() {
        echo '<style>.create_label::after {font-family: dashicons !important; content: "\f190" !important;}</style>';
    }
}

new GLS_Admin_Order_Columns();
