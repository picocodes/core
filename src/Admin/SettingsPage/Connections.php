<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
use W3Guy\Custom_Settings_Page_Api;

if (!defined('ABSPATH')) {
    exit;
}


class Connections extends AbstractSettingsPage
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_settings_page'));

        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('removable_query_args', array($this,'removable_query_args'));
    }

    public function register_settings_page()
    {
        add_submenu_page(
            'mailoptin-settings',
            __('Connections - MailOptin', 'mailoptin'),
            __('Connections', 'mailoptin'),
            'manage_options',
            'mailoptin-connections',
            array($this, 'settings_admin_page_callback')
        );
    }

    public function settings_admin_page_callback()
    {
        do_action('mailoptin_before_connections_settings_page', MAILOPTIN_CONNECTIONS_DB_OPTION_NAME);

        $args = apply_filters('mailoptin_connections_settings_page', array());

        $instance = Custom_Settings_Page_Api::instance($args, MAILOPTIN_CONNECTIONS_DB_OPTION_NAME, __('Connections', 'mailoptin'));
        $this->register_core_settings($instance);
        $instance->build();
    }

    public function admin_notices()
    {
        // handle oauth errors.
        if (isset($_GET['mo-oauth-provider'], $_GET['mo-oauth-error'])) {
            $provider = ucfirst(sanitize_text_field($_GET['mo-oauth-provider']));
            $error_message = strtolower(sanitize_text_field($_GET['mo-oauth-error']));

            echo '<div id="message" class="updated notice is-dismissible">';
            echo '<p>';
            echo "$provider $error_message";
            echo '</p>';
            echo '</div>';
        }
    }

    public function removable_query_args($args)
    {
        $args[] = 'mo-oauth-provider';
        $args[] = 'mo-oauth-error';
        $args[] = 'code';

        return $args;
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}