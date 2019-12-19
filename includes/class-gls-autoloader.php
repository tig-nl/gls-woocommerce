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
 * Autoloader class.
 */
class GLS_Autoloader extends WC_Autoloader
{
    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * GLS_Autoloader constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');
        }

        spl_autoload_register(
            array(
                $this,
                'autoload'
            )
        );

        $this->include_path = untrailingslashit(plugin_dir_path(GLS_PLUGIN_FILE)) . '/includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param string $class Class name.
     *
     * @return string
     */
    private function get_file_name_from_class($class)
    {
        return 'class-' . str_replace('_', '-', $class) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param string $path File path.
     *
     * @return bool Successful or not.
     */
    private function load_file($path)
    {
        if ($path && is_readable($path)) {
            include_once $path;

            return true;
        }

        return false;
    }

    /**
     * Auto-load GLS classes on demand to reduce memory consumption.
     *
     * @param string $class Class name.
     */
    public function autoload($class)
    {
        $class = strtolower($class);

        if (0 !== strpos($class, 'gls_')) {
            parent::autoload($class);

            return;
        }

        $file = $this->get_file_name_from_class($class);
        $path = '';

        if (0 === strpos($class, 'wc_shipping_')) {
            $path = $this->include_path . 'shipping/' . substr(str_replace('_', '-', $class), 12) . '/';
        } elseif (0 === strpos($class, 'gls_admin_meta_box')) {
            $path = $this->include_path . 'admin/meta-boxes/';
        } elseif (0 === strpos($class, 'wc_admin')) {
            $path = $this->include_path . 'admin/';
        } elseif (0 === strpos($class, 'gls_option_')) {
            $path = $this->include_path . 'options/';
        }

        if (empty($path) || !$this->load_file($path . $file)) {
            $this->load_file($this->include_path . $file);
        }
    }
}

new GLS_Autoloader();
