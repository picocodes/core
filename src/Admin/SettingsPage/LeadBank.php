<?php

namespace MailOptin\Core\Admin\SettingsPage;

use W3Guy\Custom_Settings_Page_Api;

class LeadBank extends AbstractSettingsPage
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_settings_page'));
    }

    public function register_settings_page()
    {
        $hook = add_submenu_page(
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            __('Lead Bank - MailOptin', 'mailoptin'),
            __('Lead Bank', 'mailoptin'),
            'manage_options',
            MAILOPTIN_LEAD_BANK_SETTINGS_SLUG,
            array($this, 'settings_admin_page_callback')
        );

        do_action("mailoptin_leadbank_settings_page", $hook);

        if (!defined('MAILOPTIN_PRO_PLUGIN_TYPE')) {
            add_action('wp_cspa_main_content_area', array($this, 'upsell_settings_page'), 10, 2);
        }
    }

    public function upsell_settings_page()
    {
        ob_start();
        ?>
        <div class="mo-settings-page-disabled">
            <div class="mo-upgrade-plan">
                <div class="mo-text-center">
                    <div class="mo-lock-icon"></div>
                    <h1><?php _e('Lead Bank Locked', 'mailoptin'); ?></h1>
                    <p>
                        <?php printf(
                            __('This is a %sPRO plan%s feature. Your current plan does not include it.', 'mailoptin'),
                            '<strong>',
                            '</strong>');
                        ?>
                    </p>
                    <div class="moBtncontainer mobtnUpgrade">
                        <a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=leadbank_btn" class="mobutton mobtnPush mobtnGreen">
                            <?php _e('Upgrade to Unlock', 'mailoptin'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <img src="<?php echo MAILOPTIN_ASSETS_URL; ?>images/leadbankscreenshot.png">
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
        $instance->option_name('mo_leads');
        $instance->page_header(__('Lead Bank', 'mailoptin'));
        $this->register_core_settings($instance, true);
        $instance->build(true);
    }

    /**
     * @return Leads
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