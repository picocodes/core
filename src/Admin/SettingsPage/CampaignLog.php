<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
use W3Guy\Custom_Settings_Page_Api;

if (!defined('ABSPATH')) {
    exit;
}

class CampaignLog extends AbstractSettingsPage
{
    /** @var Campaign_Log_List */
    protected $campaign_instance;

    public function __construct()
    {
        add_action('plugins_loaded', function () {
            add_action('admin_menu', array($this, 'register_settings_page'));
        }, 40);

        add_filter('set-screen-option', array($this, 'set_screen'), 10, 3);
    }

    public function register_settings_page()
    {
        $hook = add_submenu_page(
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            __('Email Campaign Log - MailOptin', 'mailoptin'),
            __('Email Log', 'mailoptin'),
            'manage_options',
            MAILOPTIN_CAMPAIGN_LOG_SETTINGS_SLUG,
            array($this, 'settings_admin_page_callback')
        );

        add_action("load-$hook", array($this, 'screen_option'));

        add_action("load-$hook", function () {
            add_action('admin_enqueue_scripts', array($this, 'fancybox_scripts'));
        });

        // Hook the Email_Template_List table to Custom_Settings_Page_Api main content filter.
        add_action('wp_cspa_main_content_area', array($this, 'wp_list_table'), 10, 2);
    }

    public function fancybox_scripts()
    {
        wp_enqueue_script('mailoptin-fancybox', MAILOPTIN_ASSETS_URL . 'fancybox/jquery.fancybox.min.js', ['jquery'], false, true);
        wp_enqueue_script('mailoptin-activate-fancybox', MAILOPTIN_ASSETS_URL . 'js/admin/activate-fancybox.js', ['jquery'], false, true);
        wp_enqueue_style('mailoptin-fancybox', MAILOPTIN_ASSETS_URL . 'fancybox/jquery.fancybox.min.css', false, true);
        wp_enqueue_style('mailoptin-activate-fancybox', MAILOPTIN_ASSETS_URL . 'css/admin/fancybox.css', false, true);
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
            'label' => __('Email Log', 'mailoptin'),
            'default' => 10,
            'option' => 'campaign_log_per_page',
        );
        add_screen_option($option, $args);
        $this->campaign_instance = Campaign_Log_List::get_instance();
    }


    /**
     * Build the settings page structure. I.e tab, sidebar.
     */
    public function settings_admin_page_callback()
    {
        $instance = Custom_Settings_Page_Api::instance();
        $instance->option_name('mailoptin_campaign_log');
        $instance->page_header(__('Email Log', 'mailoptin'));
        $this->register_core_settings($instance, true);
        $instance->build(true);
    }

    /**
     * Callback to output content of Email_Template_List table.
     *
     * @param string $content
     * @param string $option_name settings Custom_Settings_Page_Api option name.
     *
     * @return string
     */
    public function wp_list_table($content, $option_name)
    {
        if ($option_name != 'mailoptin_campaign_log') {
            return $content;
        }

        $this->campaign_instance->prepare_items();

        ob_start();
        $this->campaign_instance->display();

        return ob_get_clean();
    }


    /**
     * @return CampaignLog
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