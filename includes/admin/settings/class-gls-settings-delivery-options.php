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

        add_action(
            'woocommerce_admin_field_services', array(
                $this,
                'services_settings'
            )
        );

        add_action(
            'woocommerce_admin_field_delivery_options', array(
                $this,
                'delivery_options_settings'
            )
        );

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
                        'id'      => 'tig_gls_api[test_mode]'
                    ),
                    array(
                        'title' => __('Username', 'gls-woocommerce'),
                        'type'  => 'text',
                        'id'    => 'tig_gls_api[username]'
                    ),
                    array(
                        'title' => __('Password', 'gls-woocommerce'),
                        'type'  => 'password',
                        'id'    => 'tig_gls_api[password]'
                    ),
                    array(
                        'title' => __('Subscription key', 'gls-woocommerce'),
                        'type'  => 'password',
                        'id'    => 'tig_gls_api[subscription_key]'
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
                        'id'      => 'tig_gls_services[cutoff_time]',
                        'options' => $this->generateTimeIntervals(),
                        'default' => '17:00'
                    ),
                    array(
                        'title'   => __('Processing Time', 'gls-woocommerce'),
                        'desc'    => __('The time (in days) it takes to process and package an order before it\'s shipped.', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => 'tig_gls_services[processing_time]',
                        'min'     => '0',
                        'default' => '0'
                    ),
                    array(
                        'title'   => __('No. of Shops to Display', 'gls-woocommerce'),
                        'desc'    => __('Number of ParcelShops to display in the ShopDelivery-tab in checkout.', 'gls-woocommerce'),
                        'type'    => 'number',
                        'id'      => 'tig_gls_services[display_shops]',
                        'min'     => '1',
                        'default' => '5'
                    ),
                    array(
                        'title'   => __('Enable ShopReturnService'),
                        'desc'    => __('Enable this to offer easy returns to your customers. A return label is generated along with every delivery label.'),
                        'type'    => 'checkbox',
                        'id'      => 'tig_gls_services[shop_return]',
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
     * Output the settings.
     */
    public function output()
    {
        global $current_section;

        // Load options so we can show any global options they may have.
        $delivery_options = GLS()->delivery_options->delivery_options();

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
                    foreach (GLS()->delivery_options->delivery_options() as $option) {

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
                                    echo "<input type='number' value='$additional_fee' name='additional_fee[$option->id]' />";
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
            $delivery_options = GLS()->delivery_options->delivery_options();
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

        parent::save();
    }
}

return new GLS_Settings_Delivery_Options();
