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
        $pdf_link = $label ? "onclick=\"window.open('" . add_query_arg(array('gls_pdf_action' => 'view', 'post' => $post->ID, '_wpnonce' => wp_create_nonce('view')), admin_url($pagenow)) . '\', \'_blank\');"' : null;
        ?>
        <ul class="order_actions order_label submitbox">
            <?php do_action('gls_order_label_start', $post->ID); ?>

            <?php if (isset($label->units[0])): ?>
                <li class="wide" id="label">
                    <ul>
                        <li><?php _e('Track ID', 'gls-woocommerce'); ?>:
                            <a href="<?= $label->units[0]->unitTrackingLink; ?>" target="_blank"><?= $label->units[0]->unitNo; ?></a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="wide">
                <div id="delete-action">
                    <?php
                    if (current_user_can('delete_post', $post->ID) && $label) {
                        ?>
                        <a class="submitdelete deletion" href="#"><?php echo esc_html(__('Delete label', 'gls-woocommerce')); ?></a>
                        <?php
                    }
                    ?>
                </div>

                <button type="button"
                        class="button <?= $label ? 'print_label' : 'create_label'; ?> button-primary" name="create"
                        value="<?= $label ? 'Print' : 'Create'; ?>" <?= $pdf_link; ?>><?= esc_html__($label ? 'Print' : 'Create', 'gls-woocommerce'); ?></button>
            </li>

            <?php do_action('gls_order_label_end', $post->ID); ?>
        </ul>
        <?php
    }
}
