<?php

namespace MailOptin\Core\Admin\SettingsPage;

use MailOptin\Libsodium\LibsodiumSettingsPage;
use W3Guy\Custom_Settings_Page_Api;

class LeadBank extends AbstractSettingsPage
{
    public function settings_admin_page()
    {
        if (!apply_filters('mailoptin_enable_leadbank', false)) {
            add_filter('wp_cspa_main_content_area', array($this, 'upsell_settings_page'), 10, 2);
        }

        do_action("mailoptin_leadbank_settings_page");

        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('mo_leads');
        $instance->page_header(__('Lead Bank', 'mailoptin'));
        $this->register_core_settings($instance);
        $instance->build(true);
    }

    public function upsell_settings_page($content, $option_name)
    {
        if ($option_name != 'mo_leads') {
            return $content;
        }

        $url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=leadbank_btn';

        ob_start();
        ?>
        <div class="mo-settings-page-disabled">
            <div class="mo-upgrade-plan">
                <div class="mo-text-center">
                    <div class="mo-lock-icon"></div>
                    <h1><?php _e('Lead Bank Locked', 'mailoptin'); ?></h1>
                    <p>
                        <?php printf(
                            __('LeadBank records or saves backup of all leads and conversions that happen on your site.', 'mailoptin'),
                            '<strong>',
                            '</strong>');
                        ?>
                    </p>
                    <p>
                        <?php _e('Your current plan does not include this feature.', 'mailoptin');
                        ?>
                    </p>
                    <div class="moBtncontainer mobtnUpgrade">
                        <a target="_blank" href="<?= $url; ?>" class="mobutton mobtnPush mobtnGreen">
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
     * @return LeadBank
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