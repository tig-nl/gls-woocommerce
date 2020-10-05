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

class GLS_Option_S17 extends GLS_Delivery_Option
{
    /**
     * Constructor for the delivery option.
     */
    public function __construct()
    {
        $this->id                 = 'gls_s17';
        $this->method_title       = __('TimeDefiniteService (Saturday before 17.00 AM)', 'gls-woocommerce');
        $this->method_description = __('Delivery on Saturday before 17.00 AM.', 'gls-woocommerce');

        $this->init_settings();

        // Define user set variables.
        $this->additional_fee = $this->get_option('additional_fee');

        if ($this->get_additional_fee_from_postdata()) {
            $this->additional_fee = $this->get_additional_fee_from_postdata();
        }

        // @formatter:off
        add_action('gls_update_options_delivery_options_' . $this->id, array($this, 'process_admin_options'));
        // @formatter:on
    }
}
