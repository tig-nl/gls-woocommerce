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

/**
 * GLS_Admin class.
 */
class GLS_Admin
{
    const GLS_SETTINGS_SERVICES = 'tig_gls_services';
    const GLS_SETTINGS_API      = 'tig_gls_api';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'includes'));
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes()
    {
        include_once dirname(__FILE__) . '/class-gls-admin-assets.php';
        include_once dirname(__FILE__) . '/class-gls-admin-meta-boxes.php';
    }
}

return new GLS_Admin();
