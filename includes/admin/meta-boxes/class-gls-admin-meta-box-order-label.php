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

defined('ABSPATH') || exit;

class GLS_Admin_Meta_Box_Order_Label
{
    public static function output($post)
    {
        global $theorder, $pagenow;

        // This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }

        $label    = $theorder->get_meta('_gls_label');
        $amount   = isset($label->units) ? count($label->units) : 1;
        $pdf_link = $label ? "onclick=\"window.open('" . add_query_arg(array('gls_pdf_action' => 'view', 'post' => $post->ID, '_wpnonce' => wp_create_nonce('view')), admin_url($pagenow)) . '\', \'_blank\');"' : null;
        ?>
        <ul class="order_actions order_label submitbox">
            <?php do_action('gls_order_label_start', $post->ID); ?>

            <?php if (isset($label->units)): ?>
                <?php foreach ($label->units as $id => $label): ?>
                    <li class="wide" id="label">
                        <ul>
                            <li><?= __('Track ID', 'gls-woocommerce') . " #" . ($id + 1); ?>:
                                <a href="<?= $label->unitTrackingLink; ?>" target="_blank"><?= $label->unitNo; ?></a>
                            </li>
                        </ul>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>

            <li class="wide">
                <div id="delete-action">
                    <?php if (current_user_can('delete_post', $post->ID) && $label): ?>
                        <a class="submitdelete deletion" href="#"><?= sprintf(_n('Delete label', 'Delete %s labels', $amount, 'gls-woocommerce'), $amount); ?></a>
                    <?php endif; ?>
                </div>

                <?php if (!$label): ?>
                    <label for="gls-label-amount"><?= __('No. of labels', 'gls-woocommerce'); ?></label>
                    <input id="gls-label-amount" value="1" type="number" name="amount" min="1" max="25" />
                <?php endif; ?>

                <?php
                $print = __('Print', 'gls-woocommerce');
                $create = __('Create', 'gls-woocommerce');
                $print_all = __('Print All', 'gls-woocommerce');

                $button_label = _n($label ? $print : $create, $label ? $print_all : $create, $amount, 'gls-woocommerce');
                ?>

                <button type="button"
                        class="button <?= $label ? 'print_label' : 'create_label'; ?> button-primary" name="create"
                        value="<?= $label ? 'Print' : 'Create'; ?>" <?= $pdf_link; ?>><?= $button_label; ?></button>
            </li>

            <?php do_action('gls_order_label_end', $post->ID); ?>
        </ul>
        <?php
    }
}
