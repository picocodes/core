<?php

namespace MailOptin\Core;

final class Core
{
    const campaign_log_table_name = 'mo_campaign_log';
    const optin_campaigns_table_name = 'mo_optin_campaigns';
    const optin_campaign_meta_table_name = 'mo_optin_campaignmeta';
    const campaign_log_meta_table_name = 'mo_campaign_logmeta';
    const conversions_table_name = 'mo_conversions';
    const email_campaigns_table_name = 'mo_email_campaigns';
    const email_campaign_meta_table_name = 'mo_email_campaignmeta';

    public function __construct()
    {
        Base::get_instance();
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public static function init()
    {
        $requirements = new RequirementsChecker('MailOptin', array(
            'php' => '5.4',
            'wp' => '4.5'
        ));

        if (!$requirements->satisfied()) {

            if (!function_exists('deactivate_plugins')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            deactivate_plugins(plugin_basename(MAILOPTIN_SYSTEM_FILE_PATH));
            wp_die($requirements->notice());
        }

        // checks passed - load the plugin
        Core::get_instance();

        do_action('mailoptin_loaded');
    }
}