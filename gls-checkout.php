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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
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

add_filter('woocommerce_checkout_fields', 'ac_checkout_reorder_fields');
function ac_checkout_reorder_fields($fields) {
    $fields['billing']['billing_heading_name'] = array(
        'type'      => 'heading',
        'label'     => 'Heading here',
    );
    $order = array(
        "first_name",
        "last_name",
        "heading_name",
        "company",
        "email",
        "phone",
        "address_1",
        "address_2",
        "postcode",
        "country"
    );
    foreach($order as $field) {
        $ordered_fields[$field] = $fields["billing"][$field];
    }
    $fields["billing"] = $ordered_fields;
    return $fields;
}


add_action( 'woocommerce_form_field_text','GLS_Shipping_options', 10, 2 );
function GLS_Shipping_options( $field, $key ){
    // will only execute if the field is billing_company and we are on the checkout page...
    if ( is_checkout() && ( $key == 'TIG') ) {
        $field .= '<div id="GlS_Shipping_options"><h2>' . __('GLS shipping options') . '</h2></div>';
    }
    return $field;
}
