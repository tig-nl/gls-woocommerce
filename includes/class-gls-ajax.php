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

class GLS_AJAX extends WC_AJAX
{
    /**
     * Hook in ajax handlers.
     */
    public static function init()
    {
        // @formatter:off
        add_action('init', array(__CLASS__, 'define_ajax'), 0);
        add_action('template_redirect', array(__CLASS__, 'do_wc_ajax'), 0);

        // @formatter:on
        self::add_ajax_events();
    }

    /**
     * Hook in methods - uses WordPress ajax handlers (admin-ajax).
     */
    public static function add_ajax_events()
    {
        $ajax_events_nopriv = array(
            'update_delivery_options',
            'update_parcel_shops',
            'delivery_option_selected'
        );

        foreach ($ajax_events_nopriv as $ajax_event) {
            // @formatter:off
            add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            add_action('wp_ajax_nopriv_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
        }

        $ajax_events = array(
            'toggle_option_enabled',
            'create_label',
            'delete_label'
        );

        foreach ($ajax_events as $ajax_event) {
            add_action('wp_ajax_woocommerce_' . $ajax_event, array(__CLASS__, $ajax_event));
            // @formatter:on
        }
    }

    /**
     * @throws Exception
     */
    public static function update_delivery_options()
    {
        check_ajax_referer('update-delivery-options', 'security');

        if (!WC()->cart->needs_shipping()) {
            wp_send_json_error(__('The products in this cart do not require shipping.', 'gls-woocommerce'), 405);
        }

        /** @var StdClass $response */
        $api = GLS_Api::instance();
        $response = $api->call(GLS()->api_delivery_options());

        self::capture_frontend_ajax_errors($response);

        self::check_required_configuration($response);

        $available_delivery_options = $response->deliveryOptions;
        $enabled_delivery_options   = GLS()->delivery_options()->enabled_delivery_options();
        $delivery_options           = GLS()->delivery_options()->delivery_options($available_delivery_options, $enabled_delivery_options);

        wp_send_json_success($delivery_options, $response->status);
    }

    /**
     * @throws Exception
     */
    public static function update_parcel_shops()
    {
        check_ajax_referer('update-parcel-shops', 'security');

        $is_shop_delivery_enabled = GLS()->delivery_options()->delivery_options['gls_shop_delivery']->enabled;

        if (GLS()->post('country') !== 'NL' || $is_shop_delivery_enabled == 'no') {
            wp_send_json_success([], 200);
        }

        /** @var StdClass $response */
        $api = GLS_Api::instance();
        $response = $api->call(GLS()->api_pickup_locations());

        self::capture_frontend_ajax_errors($response);

        self::check_required_configuration($response);

        $available_parcel_shops = $response->parcelShops;
        $parcel_shops           = GLS()->delivery_options()->parcel_shops($available_parcel_shops);

        wp_send_json_success($parcel_shops, $response->status);
    }

    /**
     * Adds fee of selected delivery option to order review block.
     */
    public static function delivery_option_selected()
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        $details = GLS()->post('details');
        $title   = isset($details['title']) ? strtolower($details['title']) : '';

        if (strpos($title, ' | ')) {
            $_POST['details']['title'] = explode(' | ', $title)[0];
        }

        $session = WC()->session;

        if (isset($details['service']) && isset($details['title']) && isset($details['fee'])) {
            $session->set('gls_service', GLS()->post(null, false));
        }

        wp_die();
    }

    /**
     * Toggle delivery option on or off via AJAX.
     *
     * @since 1.0.0
     */
    public static function toggle_option_enabled()
    {
        if (current_user_can('manage_woocommerce') && check_ajax_referer('gls-toggle-delivery-option-enabled', 'security') && isset($_POST['option_id'])) {
            $delivery_options = GLS()->delivery_options->available_delivery_options();
            $option_id        = GLS()->post('option_id');

            /** @var stdClass $option */
            foreach ($delivery_options as $option) {
                if (!in_array(
                    $option_id, array(
                    $option->id,
                    sanitize_title(get_class($option))
                ), true
                )) {
                    continue;
                }

                $enabled = $option->get_option('enabled', 'no');

                if (!wc_string_to_bool($enabled)) {
                    if ($option->needs_setup()) {
                        wp_send_json_error('needs_setup');
                        wp_die();
                    } else {
                        $option->update_option('enabled', 'yes');
                    }
                } else {
                    // Disable the option.
                    $option->update_option('enabled', 'no');
                }

                wp_send_json_success(!wc_string_to_bool($enabled));
                wp_die();
            }
        }

        wp_send_json_error('invalid_option_id');
        wp_die();
    }

    /**
     * Trigger CreateLabel-call and save it as post meta data.
     */
    public static function create_label()
    {
        check_ajax_referer('create-label', 'security');

        $order = wc_get_order(GLS()->post('order_id'));

        /** @var StdClass $response */
        $api = GLS_Api::instance();
        $response = $api->call(GLS()->api_create_label($_POST['order_id']));

        self::catch_admin_ajax_errors($response);

        self::check_required_configuration($response, true);

        self::catch_other_errors($response);

        $order->update_meta_data('_gls_label', $response);
        if (get_option(GLS_Admin::GLS_SETTINGS_SERVICES)[GLS_Admin::SETTING_CHANGE_ORDER_STATUS] === 'yes') {
            $newOrderStatus = get_option(GLS_Admin::GLS_SETTINGS_SERVICES)[GLS_Admin::SETTING_NEW_ORDER_STATUS];
            $order->update_status($newOrderStatus);
        }
        $order->save();

        GLS_Admin_Notice::admin_add_notice(__('Label(s) created successfully', 'gls-woocommerce'),'success','shop_order');
        wp_send_json_success(__('Label(s) created successfully', 'gls-woocommerce'), $response->status);
    }

    /**
     * Trigger DeleteLabel-call and remove it from post meta data if successful.
     */
    public static function delete_label()
    {
        check_ajax_referer('delete-label', 'security');

        /** @var StdClass $response */
        $api = GLS_Api::instance();
        $response = $api->call(GLS()->api_delete_label());

        self::catch_admin_ajax_errors($response, __('Label could not be deleted from the GLS API', 'gls-woocommerce'));

        $order = wc_get_order(GLS()->post('order_id'));
        $order->delete_meta_data('_gls_label');
        $order->save();

        GLS_Admin_Notice::admin_add_notice(__('Label(s) deleted successfully', 'gls-woocommerce'),'success','shop_order');
        wp_send_json_success(__('Label(s) deleted successfully', 'gls-woocommerce'), $response->status);
    }

    /**
     * Add error as notice
     * Translate common errors into
     * @param        $field
     * @param        $error
     * @param string $type
     * @param string $screen_id
     */
    private static function add_error_as_notice($field, $error, $type = 'error', $screen_id = 'shop_order'){
        // Fairly common errors
        if($field === 'addresses.PickupAddress.Name1'){
            if($error === "The value Name1 cannot exceed 30 characters."){
                GLS_Admin_Notice::admin_add_notice(_('Shop name does not fit on the GLS label. The shop name may contain at most 30 characters. Please split in Settings -> General -> Site Title'), $type, $screen_id);
                return;
            }
        }
        GLS_Admin_Notice::admin_add_notice(sprintf(_('Field %s contains errors: %s'), $field, $error), $type, $screen_id);
    }

    /**
     * @param        $errors
     * @param string $type
     * @param string $screen_id
     */
    private static function add_errors_as_notice($errors, $type = 'error', $screen_id = 'shop_order'){
        foreach($errors as $field => $errorArray){
            foreach($errorArray as $error){
                self::add_error_as_notice($field, $error);
            }
        }
    }

    /**
     * @param        $response
     * @param string $message
     */
    private static function catch_admin_ajax_errors($response, $message = null)
    {
        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            GLS_Admin_Notice::admin_add_notice($message ?? $response->message,'error','shop_order');
            if (!empty($response->errors)) {
                self::add_errors_as_notice($response->errors, 'error', 'shop_order');
            }
            wp_send_json_error($message ?? $response->message, $response->status);
        }
    }

    /**
     * @param $response
     */
    private static function capture_frontend_ajax_errors($response)
    {
        if ($response->error || isset($response->statusCode) && $response->statusCode !== 200) {
            $code = $response->statusCode ?: 412;
            wp_send_json_error($response->message, $code);
        }
    }

    /**
     * These elements only exist if some required settings aren't set. We throw the error attached the first
     * available element.
     *
     * @param $response
     */
    private static function check_required_configuration($response, $admin_add_notice = false)
    {
        if (isset($response->username) || isset($response->amountOfShops) || isset($response->passwordLength)) {
            foreach ($response as $item => $message) {
                if ($admin_add_notice) {
                    GLS_Admin_Notice::admin_add_notice(__('The GLS plugin is not configured properly') . ': ' . reset($message),'error','shop_order');
                }
                wp_send_json_error(__('The GLS plugin is not configured properly') . ': ' . reset($message), 401);
            }
        }
    }

    /**
     * @param $response
     */
    private static function catch_other_errors($response)
    {
        if (!isset($response->labels)) {
            foreach ($response as $item => $message) {
                GLS_Admin_Notice::admin_add_notice(__('Invalid request') . ': ' . reset($message),'error','shop_order');
                wp_send_json_error(__('Invalid request') . ': ' . reset($message), 400);
            }
        }
    }
}

GLS_AJAX::init();
