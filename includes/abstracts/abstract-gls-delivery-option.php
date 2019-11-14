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

if (!defined('ABSPATH')) {
    exit;
}

abstract class GLS_Delivery_Option extends WC_Settings_API
{
    const GLS_DELIVERY_OPTION_EXPRESS_LABEL  = 'ExpressService';

    const GLS_DELIVERY_OPTION_SATURDAY_LABEL = 'SaturdayService';

    /**
     * Yes or no based on whether the option is enabled.
     *
     * @var string
     */
    public $enabled = 'yes';

    /**
     * Option title.
     *
     * @var string
     */
    public $method_title = '';

    /**
     * Option description.
     *
     * @var string
     */
    public $method_description = '';

    /**
     * Option title.
     *
     * @var string
     */
    public $title = '';

    /**
     * Option Additional Fee
     *
     * @var int
     */
    public $additional_fee = 0;

    /**
     * Init settings for options.
     */
    public function init_settings()
    {
        parent::init_settings();
        $this->enabled = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
    }

    /**
     * Return whether or not this option requires additional setup to function when this option is toggled on via AJAX.
     * If this returns true a redirect will occur to the settings page instead.
     *
     * @return bool
     * @since 1.0.0
     */
    public function needs_setup() {
        return false;
    }

    /**
     * Return the title for admin screens.
     *
     * @return string
     */
    public function get_method_title()
    {
        return apply_filters('tig_gls_option_title', $this->method_title, $this);
    }

    /**
     * Return the description for admin screens.
     *
     * @return string
     */
    public function get_method_description()
    {
        return apply_filters('tig_gls_option_description', $this->method_description, $this);
    }

    /**
     * Return the option's title.
     *
     * @return string
     */
    public function get_title()
    {
        return apply_filters('tig_gls_option_title', $this->title, $this->id);
    }

    /**
     * Return the option's additional fee.
     *
     * @return mixed|void
     */
    public function get_additional_fee()
    {
        return apply_filters('tig_gls_option_additional_fee', $this->additional_fee, $this->id);
    }
}
