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

class GLS_Admin_Meta_Box_Order_Label
{
    public static function output($post) {
        global $theorder;

        // This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
        if (!is_object($theorder)) {
            $theorder = wc_get_order($post->ID);
        }

        ?>
        <ul class="order_actions order_label submitbox">

            <?php do_action( 'gls_order_label_start', $post->ID ); ?>

            <li class="wide" id="label">

            </li>

            <li class="wide">
                <div id="delete-action">
                    <?php
                    if (current_user_can('delete_post', $post->ID)) {
                        ?>
                        <a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>"><?php echo esc_html(__('Delete label', 'gls-woocommerce')); ?></a>
                        <?php
                    }
                    ?>
                </div>

                <button type="submit" class="button save_order create_label button-primary" name="create" value="<?php // If label is created change this to 'View' or something. ?>"><?= esc_html__( 'Create', 'gls-woocommerce' ); ?></button>
            </li>

            <?php do_action('gls_order_label_end', $post->ID); ?>

        </ul>
        <?php
    }
}
