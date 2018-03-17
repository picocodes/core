<?php

namespace MailOptin\Core\Admin\SettingsPage;

use PAnD as PAnD;

ob_start();

class LibsodiumSettingsPage
{
    const slug = MAILOPTIN_LICENSE_SETTINGS_SLUG;

    private static $license_key;

    public function __construct()
    {
        self::$license_key = trim(get_option('mo_license_key'));

        // plugins_loaded hook is used so it is shown as the last sub menu.
        add_action('plugins_loaded', function () {
            add_action('admin_menu', array(__CLASS__, 'register_settings_page'));
        }, 199);

        // do not run premium updater if this is a lite plugin without a saved license key.
        // it is only ran if it is lite and it has a license key saved.
        if (!defined('MAILOPTIN_DETACH_LIBSODIUM') && !empty(self::$license_key)) {
            // unavailability of this class could potentially break all ajax requests.
            if (class_exists('MailOptin\Core\Admin\SettingsPage\LicenseControl')) {
                add_action('admin_init', array(__CLASS__, 'plugin_updater'), 0);
                add_action('admin_init', array(__CLASS__, 'plugin_check_license'), 0);
            }
        }

        add_action('admin_notices', array(__CLASS__, 'license_not_active_notice'));
        add_action('admin_notices', array(__CLASS__, 'license_expired_notice'));
    }

    /**
     * Return license price ID
     *
     * @return bool|string
     */
    public static function mo_price_id()
    {
        return get_option('mo_price_id', false);
    }

    /**
     * Is plugin license valid?
     *
     * @return bool
     */
    public static function mo_is_license_valid()
    {
        $license = get_option('mo_license_status');
        return $license == 'valid' ? true : false;
    }

    /**
     * Is plugin license invalid?
     * @return bool
     */
    public static function mo_is_license_invalid()
    {
        $license = get_option('mo_license_status');
        return $license == 'invalid';
    }

    /**
     * Is plugin license expired?
     *
     * @return bool
     */
    public static function mo_is_license_expired()
    {
        $license = get_option('mo_license_expired_status', 'false');
        return $license == 'true';
    }

    /**
     * Is license empty?
     *
     * @return bool
     */
    public static function mo_is_license_empty()
    {
        $license = get_option('mo_license_key');
        if (false == $license || empty($license)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Was license once active?
     */
    public static function mo_once_active()
    {
        return get_option('mo_license_once_active', false) == 'true' ? true : false;
    }

    /**
     * @return LicenseControl
     */
    public static function license_control_instance()
    {
        return new LicenseControl(
            self::$license_key,
            MAILOPTIN_SYSTEM_FILE_PATH,
            MAILOPTIN_VERSION_NUMBER,
            EDD_MO_ITEM_NAME,
            EDD_MO_ITEM_ID
        );
    }

    /**
     * Check if the plugin license is active
     *
     * @param bool $force if true, re-validate license.
     */
    public static function plugin_check_license($force = false)
    {
        if (defined('DOING_AJAX')) return;

        // only check license if transient doesn't exist
        if (false === get_transient('mo_license_check') || $force === true) {

            // force check license if its lite with a license key saved.
            if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
                wp_version_check(array(), true);
            }

            $response = self::license_control_instance()->check_license();

            if (!empty($response->license)) {

                if ($response->license == 'site_inactive') {
                    self::activate_license();
                }

                if ($response->license == 'valid') {
                    update_option('mo_price_id', $response->price_id);
                    update_option('mo_license_once_active', 'true');
                    update_option('mo_license_expired_status', 'false');
                }

                if ($response->license == 'expired') {
                    update_option('mo_license_expired_status', 'true');
                }

                // $response->license could be valid/invalid/expired.
                update_option('mo_license_status', $response->license);
            }

            set_transient('mo_license_check', 'active', 24 * HOUR_IN_SECONDS);
        }

        if ($force === true) {
            wp_redirect(MAILOPTIN_LICENSE_SETTINGS_PAGE);
            exit;
        }
    }

    public static function register_settings_page()
    {
        add_submenu_page(
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            __('License', 'mailoptin') . ' - MailOptin',
            __('License', 'mailoptin'),
            'manage_options',
            self::slug,
            array(__CLASS__, 'license_page')
        );
    }

    /**
     * License settings page
     */
    public static function license_page()
    {
        $license = get_option('mo_license_key');

        // listen for our activate button to be clicked
        if (isset($_POST['mo_activate_license'])) {
            self::activate_license();
        } else if (isset($_POST['mo_deactivate_license'])) {
            self::deactivate_license();
        } // listen for our activate button to be clicked
        else if (isset($_POST['save_license'])) {
            self::save_license_key();
        } // listen for our activate button to be clicked
        else if (isset($_GET['revalidate_license'])) {
            self::plugin_check_license(true);
        }

        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            add_settings_error(self::slug, 'changes_saved', __('License key updated successfully', 'mailoptin'), 'updated');
        } elseif (isset($_GET['license']) && $_GET['license'] == 'activated') {
            add_settings_error(self::slug, 'valid_license', __('License key activation successful.', 'mailoptin'), 'updated');
        } elseif (isset($_GET['license']) && $_GET['license'] == 'deactivated') {
            add_settings_error(self::slug, 'invalid_license', __('License key deactivation successful.', 'mailoptin'), 'updated');
        }
        ?>

        <div class="wrap">
        <h2><?php _e('MailOptin License', 'mailoptin'); ?>
            <a class="add-new-h2" href="<?php echo add_query_arg('revalidate_license', 'true', MAILOPTIN_LICENSE_SETTINGS_PAGE); ?>">
                <?php _e('Re-validate Saved License Key', 'mailoptin') ?>
            </a>
        </h2>
        <!--	Output Settings error	-->
        <?php settings_errors(); ?>
        <?php self::license_banner(); ?>
        <form method="post">
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php _e('License Key'); ?>
                    </th>
                    <td>
                        <input id="mo_license_key" name="mo_license_key" type="text" class="regular-text" value="<?php esc_attr_e($license); ?>"/>
                        <label class="description" for="mo_plugin_license_key"><?php _e('Enter your license key', 'mailoptin'); ?></label>
                    </td>
                </tr>
                <?php if (false !== $license) { ?>
                    <tr valign="top" id="license_Activate_th">
                        <th scope="row" valign="top">
                            <?php _e('Activate License', 'mailoptin'); ?>
                        </th>
                        <td>
                            <?php if (self::mo_is_license_valid()) { ?>
                                <span style="color:green;"><?php _e('active'); ?></span>
                                <input type="submit" class="button-secondary" name="mo_deactivate_license" value="<?php _e('Deactivate License'); ?>"/>
                                <?php
                            } else {
                                ?>
                                <input type="submit" class="button-secondary" name="mo_activate_license" value="<?php _e('Activate License'); ?>"/>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php wp_nonce_field('mo_plugin_nonce', 'mo_plugin_nonce'); ?>
            <?php submit_button(null, 'primary', 'save_license'); ?>
        </form>
        <?php if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) : ?>
            <div>
                <h3><?php printf(
                        __('License key is only required if you are upgrading from Lite to MailOptin Premium. Want a license key? %sGet it here%s', 'mailoptin'),
                        '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=license_key_page">', '</a>'
                    ); ?>
                </h3>
                <p><?php printf(
                        __('If you are upgrading, kindly check the <a href="%s">WordPress update page</a> after saving your license key to install the MailOptin Premium.', 'mailoptin'),
                        esc_url(self_admin_url('update-core.php?force-check=1'))
                    ); ?>
                </p>
            </div>
        <?php endif; ?>
        <script type="text/javascript">
            (function ($) {
                field = $('input#mo_license_key');
                var initial_value = field.val();
                field.change(function () {
                    $(this).val() != initial_value ? $('tr#license_Activate_th').hide() : $('tr#license_Activate_th').show();
                });
            })(jQuery);

        </script>
        <?php
    }

    /**
     * Save License key to DB
     */
    public static function save_license_key()
    {
        // run a quick security check
        if (!check_admin_referer('mo_plugin_nonce', 'mo_plugin_nonce')) {
            return;
        }

        $old = self::$license_key;
        $new = trim(sanitize_key($_POST['mo_license_key']));

        if ($old && $old != $new) {
            delete_option('mo_license_status'); // new license has been entered, so must reactivate
        }

        update_option('mo_license_key', $new);

        if (!empty($new)) {
            self::activate_license($new);
        }

        wp_redirect(esc_url_raw(add_query_arg('settings-updated', 'true')));
        exit;
    }

    /**
     * Activate License key
     */
    public static function activate_license($license_key = '')
    {
        // run a quick security check
        if (!check_admin_referer('mo_plugin_nonce', 'mo_plugin_nonce')) {
            return;
        }

        $response = self::license_control_instance()->activate_license($license_key);

        if (is_wp_error($response)) {
            add_settings_error(self::slug, 'activation_error', $response->get_error_message());
            return;
        }

        if (is_wp_error($response)) {
            add_settings_error(self::slug, 'activation_error', $response->get_error_message());
            return;
        }

        // $response->license will be either "valid" or "invalid" or even "expired"
        update_option('mo_license_status', $response->license);
        update_option('mo_price_id', $response->price_id);

        if ($response->license == 'invalid') {
            add_settings_error(self::slug, 'invalid_license', 'License key entered is invalid.');
        } elseif ($response->license == 'expired') {
            update_option('mo_license_expired_status', 'true');
        } elseif ($response->license == 'valid') {
            //first time activation
            add_option('mo_license_once_active', 'true');
            update_option('mo_license_expired_status', 'false');
            wp_redirect(add_query_arg('license', 'activated'));
            exit;
        }

    }

    /**
     * Plugin update method
     */
    public static function plugin_updater()
    {
        if (defined('DOING_AJAX')) return;

        self::license_control_instance()->plugin_updater();
    }

    /**
     * Deactivate license
     */
    public static function deactivate_license()
    {
        // run a quick security check
        if (!check_admin_referer('mo_plugin_nonce', 'mo_plugin_nonce')) {
            return;
        } // get out if we didn't click the Activate button

        $response = self::license_control_instance()->deactivate_license();

        if (is_wp_error($response)) {
            add_settings_error(self::slug, 'deactivation_error', $response->get_error_message());
            return;
        }

        // $response->license will be either "deactivated" or "failed"
        if ($response->license == 'deactivated') {
            delete_option('mo_license_status');
        }
        wp_redirect(add_query_arg('license', 'deactivated'));
        exit;
    }

    /**
     * License Banner
     */
    public static function license_banner()
    {
        if (self::mo_is_license_empty()) {
            echo '<div class="mo-banner">' . __('Enter a License Key', 'mailoptin') . '</div><br/><br/><br/><br/>';
        } elseif (self::mo_is_license_valid()) {
            echo '<div class="mo-banner">' . __('You have an active License', 'mailoptin') . '</div><br/><br/><br/><br/>';
        } elseif (self::mo_is_license_invalid()) {
            echo '<div class="mo-banner">' . __('License key is invalid.', 'mailoptin') . '</div><br/><br/><br/><br/>';
        } elseif (self::mo_is_license_expired()) {
            echo '<div class="mo-banner">' . __('License key is expired.', 'mailoptin') . '</div><br/><br/><br/><br/>';
        }
    }

    public static function license_not_active_notice()
    {
        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) return;

        if (!is_super_admin(get_current_user_id())) {
            return;
        }

        if (self::mo_is_license_valid()) {
            return;
        }

        // bail if license is expired as this has its own admin notice.
        if (self::mo_is_license_expired()) {
            return;
        }

        echo '<div class="error notice"><p>' . sprintf(__('%sWelcome to MailOptin Premium!%s Please %s or %s to enable automatic updates.', 'mailoptin'),
                '<strong>',
                '</strong>',
                '<a href="' . MAILOPTIN_LICENSE_SETTINGS_PAGE . '">' . __('activate your license key', 'mailoptin') . '</a>',
                '<a target="_blank" href="https://my.mailoptin.io">' . __('renew it', 'mailoptin') . '</a>') . '</p></div>';
    }

    public static function license_expired_notice()
    {
        if (!PAnD::is_admin_notice_active('mo-license-expired-notice-14')) {
            return;
        }

        if (!is_super_admin(get_current_user_id())) {
            return;
        }

        if (self::mo_is_license_valid()) {
            return;
        }

        // bail if license not expired.
        if (!self::mo_is_license_expired()) {
            return;
        }

        $licenseKey = self::$license_key;
        $download_id = EDD_MO_ITEM_ID;
        $renew_url = !empty(self::$license_key) ? "https://my.mailoptin.io/checkout/?edd_license_key={$licenseKey}&download_id={$download_id}" : 'https://my.mailoptin.io';

        echo '<div data-dismissible="mo-license-expired-notice-14" class="error notice is-dismissible"><p>' .
            sprintf(
                __('Your MailOptin license has expired and you have been downgraded to the LITE version. %sClick here to renew your license%s to regain old data and features. %sLearn more%s', 'mailoptin'),
                "<a target='_blank' href=\"$renew_url\"><strong>", '</strong></a>',
                '<a target="_blank" href="https://mailoptin.io/article/cancel-subscription-expires/">',
                '</a>'
            ) . '</p></div>';
    }

    /**
     * @return LibsodiumSettingsPage
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

}