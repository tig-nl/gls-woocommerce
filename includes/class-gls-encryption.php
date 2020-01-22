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

class GLS_Encryption
{
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
     * Retrieve secret key for encryption/decryption
     *
     * @return mixed
     */
    public static function getSecretKey()
    {
        return $key = substr(NONCE_SALT,0,32);
    }

    /**
     * @param $value
     * @return string
     */
    public static function Decrypt($value)
    {
        $key = self::getSecretKey();
        $decoded = base64_decode($value);
        $nonce = substr($decoded, 0, 24);
        $ciphertext = substr($decoded, 24);
        return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $nonce,
            $nonce,
            $key
        );
    }

    /**
     * @param $value
     * @return string
     * @throws SodiumException
     */
    public static function Encrypt($value)
    {
        $key = self::getSecretKey();
        $nonce = random_bytes(24);
        return base64_encode(
            $nonce . sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $value,
                $nonce,
                $nonce,
                $key
            )
        );
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