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
        if (!isset(self::$notice_array[$screen_id][$type])) {
            self::$notice_array[$screen_id][$type] = [];
        }

        self::$notice_array[$screen_id][$type][] = $message;

        set_transient(self::ADMIN_NOTICE_TRANSIENT, self::$notice_array, self::ADMIN_NOTICE_EXPIRATION);
    }

    /**
     * @param        $message
     * @param string $type
     */
    private static function echo_notices($messages, $type = 'info'){
        if(!is_array($messages)){
            $messages = [$messages];
        }
        foreach($messages as $message){
            ?>
            <div id="message" class="notice notice-<?php echo $type; ?> is-dismissible">
                <p><?php _e($message, 'gls-woocommerce'); ?></p>
            </div>
            <?php
        }
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

                foreach ($admin_notice as $type => $messages) {
                    static::echo_notices($messages, $type);
                }
            }
        }

        delete_transient(self::ADMIN_NOTICE_TRANSIENT);
    }
}
