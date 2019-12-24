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

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

defined('ABSPATH') || exit;

class GLS_Pdf
{
    /**
     * @var array
     */
    public static $pdf_label_array = array();

    /**
     * Check if request is PDF action.
     *
     * @return bool
     */
    private static function is_pdf_request()
    {
        return (isset($_GET['post']) && isset($_GET['gls_pdf_action']));
    }

    /**
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public static function gls_pdf_callback()
    {
        if (!self::is_pdf_request()) {
            return;
        }

        // sanitize data and verify nonce.
        $action = sanitize_key($_GET['gls_pdf_action']);
        //$nonce  = sanitize_key( $_GET['nonce'] );
        //if ( ! wp_verify_nonce( $nonce, $action ) ) {
        //    wp_die( 'Invalid request.' );
        //}

        // validate allowed user roles.
        //$user          = wp_get_current_user();
        //$allowed_roles = apply_filters( 'bewpi_allowed_roles_to_download_invoice', array(
        //    'administrator',
        //    'shop_manager',
        ///) );

        //if ( ! array_intersect( $allowed_roles, $user->roles ) && ! user_can( $user, 'manage_network_snippets' ) ) {
        //    wp_die( 'Access denied' );
        //}

        $order_id  = intval($_GET['post']);
        $order_ids = intval($_GET['post_ids']);

        // execute pdf action.
        switch ($action) {
            case 'view':
                $pdf_string = self::add_pdf_label_to_array(array($order_id));
                self::view_pdf($pdf_string[0]);
                break;

            case 'download':
                $pdf_string = self::add_pdf_label_to_array(array($order_id));
                self::view_pdf($pdf_string[0], 'attachment');
                break;

            case 'merge':
                self::add_pdf_label_to_array($order_ids);
                self::merge_pdf();
                break;
        }
    }

    /**
     * @param        $pdf_string
     * @param string $type
     * @param string $filename
     */
    public static function view_pdf($pdf_string, $type = 'inline', $filename = 'gls_label')
    {
        $full_path = $filename . '.pdf';

        header('Content-type: application/pdf');
        header('Content-Disposition: ' . $type . '; filename="' . basename($full_path) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($pdf_string));
        header('Accept-Ranges: bytes');

        echo $pdf_string;
        exit;
    }

    /**
     * @param array $post_ids
     *
     * @return array
     */
    public static function add_pdf_label_to_array($post_ids = array())
    {
        foreach ($post_ids as $post_id) {
            if ($post_id) {
                $gls_label = get_post_meta($post_id, $key = '_gls_label');
                if ($gls_label[0]->labels) {
                    self::$pdf_label_array[] = base64_decode($gls_label[0]->labels);
                }
            }
        }

        return self::$pdf_label_array;
    }

    /**
     * @param string $type
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public static function merge_pdf($type = 'inline')
    {
        if (count(self::$pdf_label_array) < 1) {
            return;
        }

        $pdf = new Fpdi();

        // iterate through the files
        foreach (self::$pdf_label_array as $pdf_label) {

            $pdf->setSourceFile(StreamReader::createByString($pdf_label));

            $templateId = $pdf->importPage(1);
            $size       = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], array($size['width'], $size['height']));
            $pdf->useTemplate($templateId);
        }

        $pdf_string = $pdf->Output('S');
        self::view_pdf($pdf_string, $type);
    }
}