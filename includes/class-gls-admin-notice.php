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

class GLS_Admin_Notice
{
    const ADMIN_NOTICE_TRANSIENT = 'gls_admin_notice';
    const ADMIN_NOTICE_EXPIRATION = 30;

    /** @var $notice_array array */
    public static $notice_array = array();

    /**
     * @param        $message
     * @param string $type (info|warning|error|success)
     * @param string $screen_id
     */
    public static function admin_add_notice($message, $type = 'info', $screen_id = 'all')
    {
        self::$notice_array = get_transient(self::ADMIN_NOTICE_TRANSIENT);

        //extend notice
        self::$notice_array[$screen_id][$type] = $message;

        set_transient(self::ADMIN_NOTICE_TRANSIENT, self::$notice_array, self::ADMIN_NOTICE_EXPIRATION);
    }

    /**
     *
     */
    public static function print_notice()
    {
        $temp_admin_notice = get_transient(self::ADMIN_NOTICE_TRANSIENT);

        if (is_array($temp_admin_notice)) {
            $screen_current = get_current_screen();

            foreach ($temp_admin_notice as $screen => $admin_notice) {
                if ($screen_current->id != $screen && $screen != 'all') {
                    continue;
                }

                foreach ($admin_notice as $type => $message) {
                    ?>
                    <div id="message" class="notice notice-<?php echo $type; ?> is-dismissible">
                        <p><?php _e($message, 'gls-woocommerce'); ?></p>
                    </div>
                    <?php
                }
            }
        }

        delete_transient(self::ADMIN_NOTICE_TRANSIENT);
    }
}