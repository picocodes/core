<?php

namespace MailOptin\Core\Admin\SettingsPage;

use MailOptin\AdvanceAnalytics\SettingsPage;
use MailOptin\Libsodium\LibsodiumSettingsPage;
use W3Guy\Custom_Settings_Page_Api;

class AdvanceAnalytics extends AbstractSettingsPage
{
    public function __construct()
    {
        add_action('plugins_loaded', function () {
            add_action('admin_menu', array($this, 'register_settings_page'));
        }, 20);
    }

    public function register_settings_page()
    {
        $hook = add_submenu_page(
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            __('Statistics - MailOptin', 'mailoptin'),
            __('Statistics', 'mailoptin'),
            'manage_options',
            MAILOPTIN_ADVANCE_ANALYTICS_SETTINGS_SLUG,
            array($this, 'settings_admin_page_callback')
        );

        do_action("mailoptin_advance_analytics_settings_page", $hook);

        if (!defined('MAILOPTIN_PRO_PLUGIN_TYPE') || !defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            add_filter('wp_cspa_main_content_area', array($this, 'upsell_settings_page'), 10, 2);
        }
    }

    public function upsell_settings_page($content, $option_name)
    {
        if ($option_name != 'mo_analytics') {
            return $content;
        }

        $url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=advanceanalytics_btn';

        if (class_exists('MailOptin\Libsodium\LibsodiumSettingsPage')) {
            $license_key = LibsodiumSettingsPage::license_key();
            if (!empty($license_key)) {
                $url = sprintf('https://my.mailoptin.io/?mo_plan_upgrade=%s&license_key=%s', 'pro', $license_key);
            }
        }

        ob_start();
        ?>
        <div class="mo-settings-page-disabled">
            <div class="mo-upgrade-plan">
                <div class="mo-text-center">
                    <div class="mo-lock-icon"></div>
                    <h1><?php _e('Advance Analytics Locked', 'mailoptin'); ?></h1>
                    <p>
                        <?php printf(
                            __('Get important metrics and insights to improve your lead-generation strategy and make data-driven decisions.', 'mailoptin'),
                            '<strong>',
                            '</strong>'
                        ); ?>
                    </p>
                    <p>
                        <?php printf(
                            __('This is a %sPRO plan%s feature. Your current plan does not include it.', 'mailoptin'),
                            '<strong>',
                            '</strong>');
                        ?>
                    </p>
                    <div class="moBtncontainer mobtnUpgrade">
                        <a target="_blank" href="<?= $url; ?>" class="mobutton mobtnPush mobtnGreen">
                            <?php _e('Upgrade to Unlock', 'mailoptin'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <img src="<?php echo MAILOPTIN_ASSETS_URL; ?>images/advanceanalytics.png">
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Build the settings page structure. I.e tab, sidebar.
     */
    public function settings_admin_page_callback()
    {
        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('mo_analytics');
        $instance->page_header(__('Optin Statistics', 'mailoptin'));
        $this->register_core_settings($instance, true);
        if (defined('MAILOPTIN_PRO_PLUGIN_TYPE') && defined('MAILOPTIN_DETACH_LIBSODIUM') && method_exists(SettingsPage::get_instance(), 'analytic_chart_sidebar')) {
            $instance->sidebar(SettingsPage::get_instance()->analytic_chart_sidebar());
        }
        $instance->build(!defined('MAILOPTIN_PRO_PLUGIN_TYPE'));
    }

    /**
     * @return AdvanceAnalytics
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