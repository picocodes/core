<?php
/**
 * Copyright (C) 2016  Agbonghama Collins <me@w3guy.com>
 */

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use MailOptin\Core\PluginSettings\Templates;
use MailOptin\Core\Repositories\ConnectionsRepository;
use MailOptin\Core\Repositories\EmailCampaignRepository;
use MailOptin\Core\Repositories\EmailTemplatesRepository;
use MailOptin\Core\Repositories\OptinThemesRepository;
use W3Guy\Custom_Settings_Page_Api;

class AddEmailCampaign extends AbstractSettingsPage
{
    /**
     * Array of email campaign types available.
     *
     * @return array
     */
    public function email_campaign_types()
    {
        return apply_filters('mo_email_campaign_types', [
            'new_publish_post' => __('New Post Notification', 'mailoptin')
        ]);
    }

    /**
     * Back to campaign overview button.
     */
    public function back_to_optin_overview()
    {
        $url = MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE;
        echo "<a class=\"add-new-h2\" href=\"$url\">" . __('Back to Overview', 'mailoptin') . '</a>';
    }

    /**
     * Sub-menu header for optin theme types.
     */
    public function add_email_campaign_settings_header()
    {
        if (!empty($_GET['page']) && $_GET['page'] == 'mailoptin-email-campaigns') {
            ?>
            <div class="mailoptin-optin-new-list mailoptin-optin-clear">
                <strong><?php _e('Campaign Title', 'mailoptin'); ?></strong>
                <input type="text" name="mailoptin-optin-campaign" id="mailoptin-add-campaign-title" placeholder="<?php _e('Enter a title for this campaign...', 'mailoptin'); ?>">
            </div>
            <div class="mailoptin-optin-new-list mailoptin-new-toolbar mailoptin-optin-clear">
                <strong><?php _e('Select Campaign Type', 'mailoptin'); ?></strong>
                <i class="fa fa-spinner fa-pulse fa-spin"></i>
                <?php $this->_build_campaign_types_select_dropdown(); ?>
            </div>
        <?php }
    }

    /**
     * Build the settings page structure. I.e tab, sidebar.
     */
    public function settings_admin_page()
    {
        add_action('wp_cspa_before_closing_header', [$this, 'back_to_optin_overview']);
        add_action('wp_cspa_before_post_body_content', array($this, 'add_email_campaign_settings_header'), 10, 2);
        add_filter('wp_cspa_main_content_area', [$this, 'campaign_available_email_templates']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->page_header(__('Add New Email Campaign', 'mailoptin'));
        $this->register_core_settings($instance, true);
        $instance->build(true);
    }

    /**
     * Email Campaign types select dropdown
     */
    protected function _build_campaign_types_select_dropdown()
    {
        echo '<select name="mo_email_newsletter_title"   id="mo-email-newsletter-title">';
        echo sprintf('<option value="...">%s</option>', __('Select...', 'mailoptin'));
        foreach ($this->email_campaign_types() as $key => $value) {
            echo sprintf('<option value="%s">%s</option>', $key, $value);
        }
        echo '</select>';

    }

    /**
     * Display available email template for selected campaign type.
     */
    public function campaign_available_email_templates()
    {
        $this->new_publish_post_email_templates();

        do_action('mo_campaign_available_email_templates');
    }

    /**
     * New publish post email templates.
     */
    public function new_publish_post_email_templates()
    {
        $campaign_type = EmailCampaignRepository::NEW_PUBLISH_POST;

        echo '<div id="notifType_new_publish_post" class="mailoptin-email-templates mailoptin-template-clear" style="display:none">';
        foreach (EmailTemplatesRepository::get_all() as $email_template) {

            $template_name = $email_template['name'];
            $template_class = $email_template['template_class'];
            $screenshot = $email_template['screenshot'];
            ?>
            <div id="mailoptin-email-template-list"
                 class="mailoptin-email-template mailoptin-email-template-<?php echo $template_class; ?>"
                 data-email-template="<?php echo $template_class; ?>"
                 data-campaign-type="<?php echo $campaign_type; ?>">
                <div class="mailoptin-email-template-screenshot">
                    <img src="<?php echo $screenshot; ?>" alt="<?php echo $template_name; ?>">
                </div>
                <h3 class="mailoptin-email-template-name"><?php echo $template_name . ' ' . __('Template', 'mailoptin'); ?></h3>
                <div class="mailoptin-email-template-actions">
                    <a class="button button-primary mailemail-template-select"
                       data-email-template="<?php echo $template_class; ?>"
                       data-campaign-type="<?php echo $campaign_type; ?>"
                       title="<?php _e('Select this template', 'mailoptin'); ?>">
                        <?php _e('Select Template', 'mailoptin'); ?>
                    </a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }


    /**
     * @return AddEmailCampaign
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