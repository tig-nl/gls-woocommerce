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

class GLS_Admin_Bulk_Actions
{
    /**
     * GLS_Admin_Bulk_Actions constructor.
     */
    public function __construct()
    {
        // @formatter:off
        add_filter( 'bulk_actions-edit-shop_order',  array($this, 'gls_bulk_actions_on_orders'), 20, 1 );
        add_filter( 'handle_bulk_actions-edit-shop_order', array($this,  'gls_handle_bulk_action_create_label'), 10, 3 );
        add_action( 'admin_notices', array($this, 'downloads_bulk_action_admin_notice'));
        // @formatter:on
    }
    /**
     * Add GLS Bulk Action to all order types.
     */
    public function gls_bulk_actions_on_orders($actions) {
        $actions['gls_mass_create_label'] = __( 'GLS - Create & Print Labels', 'gls-woocommerce' );
        return $actions;
    }

    /**
     * @param $redirect_to
     * @param $action
     * @param $post_ids
     * @return string
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function gls_handle_bulk_action_create_label($redirect_to, $action, $post_ids )
    {
        if ($action !== 'gls_mass_create_label') {
            return $redirect_to;
        }

        $error_ids = array();

        $glsApi = GLS_Api::instance();
        foreach ($post_ids as $post_id) {
            $gls_option = get_post_meta($post_id, $key = '_gls_delivery_option');
            $gls_label = get_post_meta($post_id, $key = '_gls_label');
            if (!$gls_label[0]->labels && count($gls_option)) {
                $order = wc_get_order($post_id);
                /** @var StdClass $response */
                $createLabel = new GLS_Api_Label_Create($post_id);
                $response = $glsApi->call($createLabel);
                if ($response->status != 200) {
                    $error_ids[] = $post_id;
                    continue;
                }
                $order->update_meta_data('_gls_label', $response);
                $order->save();
            }
        }
        $processed_ids = GLS_Pdf::add_pdf_label_to_array($post_ids);
        if (get_option('tig_gls_services')['order_status'] === 'yes') {
            foreach ($post_ids as $postId) {
                $order = wc_get_order($postId);
                $newOrderStatus = get_option('tig_gls_services')['new_order_status'];
                $order->update_status($newOrderStatus);
            }
        }
        GLS_Pdf::merge_pdf('attachment');

        return $redirect_to = add_query_arg( array(
            'gls_mass_create_label' => '1',
            'processed_count' => count($processed_ids),
            'error_ids' => implode( ',', $error_ids ),
            'error_count' => count($error_ids),
        ), $redirect_to );
    }

    /**
     *
     */
    public function downloads_bulk_action_admin_notice()
    {
        if (empty($_REQUEST['gls_mass_create_label'])) {
            return;
        }

        $count_processed = intval($_REQUEST['processed_count']);
        $count_errors    = intval($_REQUEST['error_count']);
        $error_ids       = sanitize_text_field($_REQUEST['error_ids']);

        $class = 'notice notice-updated';
        $string_errored = '';
        if ($count_errors > 0) {
            $class = 'notice notice-error';

            $error_ids = str_replace(',', ', #', $error_ids);

            $string_errored = sprintf(
                _n( 'Failed Order id: #%s for GLS shipment information.',
                    'Failed Order ids: #%s for GLS shipment information.',
                    $count_errors,
                    'gls_mass_create_label'), $error_ids
            );
        }

        $string_processed = sprintf(
            _n( 'Processed %s Order for GLS shipment information.',
            'Processed %s Orders for GLS shipment information.',
            $count_processed,
            'gls_mass_create_label'), $count_processed
        );

        echo '<div id="message" class="' . $class . '"><p>' . $string_processed . ' ' . $string_errored . '</p></div>';
    }
}

new GLS_Admin_Bulk_Actions();

