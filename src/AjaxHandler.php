<?php

namespace MailOptin\Core;

use MailOptin\Core\Admin\Customizer\CustomControls\ControlsHelpers;
use MailOptin\Core\Admin\Customizer\EmailCampaign\SolitaryDummyContent;
use MailOptin\Core\Admin\SettingsPage\Email_Campaign_List;
use MailOptin\Core\Admin\SettingsPage\OptinCampaign_List;
use MailOptin\Core\Admin\SettingsPage\SplitTestOptinCampaign;
use MailOptin\Core\EmailCampaign\TemplatifyNewPostPublish;
use MailOptin\Core\Connections\AbstractConnect;
use MailOptin\Core\Connections\ConnectionFactory;
use MailOptin\Core\OptinForms\ConversionDataBuilder;
use MailOptin\Core\PluginSettings\Settings;
use MailOptin\Core\Repositories\ConnectionsRepository;
use MailOptin\Core\Repositories\EmailCampaignRepository;
use MailOptin\Core\Repositories\OptinCampaignMeta;
use MailOptin\Core\Repositories\OptinCampaignsRepository;
use MailOptin\Core\Repositories\OptinCampaignStat;
use MailOptin\Core\Repositories\OptinConversionsRepository;
use MailOptin\Core\Repositories\OptinThemesRepository;
use MailOptin\Core\Triggers\NewPublishPost;

class AjaxHandler
{
    public function __construct()
    {
        add_action('init', array($this, 'define_ajax'), 0);
        add_action('template_redirect', array($this, 'do_mailoptin_ajax'), 0);

//        add_action('wp_ajax_mailoptin_send_test_email', array($this, 'send_test_email'));
//        add_action('wp_ajax_mailoptin_create_optin_campaign', [$this, 'create_optin_campaign']);
//        add_action('wp_ajax_mailoptin_create_email_campaign', [$this, 'create_email_campaign']);
//        add_action('wp_ajax_mailoptin_customizer_fetch_email_list', [$this, 'customizer_fetch_email_list']);
//        add_action('wp_ajax_mailoptin_optin_toggle_active', [$this, 'optin_toggle_active']);
//        add_action('wp_ajax_mailoptin_automation_toggle_active', [$this, 'automation_toggle_active']);
//        add_action('wp_ajax_mailoptin_toggle_optin_activated', [$this, 'toggle_optin_activated']);
//        add_action('wp_ajax_mailoptin_toggle_automation_activated', [$this, 'toggle_automation_activated']);
//        add_action('wp_ajax_mailoptin_optin_type_selection', [$this, 'optin_type_selection']);
//
//        add_action('wp_ajax_mailoptin_create_optin_split_test', [$this, 'create_optin_split_test']);
//        add_action('wp_ajax_mailoptin_pause_optin_split_test', [$this, 'pause_optin_split_test']);
//        add_action('wp_ajax_mailoptin_end_optin_split_modal', [$this, 'end_optin_split_modal']);
//        add_action('wp_ajax_mailoptin_split_test_select_winner', [$this, 'split_test_select_winner']);
//
//        add_action('wp_ajax_mailoptin_page_targeting_search', [$this, 'page_targeting_search']);

        // MailOptin_event => nopriv
        $ajax_events = array(
            'track_optin_impression' => true,
            'subscribe_to_email_list' => true,
            'send_test_email' => false,
            'create_optin_campaign' => false,
            'create_email_campaign' => false,
            'customizer_fetch_email_list' => false,
            'optin_toggle_active' => false,
            'automation_toggle_active' => false,
            'toggle_optin_activated' => false,
            'toggle_automation_activated' => false,
            'optin_type_selection' => false,
            'create_optin_split_test' => false,
            'pause_optin_split_test' => false,
            'end_optin_split_modal' => false,
            'split_test_select_winner' => false,
            'page_targeting_search' => false,
        );

        foreach ($ajax_events as $ajax_event => $nopriv) {
            add_action('wp_ajax_mailoptin_' . $ajax_event, array($this, $ajax_event));

            if ($nopriv) {
                // backward compat.
                add_action('wp_ajax_nopriv_mailoptin_' . $ajax_event, array($this, $ajax_event));

                // MailOptin AJAX can be used for frontend ajax requests.
                add_action('mailoptin_ajax_' . $ajax_event, array($this, $ajax_event));
            }
        }
    }

    public static function define_ajax()
    {
        if (!empty($_GET['mailoptin-ajax'])) {
            if (!defined('DOING_AJAX')) {
                define('DOING_AJAX', true);
            }

            if (!WP_DEBUG || (WP_DEBUG && !WP_DEBUG_DISPLAY)) {
                @ini_set('display_errors', 0); // Turn off display_errors during AJAX events to prevent malformed JSON
            }
            $GLOBALS['wpdb']->hide_errors();
        }
    }

    /**
     * Get MailOptin Ajax Endpoint.
     *
     * @param  string $request Optional
     *
     * @return string
     */
    public static function get_endpoint()
    {
        return esc_url_raw(add_query_arg('mailoptin-ajax', '%%endpoint%%'));
    }

    public function do_mailoptin_ajax()
    {
        global $wp_query;

        if (!empty($_GET['mailoptin-ajax'])) {
            $wp_query->set('mailoptin-ajax', sanitize_text_field($_GET['mailoptin-ajax']));
        }

        if ($action = $wp_query->get('mailoptin-ajax')) {
            $this->mo_ajax_headers();
            do_action('mailoptin_ajax_' . sanitize_text_field($action));
            wp_die();
        }
    }

    /**
     * Send headers for MailOptin Ajax Requests.
     *
     * @since 2.5.0
     */
    private static function mo_ajax_headers()
    {
        send_origin_headers();
        @header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        @header('X-Robots-Tag: noindex');
        send_nosniff_header();
        self::do_not_cache();
        status_header(200);
    }

    public static function do_not_cache()
    {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        if (!defined('DONOTCACHEDB')) {
            define('DONOTCACHEDB', true);
        }

        if (!defined('DONOTMINIFY')) {
            define('DONOTMINIFY', true);
        }

        if (!defined('DONOTCDN')) {
            define('DONOTCDN', true);
        }

        if (!defined('DONOTCACHCEOBJECT')) {
            define('DONOTCACHCEOBJECT', true);
        }

        // Set the headers to prevent caching for the different browsers.
        nocache_headers();
    }

    /**
     * Send template customizer test email.
     */
    public function send_test_email()
    {
        // if not in admin dashboard, bail
        if (!is_admin()) {
            return;
        }

        check_ajax_referer('mailoptin-send-test-email-nonce', 'security');

        if (!current_user_can('administrator')) {
            return;
        }

        $admin_email = get_option('admin_email');
        $email_campaign_id = absint($_REQUEST['email_campaign_id']);
        $campaign_subject = EmailCampaignRepository::get_customizer_value($email_campaign_id, 'email_campaign_subject');
        $campaign_type = EmailCampaignRepository::get_email_campaign_type($email_campaign_id);

        $plugin_settings = new Settings();
        $from_name = $plugin_settings->from_name();
        $from_email = $plugin_settings->from_email();
        $headers = ["Content-Type: text/html", "From: $from_name <$from_email>"];

        /** call appropriate method to get template preview. Eg @see self::new_publish_post_preview() */
        $data = $this->{"{$campaign_type}_preview"}($email_campaign_id, $campaign_subject);

        $content_html = $data[0];
        $formatted_email_subject = $data[1];

        $response = wp_mail($admin_email, $formatted_email_subject, $content_html, $headers);

        wp_send_json(array('success' => (bool)$response));
    }

    /**
     * Handles generating preview of "new publish post" email campaign for test email sending
     *
     * @param int $email_campaign_id
     * @param string $email_campaign_subject
     *
     * @return array index0 is content_html index1 is email campaign subject.
     */
    public function new_publish_post_preview($email_campaign_id, $email_campaign_subject)
    {
        $mock_post = new \stdClass();
        $mock_post->ID = 0;
        $mock_post->post_title = SolitaryDummyContent::title();
        $mock_post->post_content = SolitaryDummyContent::content();
        $mock_post->post_url = home_url();

        return [
            (new TemplatifyNewPostPublish($mock_post, $email_campaign_id))->forge(),
            NewPublishPost::format_campaign_subject($email_campaign_subject, $mock_post)
        ];
    }

    /**
     * Filter optin designs by type.
     */
    public function optin_type_selection()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (empty($_REQUEST['optin-type'])) {
            wp_send_json_error(__('Unexpected error. Please try again.', 'mailoptin'));
        }

        $optin_type = sanitize_text_field($_REQUEST['optin-type']);

        echo '<div class="mailoptin-optin-themes mailoptin-optin-clear">';
        // lightbox/modal display should be default.
        $optin_designs = OptinThemesRepository::get_by_type($optin_type);

        if (empty($optin_designs)) {
            if ($optin_type == 'bar') $optin_type = 'notification bar';
            echo '<div class="mo-error-box">';
            printf(
                __('Upgrade to %s for "%s optin" support', 'mailoptin'),
                '<a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=optin_themes_not_found" target="_blank">MailOptin Premium</a>',
                $optin_type
            );
            echo '</div>';
        } else {
            foreach ($optin_designs as $optin_theme) {
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
                        <a class="button button-primary mailoptin-theme-select" data-optin-theme="<?php echo $theme_class; ?>" data-optin-type="<?php echo $optin_type; ?>" title="<?php _e('Select this theme', 'mailoptin'); ?>">
                            <?php _e('Select Theme', 'mailoptin'); ?>
                        </a>
                    </div>
                </div>
                <?php
            }

            if (apply_filters('mailoptin_lite_upgrade_more_optin_themes', true)) {
                if ($optin_type == 'bar') $optin_type = 'notification bar';
                echo '<div style="cursor: pointer;float: left;margin: 0 4% 4% 0;position: relative;width: 30.66666666667%;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;">';
                echo '<div class="mo-error-box" style="padding: 80px 10px;margin:0">';
                printf(
                    __('Upgrade to %s for more "%s" optin themes', 'mailoptin'),
                    '<a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=get_more_optin_themes" target="_blank">MailOptin Premium</a>',
                    $optin_type
                );
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        exit;
    }

    /**
     * Create optin slit test
     */
    public function create_optin_split_test()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (!isset($_REQUEST['variant_name'], $_REQUEST['split_note'], $_REQUEST['parent_optin_id'])) {
            wp_send_json_error();
        }

        $variant_name = sanitize_text_field($_REQUEST['variant_name']);
        $split_note = sanitize_text_field($_REQUEST['split_note']);
        $parent_optin_id = absint($_REQUEST['parent_optin_id']);

        if (OptinCampaignsRepository::campaign_name_exist($variant_name)) {
            wp_send_json_error(__('Optin campaign with similar variant name exist already.', 'mailoptin'));
        }

        $optin_campaign_id = (new SplitTestOptinCampaign($parent_optin_id, $variant_name, $split_note))->forge();

        if (!$optin_campaign_id) wp_send_json_error();

        wp_send_json_success(
            ['redirect' => OptinCampaign_List::_optin_campaign_customize_url($optin_campaign_id)]
        );
    }

    /**
     * Pause and start optin slit test
     */
    public function pause_optin_split_test()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (!isset($_POST['parent_optin_id'], $_POST['split_test_action'])) {
            wp_send_json_error();
        }

        $parent_optin_id = absint($_POST['parent_optin_id']);
        $split_test_action = sanitize_text_field($_POST['split_test_action']);

        $variant_ids = OptinCampaignsRepository::get_split_test_variant_ids($parent_optin_id);

        foreach ($variant_ids as $variant_id) {

            if ('pause' === $split_test_action) {
                OptinCampaignsRepository::deactivate_campaign($variant_id);
            } else {
                OptinCampaignsRepository::activate_campaign($variant_id);
            }
        }

        wp_send_json_success();
    }

    /**
     * Select winner of split test
     */
    public function split_test_select_winner()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (!isset($_POST['parent_optin_id'], $_POST['winner_optin_id'])) {
            wp_send_json_error();
        }

        $parent_optin_id = absint($_POST['parent_optin_id']);
        $winner_optin_id = absint($_POST['winner_optin_id']);

        $variant_ids = OptinCampaignsRepository::get_split_test_variant_ids($parent_optin_id);

        // merge parent ID with variant IDs
        $variant_ids[] = $parent_optin_id;

        foreach ($variant_ids as $variant_id) {
            $variant_id = absint($variant_id);
            // skip deleting the winning optin.
            if ($variant_id !== $winner_optin_id) {
                OptinCampaignsRepository::delete_optin_campaign($variant_id);
            }
        }

        // ensure the winning optin do not have split test meta so it is no longer consider a variant.
        // useful if winner id was previously a variant.
        OptinCampaignMeta::delete_campaign_meta($winner_optin_id, 'split_test_parent');

        wp_send_json_success(['redirect' => MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE]);
    }

    /**
     * End ans select winning optin split test
     */
    public function end_optin_split_modal()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (!isset($_POST['parent_optin_id'])) {
            wp_send_json_error();
        }

        $parent_optin_id = absint($_POST['parent_optin_id']);

        $variant_ids = OptinCampaignsRepository::get_split_test_variant_ids($parent_optin_id);

        // merge parent optin with variants
        array_unshift($variant_ids, $parent_optin_id);

        ob_start();
        ?>
        <div class="mo-end-test-modal">
        <div class="mo-end-test-conversion-rate">
            <div class="mo-end-test-centered">
                <h2><?php _e('Select a Winner', 'mailoptin'); ?></h2>
                <div class="mo-end-test-first-section">
                    <div class="mo-end-test-content-optin-name mo-end-test-th-header">
                        <?php _e('Optin Name', 'mailoptin'); ?>
                    </div>
                    <div class="mo-end-test-content-conversion mo-end-test-th-header">
                        <?php _e('Conversion Rate', 'mailoptin'); ?>
                    </div>
                </div>
                <?php foreach ($variant_ids as $variant_id) : ?>
                    <?php $variant_name = OptinCampaignsRepository::get_optin_campaign_name($variant_id); ?>
                    <?php $variant_conversion_rate = (new OptinCampaignStat($variant_id))->get_conversion_rate(); ?>
                    <div class="mo-end-test-first-section mo-end-test-tbody mo-end-test-clearfix" data-parent-id="<?php echo $parent_optin_id; ?>" data-optin-id="<?php echo $variant_id; ?>">
                        <div class="mo-end-test-content-optin-name"><?php echo $variant_name; ?></div>
                        <div class="mo-end-test-content-conversion">
                            <span class="mo-end-test-converted-rate"><?php echo $variant_conversion_rate; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="mo-end-test-warning-note">
                    <?php _e('Warning: selecting a winner will delete the other A/B test variants.', 'mailoptin'); ?>
                </div>

                <div class="mo-end-test-cancel-button mo-end-test-centered">
                    <a href="javascript:window.jQuery.fancybox.close();" class="mo-end-test-btn-converted">
                        <?php _e('Cancel', 'mailoptin'); ?>
                    </a>
                </div>
                <div class="mo-end-test-preloader">
                    <img class="mo-spinner mo-end-test-spinner" id="mo-split-end-test-spinner" src="<?php echo admin_url('images/spinner.gif'); ?>">
                </div>
                <div id="mo-select-winner-error" class="mailoptin-error" style="display:none;text-align:center;font-weight:normal;">
                    <?php _e('An error occurred. Please try again.', 'mailoptin'); ?>
                </div>
            </div>
        </div>

        <?php
        wp_send_json_success(ob_get_clean());
    }

    /**
     * Create new optin campaign.
     */
    public function create_optin_campaign()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (empty($_REQUEST['title']) || empty($_REQUEST['theme']) || empty($_REQUEST['type'])) {
            wp_send_json_error(__('Unexpected error. Please try again.', 'mailoptin'));
        }

        $title = sanitize_text_field($_REQUEST['title']);
        $theme = sanitize_text_field($_REQUEST['theme']);
        $type = sanitize_text_field($_REQUEST['type']);

        if (OptinCampaignsRepository::campaign_name_exist($title)) {
            wp_send_json_error(__('Optin campaign with similar name exist already.', 'mailoptin'));
        }

        if (apply_filters('mailoptin_add_optin_email_campaign_limit', true) && OptinCampaignsRepository::campaign_count() >= MO_LITE_OPTIN_CAMPAIGN_LIMIT) {
            $upgrade_url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=add_optin_campaign_limit';
            wp_send_json_error(
                sprintf(
                    __('Upgrade to %sMailOptin premium%s to create more than three optin campaigns', 'mailoptin'),
                    '<a href="' . $upgrade_url . '" target="_blank">', '</a>'
                )
            );
        }

        do_action('mailoptin_before_add_optin_email_campaign');

        $response = OptinCampaignsRepository::add_optin_campaign(self::generateUniqueId(), $title, $theme, $type);

        if (is_int($response)) {
            wp_send_json_success(
                array('redirect' => OptinCampaign_List::_optin_campaign_customize_url($response))
            );
        } else {
            wp_send_json_error();
        }

        wp_die();
    }

    /**
     * Create new optin campaign.
     */
    public function create_email_campaign()
    {
        if (!current_user_can('administrator')) {
            return;
        }

        check_ajax_referer('mailoptin-admin-nonce', 'nonce');

        if (empty($_REQUEST['title']) || empty($_REQUEST['template']) || empty($_REQUEST['type'])) {
            wp_send_json_error(__('Unexpected error. Please try again.', 'mailoptin'));
        }

        $title = sanitize_text_field($_REQUEST['title']);
        $template = sanitize_text_field($_REQUEST['template']);
        $type = sanitize_text_field($_REQUEST['type']);

        if (apply_filters('mailoptin_add_new_email_campaign_limit', true) && EmailCampaignRepository::campaign_count() > 1) {
            $upgrade_url = 'https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=add_email_campaign_limit';
            wp_send_json_error(
                sprintf(__('Upgrade to %s to create multiple email campaigns', 'mailoptin'),
                    '<a href="' . $upgrade_url . '" target="_blank">MailOptin premium</a>'
                )
            );
        }

        if (EmailCampaignRepository::campaign_name_exist($title)) {
            wp_send_json_error(__('Email campaign with similar name exist already.', 'mailoptin'));
        }

        $email_campaign_id = EmailCampaignRepository::add_email_campaign($title, $type, $template);

        if (is_int($email_campaign_id)) {
            wp_send_json_success(
                array('redirect' => Email_Campaign_List::_campaign_customize_url($email_campaign_id))
            );
        } else {
            wp_send_json_error();
        }

        wp_die();
    }

    /**
     * Generate unique ID for each optin form.
     *
     * @param int $length
     *
     * @return string
     */
    public static function generateUniqueId($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * fetch connect/email provider email list.
     */
    public function customizer_fetch_email_list()
    {
        check_ajax_referer('customizer-fetch-email-list', 'security');

        current_user_can('administrator') || exit;

        $connect = sanitize_text_field($_REQUEST['connect_service']);

        $email_list = ConnectionsRepository::connection_email_list($connect);

        wp_send_json_success($email_list);

        wp_die();
    }

    public function optin_toggle_active()
    {
        check_ajax_referer('customizer-fetch-email-list', 'security');

        current_user_can('administrator') || exit;

        $optin_campaign_id = absint($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'true') {
            OptinCampaignsRepository::activate_campaign($optin_campaign_id);
        } else {
            OptinCampaignsRepository::deactivate_campaign($optin_campaign_id);
        }

        wp_die();
    }

    public function automation_toggle_active()
    {
        check_ajax_referer('customizer-fetch-email-list', 'security');

        current_user_can('administrator') || exit;

        $email_campaign_id = absint($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'true') {
            EmailCampaignRepository::activate_email_campaign($email_campaign_id);
        } else {
            EmailCampaignRepository::deactivate_email_campaign($email_campaign_id);
        }

        wp_die();
    }

    /**
     * Toggle activation of optin camapigns in WP List
     */
    public function toggle_optin_activated()
    {
        current_user_can('administrator') || exit;

        $optin_campaign_id = absint($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'true') {
            OptinCampaignsRepository::activate_campaign($optin_campaign_id);
        } else {
            OptinCampaignsRepository::deactivate_campaign($optin_campaign_id);
        }

        wp_die();
    }

    public function toggle_automation_activated()
    {
        current_user_can('administrator') || exit;

        $email_campaign_id = absint($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        if ($status == 'true') {
            EmailCampaignRepository::activate_email_campaign($email_campaign_id);
        } else {
            EmailCampaignRepository::deactivate_email_campaign($email_campaign_id);
        }

        wp_die();
    }

    /**
     * Add subscriber to a connected service mailing list.
     */
    public function subscribe_to_email_list()
    {
        $builder = new ConversionDataBuilder();
        $builder->payload = $payload = apply_filters('mailoptin_optin_subscription_request_body', sanitize_data($_REQUEST['optin_data']));
        $builder->optin_uuid = $optin_uuid = $payload['optin_uuid'];
        $builder->email = $payload['email'];
        $builder->name = $payload['name'];
        $builder->user_agent = $payload['user_agent'];
        $builder->conversion_page = $payload['conversion_page'];
        $builder->referrer = $payload['referrer'];

        $response = self::do_optin_conversion($builder);

        wp_send_json($response);
    }

    public static function send_optin_error_email($optin_campaign_id, $error_message)
    {
        $email = get_option('admin_email');

        sprintf(
            __("%s\n\n -- \n\nThis e-mail was sent by %s plugin on %s (%s)", 'mailoptin'), '[LEAD_DATA]', 'MailOptin', get_bloginfo('name'), site_url()
        );

        $optin_campaign_name = OptinCampaignsRepository::get_optin_campaign_name($optin_campaign_id);

        $subject = apply_filters('mo_optin_form_email_error_email_subject', sprintf(__('Warning! %s Optin Form Is Not Working', 'mailoptin'), $optin_campaign_name), $optin_campaign_id, $error_message);

        $message = apply_filters(
            'mo_optin_form_email_error_email_message',
            sprintf(
                __('The optin campaign "%s" is failing to convert leads due to the following error "%s". %6$s -- %6$sThis e-mail was sent by %s plugin on %s (%s)', 'mailoptin'),
                $optin_campaign_name,
                $error_message,
                'MailOptin',
                get_bloginfo('name'),
                site_url(),
                "\r\n\n"
            )
        );

        @wp_mail($email, $subject, $message);
    }

    /**
     * Accept wide range of optin conversion data and save the lead.
     *
     * @param ConversionDataBuilder $conversion_data
     * @return array
     */
    public static function do_optin_conversion(ConversionDataBuilder $conversion_data)
    {
        $optin_campaign_id = absint(OptinCampaignsRepository::get_optin_campaign_id_by_uuid($conversion_data->optin_uuid));

        if (!is_email($conversion_data->email)) {
            return AbstractConnect::ajax_failure(
                apply_filters('mo_subscription_invalid_email_error', __('Email address is invalid. Try again.', 'mailoptin'))
            );
        }

        if ($conversion_data->is_timestamp_check_active === true) {

            $timestamp = $conversion_data->payload['_mo_timestamp'];

            // make sure `_mo_timestamp` is at least 1.5 seconds ago
            // culled from https://twitter.com/w3guy/status/869576726296358915
            if (empty($timestamp) || time() < (intval($timestamp) + 1.5)) {
                return AbstractConnect::ajax_failure(
                    apply_filters('mo_subscription_invalid_spam_error', __('Your submission has been flagged as potential spam.', 'mailoptin'))
                );
            }
        }

        // flag to stop subscription if number of optin subscriber limit is exceeded or reached.
        if (!defined('MAILOPTIN_DETACH_LIBSODIUM') && OptinConversionsRepository::month_conversion_count() >= MO_LITE_OPTIN_CONVERSION_LIMIT) {
            self::send_optin_error_email($optin_campaign_id, 'mo_subscription_limit_exceeded_error');
            return AbstractConnect::ajax_failure(
                apply_filters('mo_subscription_limit_exceeded_error', __('You cannot be added to our email list at this time. Please try again later.', 'mailoptin'))
            );
        }

        $optin_campaign_type = isset($conversion_data->optin_campaign_type) ? $conversion_data->optin_campaign_type : OptinCampaignsRepository::get_optin_campaign_type($optin_campaign_id);

        $lead_bank_only = OptinCampaignsRepository::get_customizer_value($optin_campaign_id, 'lead_bank_only');

        $connection_service = isset($conversion_data->connection_service) ? $conversion_data->connection_service : OptinCampaignsRepository::get_customizer_value($optin_campaign_id, 'connection_service');

        $connection_email_list = isset($conversion_data->connection_email_list) ? $conversion_data->connection_email_list : OptinCampaignsRepository::get_customizer_value($optin_campaign_id, 'connection_email_list');

        $lead_data = [
            'optin_campaign_id' => $optin_campaign_id,
            'optin_campaign_type' => $optin_campaign_type,
            'name' => $conversion_data->name,
            'email' => $conversion_data->email,
            'user_agent' => $conversion_data->user_agent,
            'conversion_page' => $conversion_data->conversion_page,
            'referrer' => $conversion_data->referrer,
        ];

        if ($conversion_data->is_leadbank_active === true) {
            // capture optin lead / conversion
            OptinConversionsRepository::add($lead_data);
        }

        // kick-in if only lead bank should be used
        if ($lead_bank_only === true) {
            // record optin campaign conversion.
            (new OptinCampaignStat($optin_campaign_id))->save('conversion');

            do_action('mailoptin_track_conversions', $lead_data, $optin_campaign_id);

            return AbstractConnect::ajax_success();
        }

        // !$lead_bank_only ensures error is not thrown if lead_bank_only is checked or true.
        if (empty($connection_service) || empty($connection_email_list)) {
            self::send_optin_error_email($optin_campaign_id, 'No email provider or list has been set for this optin.');
            $response = AbstractConnect::ajax_failure(__('No email provider or list has been set for this optin. Please try again', 'mailoptin'));
        } else {
            $extras = $conversion_data->payload;
            $extras['optin_campaign_id'] = $optin_campaign_id;
            $extras['connection_service'] = $connection_service;
            $extras['connection_email_list'] = $connection_email_list;

            do_action_ref_array('mailoptin_before_optin_subscription', $extras);

            $instance = ConnectionFactory::make($connection_service);

            $response = $instance->subscribe($conversion_data->email, $conversion_data->name, $connection_email_list, $extras);

            do_action_ref_array('mailoptin_after_optin_subscription', $extras);

            if ($response['success'] === true) {

                // record optin campaign conversion.
                (new OptinCampaignStat($optin_campaign_id))->save('conversion');

                do_action('mailoptin_track_conversions', $lead_data, $optin_campaign_id);
            }
        }
        return $response;
    }

    /**
     * Track optin impression.
     */
    public function track_optin_impression()
    {
        $payload = sanitize_data($_REQUEST['stat_data']);
        $optin_uuid = $payload['optin_uuid'];
        $optin_campaign_id = OptinCampaignsRepository::get_optin_campaign_id_by_uuid($optin_uuid);
        $stat_type = 'impression';
        (new OptinCampaignStat($optin_campaign_id))->save($stat_type);

        do_action('mailoptin_track_impressions', $payload, $optin_campaign_id, $optin_uuid);
    }

    /**
     * Handle search done on page target chosen field.
     */
    public function page_targeting_search()
    {
        $q = sanitize_text_field($_REQUEST['q']);
        $search_type = sanitize_text_field($_REQUEST['search_type']);
        $response = array();

        switch ($search_type) {
            case 'posts_never_load' :
                $response = ControlsHelpers::get_post_type_posts('post', 1000, 'publish', $q);
                break;
            case 'pages_never_load' :
                $response = ControlsHelpers::get_post_type_posts('page', 1000, 'publish', $q);
                break;
            case 'cpt_never_load' :
                $response = ControlsHelpers::get_all_post_types_posts(array('post', 'page'), 1000, $q);
                break;
            case 'exclusive_post_types_posts_load' :
                $response = ControlsHelpers::get_all_post_types_posts([], 1000, $q);
                break;
        }

        wp_send_json($response);
    }

    /**
     * @return AjaxHandler
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