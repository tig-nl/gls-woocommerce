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

if (class_exists('GLS_Settings_Delivery_Options', false)) {
    return new GLS_Settings_Delivery_Options();
}

/**
 * Class GLS_Settings_Delivery_Options
 */
class GLS_Settings_Delivery_Options extends WC_Settings_Page
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id    = 'tig_gls';
        $this->label = _x('GLS', 'Settings tab label', 'gls-woocommerce');

        // @formatter:off
        add_action('woocommerce_admin_field_services', array($this, 'services_settings'));
        add_action('woocommerce_admin_field_delivery_options', array($this, 'delivery_options_settings'));
        add_action('woocommerce_admin_field_api_check', array($this,'api_check_settings'));
        // @formatter:on

        parent::__construct();
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            ''                 => __('API Configuration', 'gls-woocommerce'),
            'delivery_options' => __('Delivery Options', 'gls-woocommerce')
        );

        return apply_filters('tig_gls_get_sections_' . $this->id, $sections);
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Section being shown.
     *
     * @return array
     */
    public function get_settings($current_section = '')
    {
        $settings = array();

        if ('' === $current_section) {
            $settings = apply_filters(
                'tig_gls_api_configuration_settings',
                array(
                    array(
                        'title' => __('API Configuration', 'gls-woocommerce'),
                        'desc'  => __('Add your API credentials to connect WooCommerce to the GLS API.', 'gls-woocommerce'),
                        'type'  => 'title',
                        'id'    => 'api_configuration_options'
                    ),
                    array(
                        'title'   => __('Test mode', 'gls-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Use test mode in staging or development environments', 'gls-woocommerce'),
                        'default' => 'no',
                        'id'      => GLS_Admin::GLS_SETTINGS_API . '[test_mode]'
                    ),
                    array(
                        'title' => __('Username', 'gls-woocommerce'),
                        'type'  => 'text',
                        'id'    => GLS_Admin::GLS_SETTINGS_API . '[username]'
                    ),
                    array(
                        'title' => __('Password', 'gls-woocommerce'),
                        'type'  => 'password',
                        'id'    => GLS_Admin::GLS_SETTINGS_API . '[password]'
                    ),
                    array(
                        'title' => __('Subscription key', 'gls-woocommerce'),
                        'type'  => 'password',
                        'id'    => GLS_Admin::GLS_SETTINGS_API . '[subscription_key]'
                    ),
                    array(
                        'title' => __('Test credentials', 'gls-woocommerce'),
                        'type'  => 'api_check',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id'   => 'api_configuration_options'
                    )
                )
            );
        }

        if ('delivery_options' === $current_section) {
            $settings = apply_filters(
                'tig_gls_delivery_options_settings',
                array(
                    array(
                        'title' => __('Services', 'gls-woocommerce'),
                        'desc'  => __('Configure the display of the available delivery options in checkout.'),
                        'type'  => 'title',
                        'id'    => 'services_options'
                    ),
                    array(
                        'title'   => __('Cut-off Time', 'gls-woocommerce'),
                        'desc'    => __('Deadline at which an order can be placed in order to be processed.', 'gls-woocommerce'),
                        'type'    => 'select',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[cutoff_time]',
                        'options' => $this->generateTimeIntervals(),
                        'default' => '17:00'
                    ),
                    array(
                        'title'   => __('Processing Time', 'gls-woocommerce'),
                        'desc'    => __('The time (in days) it takes to process and package an order before it\'s shipped.', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[processing_time]',
                        'min'     => '0',
                        'default' => '0'
                    ),
                    array(
                        'title'   => __('Label format', 'gls-woocommerce'),
                        'desc'    => __('Which label format do you need', 'gls-woocommerce'),
                        'type'    => 'select',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[label_format]',
                        'options' => $this->getLabelFormat(),
                        'default' => 'pdfA6S'
                    ),
                    array(
                        'title'   => __('Label Margin Top (only PDF A4)', 'gls-woocommerce'),
                        'desc'    => __('distance in mm', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[label_margin_top_a4]',
                        'min'     => '0',
                        'default' => '0'
                    ),
                    array(
                        'title'   => __('Label Margin Left (only PDF A4)', 'gls-woocommerce'),
                        'desc'    => __('distance in mm', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[label_margin_left_a4]',
                        'min'     => '0',
                        'default' => '0'
                    ),
                    array(
                        'title'   => __('No. of Shops to Display', 'gls-woocommerce'),
                        'desc'    => __('Number of ParcelShops to display in the ShopDelivery-tab in checkout.', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[display_shops]',
                        'min'     => '1',
                        'default' => '5'
                    ),
                    array(
                        'title'   => __('Enable ShopReturnService'),
                        'desc'    => __('Enable this to offer easy returns to your customers. A return label is generated along with every delivery label.'),
                        'type'    => 'checkbox',
                        'id'      => GLS_Admin::GLS_SETTINGS_SERVICES . '[shop_return]',
                        'default' => 'yes'
                    ),
                    array(
                        'type' => 'services'
                    ),
                    array(
                        'type' => 'sectionend',
                        'id'   => 'services_options'
                    ),
                    array(
                        'title' => __('Delivery Options', 'gls-woocommerce'),
                        'desc'  => __('Available delivery options are listed below and can be enabled/disabled to control their visibility on the frontend.', 'gls-woocommerce'),
                        'type'  => 'title',
                        'id'    => 'delivery_options_options',
                    ),
                    array(
                        'type' => 'delivery_options',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id'   => 'delivery_options_options',
                    )
                )
            );
        }

        return apply_filters('tig_gls_get_settings_' . $this->id, $settings, $current_section);
    }

    /**
     * @param int    $lower
     * @param int    $upper
     * @param int    $step
     * @param string $format
     *
     * @return array
     * @throws Exception
     */
    public function generateTimeIntervals($lower = 0, $upper = 86400, $step = 1800, $format = 'H:i')
    {
        $times = array();

        foreach (range($lower, $upper, $step) as $interval) {
            $increment = date('H:i', $interval);
            list($hour, $minutes) = explode(':', $increment);
            $date                       = new DateTime($hour . ':' . $minutes);
            $times[(string) $increment] = $date->format($format);
        }

        return $times;
    }

    /**
     * @return array
     */
    public function getLabelFormat()
    {
        return [   'pdfA6S' => __('PDF (A6)'),
            'pdf2A4' => __('PDF (A4, 2 labels per pagina)'),
            'pdf4A4' => __('PDF (A4, 4 labels per pagina)'),
        ];
    }

    /**
     * Output delivery options settings.
     */
    public function api_check_settings()
    {
        $validation = new GLS_Api_Validate_Login();
        $response = $validation->call();
        if ($response->status == 200 && $response->error == false):
            ?>
            <tr valign="top">
                <td class="gls_api_check_wrapper" colspan="2">
                    <div id="api_moderated_ok" class="updated inline"><p><?php _e('Api gegevens zijn correct.');?></p></div>
                </td>
            </tr>
        <?php
        else:
            ?>
            <tr valign="top">
                <td class="gls_api_check_wrapper" colspan="2">
                    <div id="api_moderated_notok" class="error inline"><p><?php _e('Api gegevens zijn nog niet correct, voer de juiste informatie in.');?></p></div>
                </td>
            </tr>
        <?php
        endif;
    }

    /**
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        // Load options so we can show any global options they may have.
        $delivery_options = GLS()->delivery_options->available_delivery_options();

        if ($current_section) {
            foreach ($delivery_options as $option) {
                if (in_array(
                    $current_section, array(
                    $option->id,
                    sanitize_title(get_class($option))
                ), true
                )) {
                    if (isset($_GET['toggle_enabled'])) {
                        $enabled = $option->get_option('enabled');

                        if ($enabled) {
                            $option->settings['enabled'] = wc_string_to_bool($enabled) ? 'no' : 'yes';
                        }
                    }
                    $option->admin_options();
                    break;
                }
            }
        }
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    public function services_settings()
    {
        ?>
        <tbody>

        </tbody>
        <?php
    }

    /**
     * Output delivery options settings.
     */
    public function delivery_options_settings()
    {
        ?>
        <tr valign="top">
            <td class="gls_delivery_options_wrapper" colspan="2">
                <table class="gls_options widefat" cellspacing="0" aria-describedby="delivery_options_options-description">
                    <thead>
                    <tr>
                        <?php
                        $default_columns = array(
                            'name'           => __('Delivery Option', 'gls-woocommerce'),
                            'description'    => __('Description', 'gls-woocommerce'),
                            'additional_fee' => __('Additional Fee', 'gls-woocommerce'),
                            'status'         => __('Enabled', 'gls-woocommerce')
                        );

                        $columns = apply_filters('tig_gls_delivery_options_setting_columns', $default_columns);

                        foreach ($columns as $key => $column) {
                            echo '<th class="' . esc_attr($key) . '">' . esc_html($column) . '</th>';
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach (GLS()->delivery_options->available_delivery_options() as $option) {

                        echo '<tr data-option_id="' . esc_attr($option->id) . '">';

                        foreach ($columns as $key => $column) {
                            if (!array_key_exists($key, $default_columns)) {
                                do_action('tig_gls_delivery_options_setting_column_' . $key, $option);
                                continue;
                            }

                            $width = '';

                            if (in_array($key, array('status'), true)) {
                                $width = '1%';
                            }

                            $method_title   = $option->get_method_title() ?: $option->get_title();
                            $additional_fee = $option->get_additional_fee() ?: 0;

                            echo '<td class="' . esc_attr($key) . '" width="' . esc_attr($width) . '">';

                            switch ($key) {
                                case 'name':
                                    echo wp_kses_post($method_title);
                                    break;
                                case 'description':
                                    echo wp_kses_post($option->get_method_description());
                                    break;
                                case 'additional_fee':
                                    echo "<input type='short wc_input_price' value='$additional_fee' name='additional_fee[$option->id]' />";
                                    break;
                                case 'status':
                                    echo '<a class="gls-delivery-option-method-toggle-enabled" href="' . esc_url(admin_url('admin.php?page=wc-settings&tab=tig_gls&section=' . strtolower($option->id))) . '">';
                                    if (wc_string_to_bool($option->enabled)) {
                                        /* Translators: %s Payment gateway name. */
                                        echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . esc_attr(sprintf(__('The "%s" delivery option is currently enabled', 'gls-woocommerce'), $method_title)) . '">' . esc_attr__('Yes', 'woocommerce') . '</span>';
                                    } else {
                                        /* Translators: %s Payment gateway name. */
                                        echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . esc_attr(sprintf(__('The "%s" delivery option is currently disabled', 'gls-woocommerce'), $method_title)) . '">' . esc_attr__('No', 'woocommerce') . '</span>';
                                    }
                                    echo '</a>';
                                    break;
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }

    /**
     * Save settings.
     */
    public function save()
    {
        global $current_section;

        // TODO: Fix bug where updated settings aren't show immediately after saving changes. They are updated in the database, however...
        if (current_user_can('manage_woocommerce') && isset($_POST['additional_fee'])) {
            $delivery_options = GLS()->delivery_options->available_delivery_options();
            $additional_fee   = wc_clean(wp_unslash($_POST['additional_fee']));

            foreach ($delivery_options as $option) {
                if (!array_search($additional_fee[$option->id], $additional_fee, true)) {
                    continue;
                }

                $option->update_option('additional_fee', $additional_fee[$option->id]);
            }
        }

        // TODO: Encrypt storage of passwords in database.
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::save_fields($settings);
    }
}

return new GLS_Settings_Delivery_Options();
