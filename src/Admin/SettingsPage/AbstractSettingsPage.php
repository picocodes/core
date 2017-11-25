<?php

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
use MailOptin\Core\Base;
use MailOptin\Core\Core;
use W3Guy\Custom_Settings_Page_Api;

if (!defined('ABSPATH')) {
    exit;
}

abstract class AbstractSettingsPage
{
    protected $option_name;

    public function init_menu()
    {
        add_action('admin_menu', array($this, 'register_core_menu'));

        add_action('wp_cspa_after_settings_tab', [$this, 'toggle_buttons']);
    }

    public function register_core_menu()
    {
        add_menu_page(
            __('MailOptin WordPress Plugin', 'mailoptin'),
            __('MailOptin', 'mailoptin'),
            'manage_options',
            'mailoptin-settings',
            '',
            'dashicons-email-alt'
        );
    }

    public function toggle_buttons($option_name)
    {
        if(!in_array($option_name, [MAILOPTIN_CONNECTIONS_DB_OPTION_NAME, MAILOPTIN_SETTINGS_DB_OPTION_NAME])) return;
        ?>
        <div style="margin: 10px">
            <a href="#" id="mo-metabox-expand" class="button"><?php _e('Expand All', 'mailoptin'); ?></a>
            <a href="#" id="mo-metabox-collapse" class="button"><?php _e('Collapse All', 'mailoptin'); ?></a>
        </div>
        <?php
    }

    /**
     * Register mailoptin core settings.
     *
     * @param Custom_Settings_Page_Api $instance
     * @param bool $remove_sidebar
     */
    public function register_core_settings(Custom_Settings_Page_Api $instance, $remove_sidebar = false)
    {
        if (!$remove_sidebar) {
            $instance->sidebar($this->sidebar_args());
        }

        $instance->tab($this->tab_args());
    }

    public function sidebar_args()
    {
        $sidebar_args = array(
            array(
                'section_title' => __('Help / Support', 'mailoptin'),
                'content' => $this->sidebar_support_docs(),
            )
        );

        $sidebar_args[] = array(
            'section_title' => __('Plugin Need Your Help', 'mailoptin'),
            'content' => self::rate_review_ad(),
        );

        if (!apply_filters('mailoptin_disable_sidebar_ads', false)) {
            $sidebar_args[] = array(
                'section_title' => __('Why Upgrade to PRO', 'mailoptin'),
                'content' => self::why_upgrade_to_pro(),
            );
            $sidebar_args[] = array(
                'section_title' => __('Upgrade to MailOptin PRO', 'mailoptin'),
                'content' => self::mailoptin_pro_ad(),
            );
        }

        if (!is_plugin_active('profilepress/profilepress.php')) {
            $sidebar_args[] = array(
                'section_title' => __('Get ProfilePress', 'mailoptin'),
                'content' => self::profilepress_ad(),
            );
        }

        return $sidebar_args;
    }

    public function tab_args()
    {
        $tabs = apply_filters('mailoptin_settings_page_tabs', array(
            20 => array('url' => MAILOPTIN_SETTINGS_SETTINGS_PAGE, 'label' => __('Settings', 'mailoptin')),
            40 => array('url' => MAILOPTIN_CONNECTIONS_SETTINGS_PAGE, 'label' => __('Connections', 'mailoptin')),
            45 => array('url' => MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE, 'label' => __('Email Automations', 'mailoptin')),
            50 => array('url' => MAILOPTIN_CAMPAIGN_LOG_SETTINGS_PAGE, 'label' => __('Email Log', 'mailoptin')),
            60 => array('url' => MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE, 'label' => __('Optin Campaigns', 'mailoptin')),
        ));

        if (!defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=tab_menu';
            $tabs[999999] = array('url' => $url, 'label' => __('Go Premium', 'mailoptin'), 'style' => 'background-color:#d54e21;color:#fff;border-color:#d54e21');
        }

        ksort($tabs);

        return $tabs;
    }

    public static function why_upgrade_to_pro()
    {
        $url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=why_upgrade_sidebar';

        $content = '<ul>';
        $content .= '<li>' . __('Unlimited number of optin conversion.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('More optin types e.g slide-in & top bar.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('More newsletters type e.g email digest.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('Ton of premium optin and email templates.', 'mailoptin') . '</li> ';
        $content .= '<li>' . __('Optin triggers e.g Exit Intent, Scroll etc.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('Actionable reporting & insights.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('Lead Bank - conversion backup.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('Wow your visitors with DisplayEffects.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('Page level targeting.', 'mailoptin') . '</li>';
        $content .= '<li>' . __('And lots more.', 'mailoptin') . '</li>';
        $content .= '</ul>';
        $content .= '<div>Get <strong>10%</strong> off using this coupon.</div>';
        $content .= '<div style="margin: 5px"><span style="background: #e3e3e3;padding: 2px;">10PERCENTOFF</span></div>';
        $content .= '<div><a target="_blank" href="' . $url . '" class="button-primary" type="button">Go Premium</a></div>';

        return $content;
    }

    public function sidebar_support_docs()
    {
        $content = '<p>';
        $content .= sprintf(
            __('For support, %sreach out to us%s.', 'mailoptin'),
            '<strong><a href="https://mailoptin.io/support/" target="_blank">', '</a></strong>'
        );
        $content .= '</p>';

        $content .= '<p>';
        $content .= sprintf(
            __('Visit the %s for guidance.', 'mailoptin'),
            '<strong><a href="https://mailoptin.io/docs/" target="_blank">' . __('Documentation', 'mailoptin') . '</a></strong>'
        );

        $content .= '</p>';

        return $content;
    }

    public function rate_review_ad()
    {
        ob_start();
        $review_url = 'https://wordpress.org/support/view/plugin-reviews/mailoptin';
        $compatibility_url = 'https://wordpress.org/plugins/mailoptin/#compatibility';
        $twitter_url = 'https://twitter.com/home?status=I%20love%20this%20WordPress%20plugin!%20https://wordpress.org/plugins/mailoptin/';

        ?>
        <div style="text-align: center; margin: auto">
            <ul>
                <li>
                    <?php printf(
                        wp_kses(__('Is this plugin useful for you? Leave a positive review on the plugin\'s <a href="%s" target="_blank">WordPress listing</a>', 'mailoptin'),
                            array(
                                'a' => array(
                                    'href' => array(),
                                    'target' => array('_blank'),
                                ),
                            )
                        ),
                        esc_url($review_url));
                    ?>
                </li>
                <li><?php printf(wp_kses(__('<a href="%s" target="_blank">Share your thoughts on Twitter</a>',
                        'mailoptin'),
                        array(
                            'a' => array(
                                'href' => array(),
                                'target' => array('_blank'),
                            ),
                        )),
                        esc_url($twitter_url)); ?></li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function mailoptin_pro_ad()
    {
        $content = '<a href="https://mailoptin.io/pricing/?discount=10PERCENTOFF&utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=sidebar_banner" target="_blank">';
        $content .= '<img width="250" src="' . MAILOPTIN_ASSETS_URL . 'images/mo-pro-upgrade.jpg' . '">';
        $content .= '</a>';

        return $content;
    }

    public static function profilepress_ad()
    {
        $content = '<a href="https://profilepress.net/pricing/?discount=20PERCENTOFF&ref=mailoptin_settings_page" target="_blank">';
        $content .= '<img width="250" src="' . MAILOPTIN_ASSETS_URL . 'images/profilepress-ad.jpg' . '">';
        $content .= '</a>';

        return $content;
    }
}