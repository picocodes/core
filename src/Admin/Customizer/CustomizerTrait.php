<?php

namespace MailOptin\Core\Admin\Customizer;


trait CustomizerTrait
{
    public function modify_customizer_publish_button()
    {
        add_filter('gettext', function ($translations, $text, $domain) {
            if ($domain == 'default' && $text == 'Publish') {
                $translations = __('Save Changes', 'mailoptin');
            }
            if ($domain == 'default' && $text == 'Published') {
                $translations = __('Saved', 'mailoptin');
            }

            return $translations;
        }, 10, 3);
    }

    /**
     * @param \WP_Customize_Manager $wp_customize
     */
    public function register_custom_section($wp_customize)
    {
        $wp_customize->register_section_type('MailOptin\Core\Admin\Customizer\UpsellCustomizerSection');
    }

    /**
     * Registered customize control as eligible to be rendered via JS and created dynamically.
     *
     * @param \WP_Customize_Manager $wp_customize
     */
    public function register_control_type($wp_customize)
    {
        $controls = apply_filters('mo_optin_registered_control_types', [
            'MailOptin\Core\Admin\Customizer\CustomControls\WP_Customize_Button_Set_Control'
        ]);

        foreach ($controls as $control) {
            $wp_customize->register_control_type($control);
        }

        do_action('mo_customizer_register_control_type', $wp_customize);
    }

    public function clean_up_customizer()
    {
        add_action('init', function () {

            remove_all_actions('admin_print_footer_scripts');

            // remove all custom media button added by plugins and core.
            remove_all_actions('media_buttons');
            remove_all_filters('media_buttons_context');
            remove_all_filters('mce_buttons', 10);
            remove_all_filters('mce_external_plugins', 10);

            remove_all_actions('wp_head');
            remove_all_actions('wp_print_styles');
            remove_all_actions('wp_print_head_scripts');
            remove_all_actions('wp_footer');

            // Handle `wp_head`
            add_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_head', 'wp_print_styles', 8);
            add_action('wp_head', 'wp_print_head_scripts', 9);
            add_action('wp_head', 'wp_site_icon');

            // Handle `wp_footer`
            add_action('wp_footer', 'wp_print_footer_scripts', 20);

            // add core media button back.
            add_action('media_buttons', 'media_buttons');

            $wp_get_theme = wp_get_theme();

            $active_plugins = array_reduce(get_option('active_plugins'), function ($carry, $item) {
                $name = dirname($item);
                if ($name != 'mailoptin' && $name != '.') {
                    $carry[] = $name;
                }

                return $carry;
            });

            $active_plugins = ! is_array($active_plugins) ? [] : $active_plugins;

            add_action('customize_controls_enqueue_scripts', function () use ($wp_get_theme, $active_plugins) {
                global $wp_styles;
                global $wp_scripts;

                $child_theme  = $wp_get_theme->get_stylesheet();
                $parent_theme = $wp_get_theme->get_template();

                foreach ($wp_scripts->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, "themes/$child_theme/") !== false || strpos($src, "themes/$parent_theme/") !== false) {
                        unset($wp_scripts->registered[$key]);
                    }

                    if (strpos($src, "/uploads/$child_theme/") !== false || strpos($src, "/uploads/$parent_theme/") !== false) {
                        unset($wp_scripts->registered[$key]);
                    }

                    foreach ($active_plugins as $active_plugin) {
                        if (strpos($src, "plugins/$active_plugin/") !== false) {
                            unset($wp_scripts->registered[$key]);
                        }
                    }
                }

                foreach ($wp_styles->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, "themes/$child_theme/") !== false || strpos($src, "themes/$parent_theme/") !== false) {
                        unset($wp_styles->registered[$key]);
                    }

                    if (strpos($src, "/uploads/$child_theme/") !== false || strpos($src, "/uploads/$parent_theme/") !== false) {
                        unset($wp_styles->registered[$key]);
                    }

                    foreach ($active_plugins as $active_plugin) {
                        if (strpos($src, "plugins/$active_plugin/") !== false) {
                            unset($wp_styles->registered[$key]);
                        }
                    }
                }

            }, 9999999999999);

            add_action('wp_enqueue_scripts', function () use ($wp_get_theme, $active_plugins) {
                global $wp_styles;
                global $wp_scripts;

                $child_theme  = $wp_get_theme->get_stylesheet();
                $parent_theme = $wp_get_theme->get_template();

                foreach ($wp_scripts->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, "themes/$child_theme/") !== false || strpos($src, "themes/$parent_theme/") !== false) {
                        unset($wp_scripts->registered[$key]);
                    }

                    if (strpos($src, "/uploads/$child_theme/") !== false || strpos($src, "/uploads/$parent_theme/") !== false) {
                        unset($wp_scripts->registered[$key]);
                    }

                    foreach ($active_plugins as $active_plugin) {
                        if (strpos($src, "plugins/$active_plugin/") !== false) {
                            unset($wp_scripts->registered[$key]);
                        }
                    }
                }

                foreach ($wp_styles->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, "themes/$child_theme/") !== false || strpos($src, "themes/$parent_theme/") !== false) {
                        unset($wp_styles->registered[$key]);
                    }

                    if (strpos($src, "/uploads/$child_theme/") !== false || strpos($src, "/uploads/$parent_theme/") !== false) {
                        unset($wp_styles->registered[$key]);
                    }

                    foreach ($active_plugins as $active_plugin) {
                        if (strpos($src, "plugins/$active_plugin/") !== false) {
                            unset($wp_styles->registered[$key]);
                        }
                    }
                }

            }, 9999999999999);

            if (class_exists('Astra_Customizer') && method_exists('Astra_Customizer', 'print_footer_scripts')) {
                remove_action('customize_controls_print_footer_scripts', [\Astra_Customizer::get_instance(), 'print_footer_scripts']);
            }

            if(function_exists('td_customize_js')) {
                remove_action('customize_controls_print_footer_scripts', 'td_customize_js');
            }

        }, 9999999999999);
    }
}