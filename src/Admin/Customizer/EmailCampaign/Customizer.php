<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;

use MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Submit_Button_Control;
use MailOptin\Core\Admin\Customizer\CustomizerTrait;
use MailOptin\Core\Admin\Customizer\UpsellCustomizerSection;
use MailOptin\Core\Repositories\EmailCampaignRepository;

class Customizer
{
    use CustomizerTrait;

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
        if ( ! empty($_REQUEST['mailoptin_email_campaign_id'])) {

            $this->clean_up_customizer();
            $this->modify_customizer_publish_button();

            add_action('customize_controls_enqueue_scripts', array($this, 'monkey_patch_customizer_payload'));
            add_action('customize_controls_enqueue_scripts', array($this, 'customizer_css'));
            add_action('customize_controls_enqueue_scripts', array($this, 'customizer_js'));

            add_action('customize_controls_print_footer_scripts', [$this, 'add_activate_switch']);

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

            add_action('customize_controls_enqueue_scripts', function () {
                wp_enqueue_script('mailoptin-send-test-email', MAILOPTIN_ASSETS_URL . 'js/admin/send-test-email.js');
            });

            add_filter('template_include', array($this, 'include_campaign_customizer_template'), 999999999);

            add_filter('gettext', array($this, 'rewrite_customizer_panel_description'), 10, 3);

            // remove all sections other than that of email campaign customizer.
            add_action('customize_section_active', array($this, 'remove_sections'), 10, 2);

            // Remove all customizer panels.
            add_action('customize_panel_active', '__return_false');

            add_action('customize_register', array($this, 'register_campaign_customizer'));

            // save edited email campaign title
            add_action('customize_save', array($this, 'save_email_campaign_title'));

            add_action('customize_controls_init', [$this, 'set_customizer_urls']);
        }
    }

    public function set_customizer_urls()
    {
        global $wp_customize;

        $wp_customize->set_preview_url(
            add_query_arg(
                '_wpnonce',
                wp_create_nonce('mailoptin-preview-email-campaign'),
                sprintf(home_url('/?mailoptin_email_campaign_id=%d'), absint($_GET['mailoptin_email_campaign_id']))
            )
        );

        $wp_customize->set_return_url(MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE);


    }

    /**
     * Add activation switch to optin customizer
     */
    public function add_activate_switch()
    {
        $input_value = EmailCampaignRepository::is_campaign_active($this->email_campaign_id) ? 'yes' : 'no';
        $checked     = ($input_value == 'yes') ? 'checked="checked"' : null;
        $tooltip     = __('Toggle to activate and deactivate email automation.', 'mailoptin');

        $switch = sprintf(
            '<input id="mo-automation-activate-switch" type="checkbox" class="tgl tgl-light" value="%s" %s />',
            $input_value,
            $checked
        );

        $switch .= '<label id="mo-automation-active-switch" for="mo-automation-activate-switch" class="tgl-btn"></label>';
        $switch .= '<span title="' . $tooltip . '" class="mo-tooltipster dashicons dashicons-editor-help" style="margin: 9px 5px;font-size: 18px;cursor: pointer;"></span>';
        ?>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('#customize-header-actions').prepend(jQuery('<?php echo $switch; ?>'));
            });
        </script>
        <?php
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

        do_action('mailoptin_email_campaign_enqueue_customizer_js');
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

        return $campaign_name ?: __('Email Automation', 'mailoptin');
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
                'The customizer allows you to design, preview and set up to email campaigns.',
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
        if ( ! $result) {
            wp_redirect(add_query_arg('email-campaign-error', 'class-not-found', MAILOPTIN_EMAIL_CAMPAIGNS_SETTINGS_PAGE));
            exit;
        }

        $this->register_custom_section($wp_customize);
        $this->register_control_type($wp_customize);

        $this->add_sections($wp_customize);
        $this->add_settings($wp_customize, $option_prefix);
        $this->add_controls($wp_customize, $option_prefix);

        // rewrite panel name from blog name to email campaign name.
        add_filter('pre_option_blogname', array($this, 'rewrite_customizer_panel_title'));
    }

    /**
     * @param \WP_Customize_Manager $wp_customize_manager
     */
    public function save_email_campaign_title($wp_customize_manager)
    {
        $email_campaign_id = absint($_POST['mailoptin_email_campaign_id']);
        $option_name       = "mo_email_campaigns[$email_campaign_id][email_campaign_title]";
        $posted_values     = $wp_customize_manager->unsanitized_post_values();

        if (array_key_exists($option_name, $posted_values)) {
            EmailCampaignRepository::update_campaign_name(
                sanitize_text_field($posted_values[$option_name]),
                $email_campaign_id
            );
        }
    }

    /**
     * Add sections to customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_sections($wp_customize)
    {
        if ( ! apply_filters('mo_email_customizer_disable_upsell_section', false)) {
            $wp_customize->add_section(
                new UpsellCustomizerSection($wp_customize, 'mailoptin_upsell_section',
                    array(
                        'pro_text'   => __('Check out MailOptin Premium!', 'mailoptin'),
                        'pro_url'    => 'https://mailoptin.io/pricing/?utm_source=optin_customizer&utm_medium=upgrade&utm_campaign=upsell_customizer_section',
                        'capability' => 'manage_options',
                        'priority'   => 0,
                        'type'       => 'mo-upsell-section'
                    )
                )
            );
        }

        $wp_customize->add_section($this->campaign_settings_section_id, array(
                'title'    => __('Settings', 'mailoptin'),
                'priority' => 10,
            )
        );

        $wp_customize->add_section($this->campaign_page_section_id, array(
                'title'    => __('Body', 'mailoptin'),
                'priority' => 20,
            )
        );

        $wp_customize->add_section($this->campaign_header_section_id, array(
                'title'    => __('Header', 'mailoptin'),
                'priority' => 30,
            )
        );

        $wp_customize->add_section($this->campaign_content_section_id, array(
                'title'    => __('Content', 'mailoptin'),
                'priority' => 40,
            )
        );

        $wp_customize->add_section($this->campaign_footer_section_id, array(
                'title'    => __('Footer', 'mailoptin'),
                'priority' => 50,
            )
        );

        $wp_customize->add_section($this->campaign_send_email_section_id, array(
                'title'    => __('Send Test Email', 'mailoptin'),
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
                'type'      => 'option',
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
                    'label'       => __('Background Color', 'mailoptin'),
                    'description' => __("Save any changes first and then click the button to send a test email to $admin_email", 'mailoptin'),
                    'section'     => $this->campaign_send_email_section_id,
                    'settings'    => $this->option_prefix . '[send_test_email]',
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