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

class GLS_Option_T12 extends GLS_Delivery_Option
{
    /**
     * Constructor for the delivery option.
     */
    public function __construct()
    {
        $this->id                 = 'gls_t12';
        $this->method_title       = _x('TimeDefiniteService (Before 12.00 AM)', 'gls-woocommerce');
        $this->method_description = __('Next business day delivery before 12.00 AM.', 'gls-woocommerce');

        $this->init_settings();

        // Define user set variables.
        $this->additional_fee = $this->get_option('additional_fee');

        if ($this->get_additional_fee_from_postdata()) {
            $this->additional_fee = $this->get_additional_fee_from_postdata();
        }

        add_action(
            'gls_update_options_delivery_options_' . $this->id, array(
                $this,
                'process_admin_options'
            )
        );
    }
}
