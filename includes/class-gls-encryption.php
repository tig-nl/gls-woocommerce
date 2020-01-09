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

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

defined('ABSPATH') || exit;

class GLS_Encryption
{
    /**
     *
     */
    const GLS_SECRET_FILE = '../gls-secret.key';

    /** @var null $_instance */
    protected static $_instance = null;

    /**
     * Main GLS_Encryption Instance.
     *
     * Ensures only one instance of GLS_Encryption is loaded or can be loaded.
     *
     * @return GLS_Encryption Main instance
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
     * GLS_Encryption constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     *
     */
    public function init()
    {
        if (!$this->hasSecretKey()) {
            $this->writeSecretKeyToFile();
        }
    }

    /**
     * @return bool
     */
    public function hasSecretKey()
    {
        //GLS_ABSPATH;
        $file = plugin_dir_path( __FILE__ ) . self::GLS_SECRET_FILE;
        return file_exists($file) && filesize($file) > 0;
    }

    /**
     * Generates a random Key and writes it to a file
     * 
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function writeSecretKeyToFile()
    {
        $key  = Key::createNewRandomKey();
        $objectData = serialize($key);

        $file = plugin_dir_path( __FILE__ ) . self::GLS_SECRET_FILE;
        file_put_contents($file, $objectData);
    }

    /**
     * Retrieve secret key for encryption/decryption
     *
     * @return mixed
     */
    public static function getSecretKey()
    {
        $file = plugin_dir_path( __FILE__ ) . self::GLS_SECRET_FILE;
        $key = file_get_contents($file);
        return unserialize($key);
    }

    /**
     * @param $value
     * @return string
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     */
    public static function Decrypt($value)
    {
        $key = self::getSecretKey();
        return Crypto::Decrypt($value, $key);
    }

    /**
     * @param $value
     * @return string
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function Encrypt($value)
    {
        $key = self::getSecretKey();
        return Crypto::Encrypt($value, $key);
    }

    /**
     * Config section for type 'encrypt_text' and 'encrypt_password'
     *
     * @param $value
     * @param string $sudo_type
     */
    public function encrypt_field($value, $sudo_type = 'text')
    {
        $option_value = '';

        try {
            $option_value = ($value['value']) ? self::Decrypt($value['value']) : '';
        }
        catch (Exception $e) {
            //nothing
        }

        // Description handling.
        $field_description = WC_Admin_Settings::get_field_description( $value );
        $description       = $field_description['description'];
        $tooltip_html      = $field_description['tooltip_html'];

        ?><tr valign="top">
        <th scope="row" class="titledesc">
            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
        </th>
        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $sudo_type ) ); ?>">
            <input
                name="<?php echo esc_attr( $value['id'] ); ?>"
                id="<?php echo esc_attr( $value['id'] ); ?>"
                type="<?php echo esc_attr( $sudo_type ); ?>"
                style="<?php echo esc_attr( $value['css'] ); ?>"
                value="<?php echo esc_attr( $option_value ); ?>"
                class="<?php echo esc_attr( $value['class'] ); ?>"
                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
            /><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; // WPCS: XSS ok. ?>
        </td>
        </tr>
        <?php
    }
}