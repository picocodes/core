<?php

namespace MailOptin\Core\Admin\Customizer\OptinForm;

use MailOptin\Core\Admin\Customizer\CustomizerTrait;
use MailOptin\Core\Admin\Customizer\UpsellCustomizerSection;
use MailOptin\Core\OptinForms\AbstractOptinForm;
use MailOptin\Core\Repositories\OptinCampaignsRepository;

class Customizer
{
    use CustomizerTrait;

    /** @var string template database option name */
    public $optin_form_settings = MO_OPTIN_CAMPAIGN_WP_OPTION_NAME;

    /** @var int optin campaign ID */
    public $optin_campaign_id;

    /** @var string optin type */
    public $optin_campaign_type;

    /** @var string ID of optin form design customizer section. */
    public $design_section_id = 'mo_design_section';

    /** @var string ID of optin form headline customizer section. */
    public $headline_section_id = 'mo_headline_section';

    /** @var string ID of optin form description customizer section. */
    public $description_section_id = 'mo_description_section';

    /** @var string ID of optin form note customizer section. */
    public $note_section_id = 'mo_note_section';

    /** @var string ID of optin form fields customizer section. */
    public $fields_section_id = 'mo_fields_section';

    /** @var string ID of optin form configuration customizer section. */
    public $configuration_section_id = 'mo_configuration_section';

    /** @var string ID of optin form effects customizer section. */
    public $effects_section_id = 'mo_effects_section';

    /** @var string ID of optin form integration customizer section. */
    public $integration_section_id = 'mo_integration_section';

    /** @var string ID of optin form integration customizer section. */
    public $success_section_id = 'mo_success_section';

    /** @var string panel ID of display rules. */
    public $display_rules_panel_id = 'mo_display_rules_panel_section';

    /** @var string ID of "WordPress page filter display rule" customizer section. */
    public $setup_display_rule_section_id = 'mo_setup_display_rule_section';

    /** @var string ID of "Visitor has viewed ‘X’ pages" customizer section. */
    public $x_page_views_display_rule_section_id = 'mo_wp_x_page_views_display_rule_section';

    /** @var string ID of "exit intent" customizer section. */
    public $exit_intent_display_rule_section_id = 'mo_wp_exit_intent_display_rule_section';

    public $schedule_display_rule_section_id = 'mo_wp_schedule_display_rule_section';

    /** @var string ID of "link click" customizer section. */
    public $click_launch_display_rule_section_id = 'mo_wp_click_launch_display_rule_section';

    /** @var string ID of "After ‘X’ seconds" customizer section. */
    public $x_seconds_display_rule_section_id = 'mo_wp_x_seconds_display_rule_section';

    /** @var string ID of "After ‘X’ percent scroll" customizer section. */
    public $x_scroll_display_rule_section_id = 'mo_wp_x_scroll_display_rule_section';

    /** @var string ID of "WordPress page filter display rule" customizer section. */
    public $page_filter_display_rule_section_id = 'mo_wp_page_filter_display_rule_section';

    /** @var string ID of "user targeting" customizer section. */
    public $user_targeting_display_rule_section_id = 'mo_wp_user_filter_display_rule_section';

    /**
     * Customizer constructor.
     */
    public function __construct()
    {
        if (!empty($_REQUEST['mailoptin_optin_campaign_id'])) {

            $this->clean_up_customizer();
            $this->modify_customizer_publish_button();

            add_action('customize_controls_enqueue_scripts', array($this, 'customizer_css_js'));

            add_action('customize_controls_print_footer_scripts', [$this, 'customizer_footer_scripts']);

            $this->optin_campaign_id = absint($_REQUEST['mailoptin_optin_campaign_id']);
            $this->optin_campaign_type = OptinCampaignsRepository::get_optin_campaign_type($this->optin_campaign_id);

            add_action('customize_controls_init', function () {
                echo '<script type="text/javascript">';
                echo "var mailoptin_optin_campaign_id = $this->optin_campaign_id;";
                echo '</script>';
            });

            add_filter('template_include', array($this, 'include_optin_form_customizer_template'));

            add_filter('gettext', array($this, 'rewrite_customizer_panel_description'), 10, 3);
            add_filter('gettext', array($this, 'rewrite_customizer_save_publish_label'), 10, 3);

            // remove all sections other han that of template customizer.
            add_action('customize_section_active', array($this, 'remove_sections'), 10, 2);

            // Remove all customizer panels.
            add_action('customize_panel_active', array($this, 'remove_panels'), 10, 2);

            add_action('customize_register', array($this, 'register_optin_form_customizer'));

            // save edited optin campaign title
            add_action('customize_save', array($this, 'save_optin_campaign_title'));

            // save edited optin campaign title
            add_action('customize_save_after', array($this, 'burst_cache_after_customizer_save'));

            // Disable admin bar.
            add_filter('show_admin_bar', '__return_false');
        }
    }

    /**
     * Add activation switch to optin customizer.
     */
    public function add_activate_switch()
    {
        if (OptinCampaignsRepository::is_split_test_variant($this->optin_campaign_id)) return;

        $input_value = OptinCampaignsRepository::is_activated($this->optin_campaign_id) ? 'yes' : 'no';
        $checked = ($input_value == 'yes') ? 'checked="checked"' : null;
        $tooltip = __('Toggle to activate and deactivate optin.', 'mailoptin');

        $switch = sprintf(
            '<input id="mo-optin-activate-switch" type="checkbox" class="tgl tgl-light" value="%s" %s />',
            $input_value,
            $checked
        );

        $switch .= '<label id="mo-optin-active-switch" for="mo-optin-activate-switch" class="tgl-btn"></label>';
        $switch .= '<span title="' . $tooltip . '" class="mo-tooltipster dashicons dashicons-editor-help" style="margin: 9px 5px;font-size: 18px;cursor: pointer;"></span>';
        ?>
        <script type="text/javascript">
            jQuery(function () {
                jQuery('#customize-header-actions').prepend(jQuery('<?php echo $switch; ?>'));
            });
        </script>
        <?php
    }

    public function standard_plan_upsell()
    {
        if (defined('MAILOPTIN_PRO_PLUGIN_TYPE')) return;

        $content = sprintf(
            '<div class="mo-pro"><a href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=display_rules_panel_standard" target="_blank">%s</a></div>',
            __('Upgrade Available', 'mailoptin')
        );

        $content .= sprintf(
            __('Upgrade to %sPro plan%s or higher to unlock display scheduling and advance triggers such as %3$sAdBlock Detection%4$s, %3$sNew vs Returning Visitors%4$s, %3$sReferral Detection%4$s.', 'mailoptin'),
            '<a target="_blank" href="https://mailoptin.io/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=display_rules_panel_standard2">',
            '</a>',
            '<strong>',
            '</strong>'
        );
        ?>
        <script type="text/html" id="tmpl-mo-optin-trigger-upsell">
            <div class="customize-mo-custom-content-block" style="margin:0">
                <?php echo $content; ?>
            </div>
        </script>
        <script type="text/javascript">
            jQuery(function () {
                var template = wp.template('mo-optin-trigger-upsell');
                $('#sub-accordion-panel-mo_display_rules_panel_section').append(template());
            });
        </script>
        <?php
    }

    /**
     * Add activation switch to optin customizer
     */
    public function customizer_footer_scripts()
    {
        $this->add_activate_switch();
        $this->standard_plan_upsell();
        do_action('mo_optin_customizer_footer_scripts', $this);
    }

    public function selector_mapping_scripts_styles()
    {
        $mappings = apply_filters('mo_optin_selectors_mapping', [
            [
                'selector' => '.mo-optin-form-headline',
                'type' => 'section',
                'value' => $this->headline_section_id
            ],
            [
                'selector' => '.mo-optin-form-description',
                'type' => 'section',
                'value' => $this->description_section_id
            ],
            [
                'selector' => '.mo-optin-form-note',
                'type' => 'section',
                'value' => $this->note_section_id
            ],
            [
                'selector' => '.mo-optin-form-close-icon',
                'type' => 'control',
                'value' => 'hide_close_button'
            ],
            [
                'selector' => '.mo-optin-form-name-field',
                'type' => 'control',
                'value' => 'name_field_placeholder'
            ],
            [
                'selector' => '.mo-optin-form-email-field',
                'type' => 'control',
                'value' => 'email_field_placeholder'
            ],
            [
                'selector' => '.mo-optin-form-submit-button',
                'type' => 'control',
                'value' => 'submit_button'
            ],
            [
                'selector' => '.mo-optin-form-image',
                'type' => 'control',
                'value' => 'form_image'
            ],
            [
                'selector' => '.mo-optin-form-background-image',
                'type' => 'control',
                'value' => 'form_background_image'
            ],
            [
                'selector' => '.mo-optin-form-cta-button',
                'type' => 'control',
                'value' => 'input[type="submit].cta_button_action'
            ],
            [
                'selector' => '.rescript_miniHeader',
                'type' => 'control',
                'value' => 'mini_headline'
            ],
            [
                'selector' => '.gridgum_header2',
                'type' => 'control',
                'value' => 'mini_headline'
            ],
            [
                'selector' => '.columbine-miniText',
                'type' => 'control',
                'value' => 'mini_headline'
            ]
        ]);

        if (defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $mappings[] =
                [
                    'selector' => '.mo-optin-powered-by',
                    'type' => 'control',
                    'value' => 'remove_branding'
                ];
        }

        // source: https://stackoverflow.com/a/35957563/2648410
        $last_mapping = array_values(array_slice($mappings, -1))[0];
        $css_selectors = '';
        foreach ($mappings as $mapping) {
            $css_selectors .= $mapping['selector'] . ':hover';
            // do not add comma to trailing/last selector
            if ($mapping != $last_mapping) {
                $css_selectors .= ',';
            }
        }
        $css_selectors .= '{background: rgba(255, 185, 0, 0.52) !important;border: 1px dashed #ffb900 !important;cursor: pointer !important;}';
        ?>

        <style type="text/css"><?php echo $css_selectors; ?></style>
        <script type="text/javascript">
            var mailoptin_option_mapping = <?php echo wp_json_encode($mappings); ?>;
            (function ($) {
                $(function () {
                    $.each(mailoptin_option_mapping, function (key, value) {
                        $(document).on('click', value.selector, function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            if (value.type === 'section') {
                                parent.wp.customize.section(value.value).focus()
                            }
                            if (value.type === 'control') {
                                parent.wp.customize.control('mo_optin_campaign[' + mailoptin_optin_campaign_id + '][' + value.value + ']').focus()
                            }
                            if (value.type === 'panel') {
                                parent.wp.customize.panel(value.value).focus()
                            }
                        });
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Burst / clear optin cache after changes in customizer.
     */
    public function burst_cache_after_customizer_save()
    {
        if (isset($_REQUEST['customized']) && !empty($_REQUEST['mailoptin_optin_campaign_id'])) {
            $optin_id = absint($_REQUEST['mailoptin_optin_campaign_id']);
            OptinCampaignsRepository::burst_cache($optin_id);
        }
    }

    /**
     * Enqueue CSS / JavaScript for optin form customizer controls.
     */
    public function customizer_css_js()
    {
        // monkey patch
        wp_add_inline_script('customize-controls', '(function ( api ) {
              api.bind( "ready", function () {
                  var _query = api.previewer.query;
                      api.previewer.query = function () {
                          var query = _query.call( this );
                          query.mailoptin_optin_campaign_id = "' . $this->optin_campaign_id . '";
                          return query;
                      };
                  // needed to ensure save button is publising changes and not saving draft.
                  // esp for wp.com business hosting with save button set to draft by default.
                  api.state("selectedChangesetStatus").set("publish");
                  });
              })( wp.customize );'
        );

        wp_enqueue_script(
            'mailoptin-optin-form-contextual-customizer-controls',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/contextual-customizer-controls.js',
            array('customize-controls'),
            MAILOPTIN_VERSION_NUMBER
        );

        wp_enqueue_script(
            'mailoptin-optin-form-fetch-customizer-connect-list-controls',
            MAILOPTIN_ASSETS_URL . 'js/customizer-controls/fetch-customizer-connect-list.js',
            array('customize-controls'),
            MAILOPTIN_VERSION_NUMBER
        );

        wp_enqueue_style('mailoptin-customizer', MAILOPTIN_ASSETS_URL . 'css/admin/customizer-stylesheet.css');

        do_action('mo_optin_customizer_css_js_enqueue', $this);
    }


    /**
     * By default, customizer uses blog name as panel title
     * hence the rewrite to template name if pass as query sting to customizer url.
     * default to 'Optin Form'.
     *
     * @param string $blogname
     *
     * @return string
     */
    public function rewrite_customizer_panel_title($blogname)
    {
        $optin_form_name = OptinCampaignsRepository::get_optin_campaign_name($this->optin_campaign_id);

        return $optin_form_name ?: __('Optin Form', 'mailoptin');
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
                'The customizer allows you to make and preview changes to MailOptin powered optin forms.',
                'mailoptin'
            );
        }

        return $translations;
    }

    /**
     * Rewrite "save & publish" to "save changes"
     *
     * @param string $translations
     * @param string $text
     * @param string $domain
     *
     * @return mixed
     */
    public function rewrite_customizer_save_publish_label($translations, $text, $domain)
    {
        if ($text == 'Save &amp; Publish') {
            $translations = __('Save Changes', 'mailoptin');
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
            'mo_optin_customizer_sections_ids',
            array(
                $this->configuration_section_id,
                $this->integration_section_id,
                $this->design_section_id,
                $this->fields_section_id,
                $this->headline_section_id,
                $this->description_section_id,
                $this->note_section_id,
                $this->setup_display_rule_section_id,
                $this->user_targeting_display_rule_section_id,
                $this->click_launch_display_rule_section_id,
                $this->exit_intent_display_rule_section_id,
                $this->x_seconds_display_rule_section_id,
                $this->x_scroll_display_rule_section_id,
                $this->x_page_views_display_rule_section_id,
                $this->page_filter_display_rule_section_id,
                $this->user_targeting_display_rule_section_id,
                $this->schedule_display_rule_section_id,
                $this->success_section_id
            )
        );

        return in_array($section->id, $sections_ids);
    }

    /**
     * Remove non-mailoptin customizer panels.
     *
     * @param $active
     * @param $panel
     *
     * @return bool
     */
    public function remove_panels($active, $panel)
    {
        $panel_ids = apply_filters('mo_optin_customizer_panel_ids', array($this->display_rules_panel_id));

        return in_array($panel->id, $panel_ids);
    }

    /**
     * Include template preview template.
     *
     * @param string $template
     *
     * @return string
     */
    public function include_optin_form_customizer_template($template)
    {
        if (is_customize_preview() &&
            wp_verify_nonce($_REQUEST['_wpnonce'], 'mailoptin-preview-optin-form')
        ) {
            $template = MAILOPTIN_SRC . 'Admin/Customizer/OptinForm/optin-form-preview.php';
        } else {
            wp_redirect(MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE);
            exit;
        }

        return $template;
    }

    /**
     * Handles filtering of panels, settings and controls base on optin type condition.
     * e.g a optin type might need a setting/control/panel inactive.
     */
    public function contextual_section_panel_settings_control()
    {
        add_filter('mo_optin_customizer_disable_description_section', function ($status) {
            if ($this->optin_campaign_type == 'bar') {
                $status = true;
                add_filter('mailoptin_tinymce_customizer_control_count', function ($count) {
                    return --$count;
                });
            }

            return $status;
        });

        add_filter('mo_optin_customizer_disable_note_section', function ($status) {
            if ($this->optin_campaign_type == 'bar') {
                $status = true;
                add_filter('mailoptin_tinymce_customizer_control_count', function ($count) {
                    return --$count;
                });
            }

            return $status;
        });

        add_filter('mo_optin_form_customizer_configuration_controls',
            function ($controls, $wp_customize, $option_prefix, $customizerClassInstance) {
                // do not display these controls if optin type is sidebar.
                if (in_array($customizerClassInstance->optin_campaign_type, ['sidebar', 'inpost'])) {
                    unset($controls['cookie']);
                    unset($controls['success_cookie']);
                }

                if ($customizerClassInstance->optin_campaign_type == 'bar') {
                    unset($controls['hide_headline']);
                    unset($controls['hide_description']);
                    unset($controls['hide_note']);
                    unset($controls['hide_description']);
                }

                if ($customizerClassInstance->optin_campaign_type != 'bar') {
                    unset($controls['bar_position']);
                    unset($controls['bar_sticky']);
                }

                if ($customizerClassInstance->optin_campaign_type != 'slidein') {
                    unset($controls['slidein_position']);
                }

                if (!in_array($customizerClassInstance->optin_campaign_type, ['lightbox', 'slidein', 'bar'])) {
                    unset($controls['hide_close_button']);
                }

                return $controls;
            }, 10, 4);

        add_filter('mo_optin_form_customizer_page_filter_controls',
            function ($controls, $wp_customize, $option_prefix, $customizerClassInstance) {
                // do not display these controls if optin type is inpost.
                if ($customizerClassInstance->optin_campaign_type == 'inpost') {
                    unset($controls['load_optin_index']);
                }

                return $controls;
            }, 10, 4);

        add_filter('mo_optin_form_customizer_configuration_controls',
            function ($controls, $wp_customize, $option_prefix, $customizerClassInstance) {
                // restrict inpost_form_optin_position control to only inpost type
                if ('inpost' != $customizerClassInstance->optin_campaign_type) {
                    unset($controls['inpost_form_optin_position']);
                }

                return $controls;
            }, 10, 4);

        add_filter('mo_optin_form_customizer_note_controls',
            function ($controls, $wp_customize, $option_prefix, $customizerClassInstance) {
                // restrict inpost_form_optin_position control to only inpost type
                if ('inpost' != $customizerClassInstance->optin_campaign_type) {
                    unset($controls['inpost_form_optin_position']);
                }

                return $controls;
            }, 10, 4);
    }

    /**
     * Customizer registration.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function register_optin_form_customizer($wp_customize)
    {
        $optin_campaign_id = absint($_REQUEST['mailoptin_optin_campaign_id']);

        $option_prefix = $this->optin_form_settings . '[' . $optin_campaign_id . ']';

        do_action('mailoptin_register_optin_form_customizer', $optin_campaign_id);

        $optin_class_instance = OptinFormFactory::make($optin_campaign_id);

        add_action('wp_head', [$this, 'selector_mapping_scripts_styles']);

        // $result is false of optin form class do not exist.
        if (!$optin_class_instance) {
            wp_redirect(add_query_arg('optin-error', 'class-not-found', MAILOPTIN_OPTIN_CAMPAIGNS_SETTINGS_PAGE));
            exit;
        }

        $this->contextual_section_panel_settings_control();

        $this->register_custom_section($wp_customize);
        $this->register_control_type($wp_customize);

        $this->add_sections($wp_customize);
        $this->add_panels($wp_customize);
        $this->add_settings($wp_customize, $option_prefix);
        $this->add_controls($wp_customize, $option_prefix, $optin_class_instance);

        // rewrite panel name from blog name to template name.
        add_filter('pre_option_blogname', [$this, 'rewrite_customizer_panel_title']);
    }

    /**
     * @param \WP_Customize_Manager $wp_customize_manager
     */
    public function save_optin_campaign_title($wp_customize_manager)
    {
        $optin_campaign_id = absint($_POST['mailoptin_optin_campaign_id']);
        $option_name = "mo_optin_campaign[$optin_campaign_id][campaign_title]";
        $posted_values = $wp_customize_manager->unsanitized_post_values();

        if (array_key_exists($option_name, $posted_values)) {
            OptinCampaignsRepository::updateCampaignName(
                sanitize_text_field($posted_values[$option_name]),
                $optin_campaign_id
            );
        }
    }

    /**
     * Register customizer panels.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_panels($wp_customize)
    {
        do_action('mo_optin_before_display_rules_panel', $wp_customize, $this);

        $wp_customize->add_panel($this->display_rules_panel_id, array(
                'title' => __('Display Rules', 'mailoptin'),
                'description' => __('Configure how this optin campaign will be shown to visitors or users.', 'mailoptin')
            )
        );

        do_action('mo_optin_after_display_rules_panel', $wp_customize, $this);
    }

    /**
     * Add sections to customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_sections($wp_customize)
    {
        do_action('mo_optin_before_design_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_upsell_section', false)) {
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

        if (!apply_filters('mo_optin_customizer_disable_design_section', false)) {
            $wp_customize->add_section($this->design_section_id, array(
                    'title' => __('Design', 'mailoptin'),
                    'priority' => 5,
                )
            );
        }

        do_action('mo_optin_after_design_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_headline_section', false)) {
            $wp_customize->add_section($this->headline_section_id, array(
                    'title' => __('Headline', 'mailoptin'),
                    'priority' => 10,
                )
            );
        }
        do_action('mo_optin_after_headline_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_description_section', false)) {
            $wp_customize->add_section($this->description_section_id, array(
                    'title' => __('Description', 'mailoptin'),
                    'priority' => 15,
                )
            );
        }

        do_action('mo_optin_after_description_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_note_section', false)) {
            $wp_customize->add_section($this->note_section_id, array(
                    'title' => __('Note', 'mailoptin'),
                    'priority' => 20,
                )
            );
        }

        do_action('mo_optin_after_note_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_fields_section', false)) {
            $wp_customize->add_section($this->fields_section_id, array(
                    'title' => __('Fields', 'mailoptin'),
                    'priority' => 25,
                )
            );
        }

        do_action('mo_optin_after_fields_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_configuration_section', false)) {
            $wp_customize->add_section($this->configuration_section_id, array(
                    'title' => __('Configuration', 'mailoptin'),
                    'priority' => 30,
                )
            );
        }

        do_action('mo_optin_after_configuration_customizer_section', $wp_customize, $this);

        if (!apply_filters('mo_optin_customizer_disable_integration_section', false)) {
            $wp_customize->add_section($this->integration_section_id, array(
                    'title' => __('Integration', 'mailoptin'),
                    'priority' => 35,
                )
            );
        }

        if (!apply_filters('mo_optin_customizer_disable_success_section', false)) {
            $wp_customize->add_section($this->success_section_id, array(
                    'title' => __('After Conversion', 'mailoptin'),
                    'priority' => 40,
                )
            );
        }

        do_action('mo_optin_after_integration_customizer_section', $wp_customize, $this);

        $this->display_rules_sections($wp_customize);

        do_action('mo_optin_after_display_rules_customizer_section', $wp_customize, $this);
    }


    /**
     * Add display rules sections to customizer.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function display_rules_sections($wp_customize)
    {
        do_action('mo_optin_before_core_display_rules_section', $wp_customize, $this);

        if (defined('MAILOPTIN_DETACH_LIBSODIUM')) {
            $wp_customize->add_section($this->page_filter_display_rule_section_id, array(
                    'title' => __('Page Targeting', 'mailoptin'),
                    'panel' => $this->display_rules_panel_id
                )
            );

            $wp_customize->add_section($this->user_targeting_display_rule_section_id, array(
                    'title' => __('User Targeting', 'mailoptin'),
                    'panel' => $this->display_rules_panel_id
                )
            );

            do_action('mo_optin_after_page_user_targeting_display_rule_section', $wp_customize, $this);
        } else {
            $wp_customize->add_section($this->page_filter_display_rule_section_id, array(
                    'title' => __('Display Rules', 'mailoptin')
                )
            );
        }

        do_action('mo_optin_after_core_display_rules_section', $wp_customize, $this);
    }


    /**
     * Add customizer settings.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function add_settings($wp_customize, $option_prefix)
    {
        $instance = new CustomizerSettings($wp_customize, $option_prefix, $this);
        $instance->design_settings();
        $instance->headline_settings();
        $instance->description_settings();
        $instance->note_settings();
        $instance->fields_settings();
        $instance->configuration_settings();
        $instance->integration_settings();
        $instance->after_conversion_settings();
        $instance->display_rules_settings();

        do_action('mo_optin_customizer_settings', $wp_customize, $option_prefix, $this);
    }


    /**
     * Add customizer controls.
     *
     * @param \WP_Customize_Manager $wp_customize
     * @param string $option_prefix
     * @param AbstractOptinForm $optin_class_instance
     */
    public function add_controls($wp_customize, $option_prefix, $optin_class_instance = null)
    {
        $instance = new CustomizerControls($wp_customize, $option_prefix, $this, $optin_class_instance);
        $instance->design_controls();
        $instance->headline_controls();
        $instance->description_controls();
        $instance->note_controls();
        $instance->fields_controls();
        $instance->configuration_controls();
        $instance->integration_controls();
        $instance->after_conversion_controls();
        $instance->page_filter_display_rule_controls();
        $instance->user_filter_display_rule_controls();

        do_action('mo_optin_after_customizer_controls', $instance, $wp_customize, $option_prefix, $this, $optin_class_instance);
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