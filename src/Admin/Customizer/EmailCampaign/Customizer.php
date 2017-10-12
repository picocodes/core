<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;

use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Submit_Button_Control;
use MailOptin\Core\Admin\Customizer\UpsellCustomizerSection;
use MailOptin\Core\Repositories\EmailCampaignRepository;

class Customizer
{
    /** @var string email campaign database option name */
    public $campaign_settings = MO_EMAIL_CAMPAIGNS_WP_OPTION_NAME;

    /** @var int email campaign ID */
    public $email_campaign_id;

    /** @var int email campaign type */
    public $email_campaign_type;

    /** @var string option name prefix. */
    public $option_prefix;

    /** @var string ID of email campaign settings customizer section. */
    public $campaign_settings_section_id = 'mailoptin_campaign_settings_section_id';

    /** @var string ID of template page customizer section. */
    public $campaign_page_section_id = 'mailoptin_campaign_page';

    /** @var string ID of template header customizer section. */
    public $campaign_header_section_id = 'mailoptin_campaign_header';

    /** @var string ID of template content customizer section. */
    public $campaign_content_section_id = 'mailoptin_campaign_content';

    /** @var string ID of template footer customizer section. */
    public $campaign_footer_section_id = 'mailoptin_campaign_footer';

    /** @var string ID of template footer customizer section. */
    public $campaign_send_email_section_id = 'mailoptin_campaign_send_email';

    /**
     * Customizer constructor.
     */
    public function __construct()
    {
        if (!empty($_REQUEST['mailoptin_email_campaign_id'])) {
            add_action('customize_controls_enqueue_scripts', array($this, 'monkey_patch_customizer_payload'));
            add_action('customize_controls_enqueue_scripts', array($this, 'customizer_css'));
            add_action('customize_controls_enqueue_scripts', array($this, 'customizer_js'));

            $this->email_campaign_id = absint($_REQUEST['mailoptin_email_campaign_id']);

            $this->email_campaign_type = EmailCampaignRepository::get_email_campaign_type(
                $this->email_campaign_id
            );

            add_action('customize_controls_print_scripts', function () {
                echo '<script type="text/javascript">';
                echo "var mailoptin_email_campaign_option_prefix = '{$this->campaign_settings}';";
                echo "var mailoptin_email_campaign_id = $this->email_campaign_id;";
                echo '</script>';
            });

            add_filter('gettext', function ($translations, $text, $domain) {
                if ($domain == 'default' && $text == 'Publish') {
                    $translations = 'Save Changes';
                }
                if ($domain == 'default' && $text == 'Published') {
                    $translations = 'Saved';
                }

                return $translations;
            }, 10, 3);

            add_action('customize_controls_enqueue_scripts', function () {
                wp_enqueue_script('mailoptin-send-test-email', MAILOPTIN_ASSETS_URL . 'js/admin/send-test-email.js');
            });

            add_filter('template_include', array($this, 'include_campaign_customizer_template'));

            add_filter('gettext', array($this, 'rewrite_customizer_panel_description'), 10, 3);

            // remove all sections other than that of email campaign customizer.
            add_action('customize_section_active', array($this, 'remove_sections'), 10, 2);

            // Remove all customizer panels.
            add_action('customize_panel_active', '__return_false');

            add_action('customize_register', array($this, 'register_campaign_customizer'));
        }

    }

    public function monkey_patch_customizer_payload()
    {
        wp_add_inline_script('customize-controls', '(function ( api ) {
                    api.bind( "ready", function () {
                        var _query = api.previewer.query;
                            api.previewer.query = function () {
                                var query = _query.call( this );
                                query.mailoptin_email_campaign_id = "' . $this->email_campaign_id . '";
                                return query;
                            };
                        });
                    })( wp.customize );'
        );
    }

    /**
     * Enqueue JavaScript for email campaign template customizer controls.
     */
    public function customizer_js()
    {
        wp_enqueue_script(
            'mailoptin-fetch-email-customizer-connect-list-controls',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/fetch-customizer-connect-list.js',
            array('customize-controls'),
            MAILOPTIN_VERSION_NUMBER
        );

        wp_enqueue_script(
            'mailoptin-email-customizer-contextual-controls',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/contextual-email-customizer-controls.js',
            array('customize-controls'),
            MAILOPTIN_VERSION_NUMBER
        );
    }

    /**
     * customizer enqueued CSS
     */
    public function customizer_css()
    {
        wp_enqueue_style('mailoptin-customizer', MAILOPTIN_ASSETS_URL . 'css/admin/customizer-stylesheet.css');
    }


    /**
     * By default, customizer uses blog name as panel title
     * hence the rewrite to email campaign name if pass as query sting to customizer url.
     * default to 'Email Campaign'.
     *
     * @param string $blogname
     *
     * @return string
     */
    public function rewrite_customizer_panel_title($blogname)
    {
        $campaign_name = EmailCampaignRepository::get_email_campaign_name($this->email_campaign_id);

        return $campaign_name ?: __('Email Campaign', 'mailoptin');
    }

    /**
     * By default, customizer has the below as its panel description
     *
     * The Customizer allows you to preview changes to your site before publishing them.
     * You can also navigate to different pages on your site to preview them.
     *
     * This class method rewrite this.
     *
     * @param string $translations
     * @param string $text
     * @param string $domain
     *
     * @return string
     */
    public function rewrite_customizer_panel_description($translations, $text, $domain)
    {
        if (strpos($text, 'Customizer allows you to preview changes to your site')) {
            $translations = __(
                'The customizer allows you to configure, make and preview changes to email templates.',
                'mailoptin'
            );
        }

        return $translations;
    }

    /**
     * Remove non-mailoptin customizer sections.
     *
     * @param $active
     * @param $section
     *
     * @return bool
     */
    public function remove_sections($active, $section)
    {
        $sections_ids = apply_filters(
            'mailoptin_campaign_sections_ids',
            array(
                $this->campaign_settings_section_id,
                $this->campaign_page_section_id,
                $this->campaign_header_section_id,
                $this->campaign_content_section_id,
                $this->campaign_footer_section_id,
                $this->campaign_send_email_section_id,
            )
        );

        return in_array($section->id, $sections_ids);
    }


    /**
     * Include template preview template.
     *
     * @param string $template
     *
     * @return string
     */
    public function include_campaign_customizer_template($template)
    {
        if (is_customize_preview() && wp_verify_nonce($_REQUEST['_wpnonce'], 'mailoptin-preview-email-campaign')) {
            $template = MAILOPTIN_SRC . 'Admin/Customizer/EmailCampaign/email-campaign-preview.php';
        } else {
            wp_redirect(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE);
            exit;
        }

        return $template;
    }


    /**
     * Customizer registration.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function register_campaign_customizer($wp_customize)
    {
        $email_campaign_id = absint($_REQUEST['mailoptin_email_campaign_id']);

        $option_prefix = $this->campaign_settings . '[' . $email_campaign_id . ']';

        do_action('mailoptin_register_campaign_customizer', $email_campaign_id);

        $result = EmailCampaignFactory::make($email_campaign_id);

        // $result is false of optin form class do not exist.
        if (!$result) {
            wp_redirect(add_query_arg('email-campaign-error', 'class-not-found', MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
            exit;
        }

        $wp_customize->register_section_type('MailOptin\Core\Admin\Customizer\UpsellCustomizerSection');

        $this->add_sections($wp_customize);
        $this->add_settings($wp_customize, $option_prefix);
        $this->add_controls($wp_customize, $option_prefix);

        // rewrite panel name from blog name to email campaign name.
        add_filter('pre_option_blogname', array($this, 'rewrite_customizer_panel_title'));
    }

    /**
     * Add sections to customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_sections($wp_customize)
    {
        if (!apply_filters('mo_email_customizer_disable_upsell_section', false)) {
            $wp_customize->add_section(
                new UpsellCustomizerSection($wp_customize, 'mailoptin_upsell_section',
                    array(
                        'pro_text' => __('Check out MailOptin Premium!', 'mailoptin'),
                        'pro_url' => 'https://mailoptin.io/pricing/?utm_source=optin_customizer&utm_medium=upgrade&utm_campaign=upsell_customizer_section',
                        'capability' => 'manage_options',
                        'priority' => 0,
                        'type' => 'mo-upsell-section'
                    )
                )
            );
        }

        $wp_customize->add_section($this->campaign_settings_section_id, array(
                'title' => __('Campaign Settings', 'mailoptin'),
                'priority' => 10,
            )
        );

        $wp_customize->add_section($this->campaign_page_section_id, array(
                'title' => __('Template Page', 'mailoptin'),
                'priority' => 20,
            )
        );

        $wp_customize->add_section($this->campaign_header_section_id, array(
                'title' => __('Template Header', 'mailoptin'),
                'priority' => 30,
            )
        );

        $wp_customize->add_section($this->campaign_content_section_id, array(
                'title' => __('Template Content', 'mailoptin'),
                'priority' => 40,
            )
        );

        $wp_customize->add_section($this->campaign_footer_section_id, array(
                'title' => __('Template Footer', 'mailoptin'),
                'priority' => 50,
            )
        );

        $wp_customize->add_section($this->campaign_send_email_section_id, array(
                'title' => __('Send Test Email', 'mailoptin'),
                'priority' => 60,
            )
        );
    }


    /**
     * Add customizer settings.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_settings($wp_customize, $option_prefix)
    {
        $instance = new CustomizerSettings($wp_customize, $option_prefix, $this);
        $instance->campaign_settings();
        $instance->page_settings();
        $instance->header_settings();
        $instance->content_settings();
        $instance->footer_settings();

        $wp_customize->add_setting($this->option_prefix . '[send_test_email]', array(
                'type' => 'option',
                'transport' => 'postMessage',
            )
        );
    }


    /**
     * Add customizer controls.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_controls($wp_customize, $option_prefix)
    {
        $instance = new CustomizerControls($wp_customize, $option_prefix, $this);
        $instance->campaign_settings_controls();
        $instance->page_controls();
        $instance->header_controls();
        $instance->content_controls();
        $instance->footer_controls();

        $admin_email = get_option('admin_email');
        $wp_customize->add_control(new WP_Customize_Submit_Button_Control(
                $wp_customize,
                $this->option_prefix . '[send_test_email]',
                array(
                    'label' => __('Background Color', 'mailoptin'),
                    'description' => __("Save the template and then click the button to send a test email to admin email $admin_email",
                        'mailoptin'
                    ),
                    'section' => $this->campaign_send_email_section_id,
                    'settings' => $this->option_prefix . '[send_test_email]',
                )
            )
        );
    }

    /**
     * @return Customizer
     */
    public static function instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}