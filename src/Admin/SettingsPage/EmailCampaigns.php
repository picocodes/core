<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use W3Guy\Custom_Settings_Page_Api;

class EmailCampaigns extends AbstractSettingsPage
{
    /**
     * @var Email_Campaign_List
     */
    protected $email_campaigns_instance;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_settings_page'));

        add_filter('set-screen-option', array($this, 'set_screen'), 10, 3);
    }

    public function register_settings_page()
    {
        $hook = add_submenu_page(
            'mailoptin-settings',
            __('Email Campaigns - MailOptin', 'mailoptin'),
            __('Email Campaigns', 'mailoptin'),
            'manage_options',
            'mailoptin-email-campaigns',
            array($this, 'settings_admin_page_callback')
        );


        add_action("load-$hook", array($this, 'screen_option'));

    }

    /**
     * Save screen option.
     *
     * @param string $status
     * @param string $option
     * @param string $value
     *
     * @return mixed
     */
    public function set_screen($status, $option, $value)
    {
        return $value;
    }

    /**
     * Screen options
     */
    public function screen_option()
    {
        $option = 'per_page';
        $args = array(
            'label' => __('Email Campaigns', 'mailoptin'),
            'default' => 8,
            'option' => 'email_campaign_per_page',
        );
        add_screen_option($option, $args);
        $this->email_campaigns_instance = Email_Campaign_List::get_instance();
    }


    /**
     * Build the settings page structure. I.e tab, sidebar.
     */
    public function settings_admin_page_callback()
    {
        if (!empty($_GET['view']) && $_GET['view'] == 'add-new-email-campaign') {
            AddEmailCampaign::get_instance()->settings_admin_page();
        } else {
            // Hook the OptinCampaign_List table to Custom_Settings_Page_Api main content filter.
            add_action('wp_cspa_main_content_area', array($this, 'wp_list_table'), 10, 2);
            add_action('wp_cspa_before_closing_header', [$this, 'add_new_email_campaign']);

            $instance = Custom_Settings_Page_Api::instance();
            $instance->option_name(MO_EMAIL_CAMPAIGNS_WP_OPTION_NAME);
            $instance->page_header(__('Email Campaigns', 'mailoptin'));
            $this->register_core_settings($instance, true);
            $instance->build(true);

        }
    }

    public function add_new_email_campaign()
    {
        $url = add_query_arg('view', 'add-new-email-campaign', MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE);
        echo "<a class=\"add-new-h2\" href=\"$url\">" . __('Add New', 'mailoptin') . '</a>';
    }


    /**
     * Callback to output content of OptinCampaign_List table.
     *
     * @param string $content
     * @param string $option_name settings Custom_Settings_Page_Api option name.
     *
     * @return string
     */
    public function wp_list_table($content, $option_name)
    {
        if ($option_name != MO_EMAIL_CAMPAIGNS_WP_OPTION_NAME) {
            return $content;
        }

        $this->email_campaigns_instance->prepare_items();

        $this->email_campaigns_instance->display();

        return ob_get_clean();
    }


    /**
     * @return EmailCampaigns
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