<?php
/**
 * Copyright (C) 2016  Agbonghama Collins <me@w3guy.com>
 */

namespace MailOptin\Core\Admin\SettingsPage;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use MailOptin\Core\Repositories\OptinThemesRepository;
use W3Guy\Custom_Settings_Page_Api;

class AddOptinCampaign extends AbstractSettingsPage
{
    /**
     * Build the settings page structure. I.e tab, sidebar.
     */
    public function settings_admin_page()
    {
        add_action('wp_cspa_before_closing_header', [$this, 'back_to_optin_overview']);
        add_action('wp_cspa_before_post_body_content', array($this, 'optin_theme_sub_header'), 10, 2);
        add_filter('wp_cspa_main_content_area', [$this, 'optin_form_list']);

        $instance = Custom_Settings_Page_Api::instance();
        $instance->page_header(__('Add New Optin', 'mailoptin'));
        $this->register_core_settings($instance, true);
        $instance->build(true);
    }

    /**
     * Sub-menu header for optin theme types.
     */
    public function optin_theme_sub_header()
    {
        if (!empty($_GET['page']) && $_GET['page'] == MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_SLUG) : ?>
            <div class="mailoptin-optin-new-list mailoptin-optin-clear">
                <h4><?php _e('Optin Campaign Title', 'mailoptin'); ?>
                    <input type="text" name="mailoptin-optin-campaign" id="mailoptin-add-optin-campaign-title" placeholder="Enter name for your optin campaign...">
                </h4>
            </div>
            <div id="mailoptin-sub-bar">
                <div class="mailoptin-new-toolbar mailoptin-clear">
                    <h4><?php _e('Select Optin Type', 'mailoptin'); ?>
                        <i class="fa fa-spinner fa-pulse fa-spin"></i>
                    </h4>
                    <span class="sr-only"><?php __('Loading...', 'mailoptin'); ?></span>
                    <ul class="mailoptin-design-options">
                        <li>
                            <a href="#" class="mo-select-optin-type mailoptin-type-active" data-optin-type="lightbox">
                                <?php _e('Lightbox', 'mailoptin'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="mo-select-optin-type" data-optin-type="inpost">
                                <?php _e('In-Post', 'mailoptin'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="mo-select-optin-type" data-optin-type="sidebar">
                                <?php _e('Sidebar/Widget', 'mailoptin'); ?>
                            </a></li>
                        <li>
                            <a href="#" class="mo-select-optin-type" data-optin-type="bar">
                                <?php _e('Notification-Bar', 'mailoptin'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="mo-select-optin-type" data-optin-type="slidein">
                                <?php _e('Slide-In', 'mailoptin'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif;
    }

    /**
     * Display list of optin
     */
    public function optin_form_list()
    {
        // lightbox/modal display should be default.
        $optin_type = 'lightbox';

        echo '<div class="mailoptin-optin-themes mailoptin-optin-clear">';
        foreach (OptinThemesRepository::get_by_type('lightbox') as $optin_theme) {

            $theme_name = $optin_theme['name'];
            $theme_class = $optin_theme['optin_class'];
            $screenshot = $optin_theme['screenshot'];
            ?>
            <div id="mailoptin-optin-theme-list" class="mailoptin-optin-theme mailoptin-optin-theme-<?php echo $theme_class; ?>" data-optin-theme="<?php echo $theme_class; ?>" data-optin-type="<?php echo $optin_type; ?>">
                <div class="mailoptin-optin-theme-screenshot">
                    <img src="<?php echo $screenshot; ?>" alt="<?php echo $theme_name; ?>">
                </div>
                <h3 class="mailoptin-optin-theme-name"><?php echo $theme_name; ?></h3>
                <div class="mailoptin-optin-theme-actions">
                    <a class="button button-primary mailoptin-theme-select"
                       data-optin-theme="<?php echo $theme_class; ?>" data-optin-type="<?php echo $optin_type; ?>"
                       title="<?php _e('Select this theme', 'mailoptin'); ?>">
                        <?php _e('Select Theme', 'mailoptin'); ?>
                    </a>
                </div>
            </div>
            <?php
        }

        if (apply_filters('mailoptin_lite_upgrade_more_optin_themes', true)) {

            if ($optin_type == 'bar') $optin_type = 'notification bar';
            echo '<div style="cursor: pointer;float: left;margin: 0 4% 4% 0;position: relative;width: 30.66666666667%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">';
            echo '<div class="mo-error-box" style="margin:0">';
            printf(
                __('Upgrade to %s for more "%s" optin themes', 'mailoptin'),
                '<a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=get_more_optin_themes" target="_blank">MailOptin Premium</a>',
                $optin_type
            );
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    }

    public function back_to_optin_overview()
    {
        $url = MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE;
        echo "<a class=\"add-new-h2\" href=\"$url\">" . __('Back to Overview', 'mailoptin') . '</a>';
    }


    /**
     * @return AddOptinCampaign
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