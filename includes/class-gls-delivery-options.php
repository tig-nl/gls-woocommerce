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

/**
 * Class GLS_Delivery_Options
 *
 * @class GLS_Delivery_Options
 */
class GLS_Delivery_Options
{
    /**
     * @var array $delivery_options
     */
    public $delivery_options = array();

    /**
     * @var null $_instance
     */
    protected static $_instance = null;

    /**
     * Main GLS_Delivery_Options Instance.
     *
     * Ensures only one instance of GLS_Delivery_Options is loaded or can be loaded.
     *
     * @return GLS_Delivery_Options Main instance
     * @since 1.0.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Cloning is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        wc_doing_it_wrong(__FUNCTION__, __('Unserializing instances of this class is forbidden.', 'woocommerce'), '1.0');
    }

    /**
     * Initialize delivery options
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Load gateways and hook in functions.
     */
    public function init()
    {
        $load_delivery_options = array(
            'GLS_Option_T9',
            'GLS_Option_T12',
            'GLS_Option_T17',
            'GLS_Option_S9',
            'GLS_Option_S12',
            'GLS_Option_S17',
            'GLS_Option_ShopDelivery'
        );

        // Filter.
        $load_delivery_options = apply_filters('gls_delivery_options', $load_delivery_options);

        // Load options in order.
        foreach ($load_delivery_options as $option) {
            if (is_string($option) && class_exists($option)) {
                $option = new $option();
            }

            // Options need to be valid and extend GLS_Delivery_Option.
            if (!is_a($option, 'GLS_Delivery_Option')) {
                continue;
            }

            $this->delivery_options[$option->id] = $option;
        }
    }

    /**
     * Get delivery options.
     *
     * @return array
     */
    public function delivery_options()
    {
        $_available_delivery_options = array();

        if (count($this->delivery_options) > 0) {
            foreach ($this->delivery_options as $option) {
                $_available_delivery_options[$option->id] = $option;
            }
        }

        return $_available_delivery_options;
    }

    public function enabled_delivery_options()
    {
        $enabled_delivery_options = array();

        if (count($this->delivery_options) > 0) {
            foreach ($this->delivery_options as $option) {
                if ($option->enabled === 'yes' && $option->id !== 'gls_shop_delivery') {
                    $enabled_delivery_options[$option->id] = $option;
                }
            }
        }

        return $enabled_delivery_options;
    }
}
