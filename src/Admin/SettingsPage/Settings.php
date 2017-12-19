<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
use MailOptin\Core\Repositories\OptinCampaignsRepository;
use W3Guy\Custom_Settings_Page_Api;

if (!defined('ABSPATH')) {
    exit;
}

class Settings extends AbstractSettingsPage
{
    public function __construct()
    {
        $this->init_menu();
        add_action('admin_menu', array($this, 'register_settings_page'));
        add_action('wp_cspa_persist_settings', array($this, 'check_for_mailoptin_affiliate_check'), 10, 2);
        add_action('admin_init', [$this, 'clear_optin_cache']);
    }

    public function register_settings_page()
    {
        add_submenu_page(
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            __('Settings - MailOptin', 'mailoptin'),
            __('Settings', 'mailoptin'),
            'manage_options',
            MAILOPTIN_SETTINGS_SETTINGS_SLUG,
            array($this, 'settings_admin_page_callback')
        );
    }

    /**
     * When clear cache button is clicked, do justice.
     */
    public function clear_optin_cache()
    {
        if (defined('DOING_AJAX')) return;

        if (isset($_GET['clear-optin-cache']) && $_GET['clear-optin-cache'] == 'true') {
            OptinCampaignsRepository::burst_all_cache();
            wp_redirect(add_query_arg('optin-cache', 'cleared', MAILOPTIN_SETTINGS_SETTINGS_PAGE));
            exit;
        }
    }

    /**
     * If changes is made to the mailoptin affiliate url field, clear cache.
     */
    public function check_for_mailoptin_affiliate_check($input, $option_name)
    {
        // Send an initial check in on settings save
        $old_data = get_option(MAILOPTIN_SETTINGS_DB_OPTION_NAME, []);
        $old_data = $old_data['mailoptin_affiliate_url'];
        $new_data = isset($input['mailoptin_affiliate_url']) ? $input['mailoptin_affiliate_url'] : '';

        if ($option_name == MAILOPTIN_SETTINGS_DB_OPTION_NAME && $old_data != $new_data) {
            OptinCampaignsRepository::burst_all_cache();
        }
    }

    public function settings_admin_page_callback()
    {
        $clear_optin_cache_url = add_query_arg('clear-optin-cache', 'true', MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE);
        $args = array(
            'general_settings' => apply_filters('mailoptin_settings_general_settings_page', array(
                    'section_title' => __('General Settings', 'mailoptin'),
                    'remove_plugin_data' => array(
                        'type' => 'checkbox',
                        'label' => __('Remove Data on Uninstall?', 'mailoptin'),
                        'description' => __('Check this box if you would like MailOptin to completely remove all of its data when uninstalled.', 'mailoptin'),
                    ),
                    'allow_tracking' => array(
                        'type' => 'checkbox',
                        'label' => __('Allow Usage Tracking?', 'mailoptin'),
                        'description' => __('Allow MailOptin to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter. No sensitive data is tracked. Ever.', 'mailoptin'),
                    ),
                    'mailoptin_affiliate_url' => array(
                        'type' => 'text',
                        'label' => __('MailOptin Affiliate Link', 'mailoptin'),
                        'description' => sprintf(
                            __('You can earn money by promoting MailOptin! %1$sJoin our affiliate program%2$s, and paste your %1$saffiliate link here%2$s. Once entered, it will replace the default MailOptin branding link on your optins.', 'mailoptin'),
                            '<a href="https://mailoptin.io/affiliates/" target="_blank">',
                            '</a>'
                        ),
                    )
                )
            ),
            'optin_campaign_settings' => apply_filters('mailoptin_settings_optin_campaign_settings_page', array(
                    'section_title' => __('Optin Campaign Settings', 'mailoptin'),
                    'clear_optin_cache' => array(
                        'type' => 'custom_field_block',
                        'label' => __('Clear Optin Cache', 'mailoptin'),
                        'data' => "<a href='$clear_optin_cache_url' class='button action'>" . __('Clear Cache', 'mailoptin') . '</a>',
                        'description' => '<p class="description">' .
                            sprintf(
                                __('Each time you create and make changes to your %soptin campaigns%s, MailOptin caches the designs so it doesn\'t hurt your website speed and performance. If for some reasons changes you made to your campaigns are not reflected on your website frontend, use this button to clear the cache.', 'mailoptin'),
                                '<a href="' . MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE . '">',
                                '</a>'
                            ) .
                            '</p>',
                    ),
                    'global_cookie' => array(
                        'type' => 'number',
                        'value' => 0,
                        'label' => __('Global Interaction Cookie', 'mailoptin'),
                        'description' => __(
                            'Entering a number of days (e.g. 30) will set a global cookie once any optin is closed by a user or visitor.
                            This global cookie will prevent any other optins from loading on your site for that visitor until the cookie expires. Defaults to 0 (no global interaction cookie).',
                            'mailoptin'
                        ),
                    ),
                    'global_success_cookie' => array(
                        'type' => 'number',
                        'value' => 0,
                        'label' => __('Global Success Cookie', 'mailoptin'),
                        'description' => __(
                            'Entering a number of days (e.g. 30) will set a global cookie once any optin has resulted in a successful conversion. This global cookie will prevent any other optins from loading on your site for that visitor until the cookie expires. Defaults to 0 (no global success cookie).',
                            'mailoptin'
                        ),
                    ),
                )
            ),
            'email_campaign_settings' => apply_filters('mailoptin_settings_email_campaign_settings_page', array(
                    'section_title' => __('Email Campaign Settings', 'mailoptin'),
                    'from_name' => array(
                        'type' => 'text',
                        'label' => __('From Name', 'mailoptin'),
                        'description' => sprintf(__('Enter the sender name to be used as the "From Name".', 'mailoptin')),
                    ),
                    'from_email' => array(
                        'type' => 'text',
                        'label' => __('From Email', 'mailoptin'),
                        'description' => __('Enter the email address to be used as the "From Email"', 'mailoptin'),
                    ),
                    'reply_to' => array(
                        'type' => 'text',
                        'label' => __('Reply To', 'mailoptin'),
                        'description' => __('Enter the email address to be used as the "Reply To"', 'mailoptin'),
                    ),
                    'company_name' => array(
                        'type' => 'text',
                        'label' => __('Company / Organization', 'mailoptin'),
                        'description' => __('Enter the name of your company or organization.', 'mailoptin'),
                    ),
                    'company_address' => array(
                        'type' => 'text',
                        'label' => __('Address', 'mailoptin'),
                    ),
                    'company_address_2' => array(
                        'type' => 'text',
                        'label' => __('Address 2', 'mailoptin'),
                    ),
                    'company_city' => array(
                        'type' => 'text',
                        'label' => __('City', 'mailoptin'),
                    ),
                    'company_state' => array(
                        'type' => 'text',
                        'label' => __('State / Province / Region', 'mailoptin'),
                    ),
                    'company_zip' => array(
                        'type' => 'text',
                        'label' => __('Zip / Postal code', 'mailoptin')
                    ),
                    'company_country' => array(
                        'type' => 'select',
                        'label' => __('Country', 'mailoptin'),
                        'options' => \MailOptin\Core\countries_array()
                    )
                )
            )
        );

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            unset($args['optin_campaign_settings']);
        }

        $instance = Custom_Settings_Page_Api::instance($args, MAILOPTIN_SETTINGS_DB_OPTION_NAME, __('MailOptin - Settings', 'mailoptin'));
        $this->register_core_settings($instance);
        $instance->build();
    }

    /**
     * @return Settings
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