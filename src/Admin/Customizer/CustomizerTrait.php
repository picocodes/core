<?php

namespace MailOptin\Core\Admin\Customizer;


trait CustomizerTrait
{
    public function init()
    {
        add_action('init', [$this, 'clean_up_customizer'], 9999999999999);
    }

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

    public function clean_up_customizer()
    {
        add_action('init', function () {

            add_action('customize_controls_enqueue_scripts', function () {
                $cache = wp_get_theme();
                $child_theme = wp_get_theme()->get_stylesheet();
                $parent_theme = $cache->get_template();

                $wp_styles = wp_styles();
                $wp_scripts = wp_scripts();

                foreach ($wp_scripts->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, $child_theme) !== false || strpos($src, $parent_theme) !== false) {
                        wp_deregister_script($key);
                        wp_dequeue_script($key);
                    }
                }

                foreach ($wp_styles->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, $child_theme) !== false || strpos($src, $parent_theme) !== false) {
                        wp_deregister_style($key);
                        wp_dequeue_style($key);
                    }
                }

            }, 9999999999999);

            add_action('customize_controls_enqueue_scripts', function () {

                $cache = wp_get_theme();
                $child_theme = wp_get_theme()->get_stylesheet();
                $parent_theme = $cache->get_template();

                $wp_styles = wp_styles();
                $wp_scripts = wp_scripts();

                foreach ($wp_scripts->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, $child_theme) !== false || strpos($src, $parent_theme) !== false) {
                        wp_deregister_script($key);
                        wp_dequeue_script($key);
                    }
                }

                foreach ($wp_styles->registered as $key => $value) {
                    $src = $value->src;
                    if (strpos($src, $child_theme) !== false || strpos($src, $parent_theme) !== false) {
                        wp_deregister_style($key);
                        wp_dequeue_style($key);
                    }
                }

            }, 9999999999999);

            // remove all custom media button added by plugins and core.
            remove_all_actions('media_buttons');
            remove_all_actions('wp_head');
            remove_all_actions('wp_print_styles');
            remove_all_actions('wp_print_head_scripts');
            remove_all_actions('wp_footer');

            // Handle `wp_head`
            add_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_head', 'wp_print_styles', 8);
            add_action('wp_head', 'wp_print_head_scripts', 9);
            add_action('wp_head', 'wp_site_icon');
            // add core media button back.
            add_action('media_buttons', 'media_buttons');
            // Handle `wp_footer`
            add_action('wp_footer', 'wp_print_footer_scripts', 20);

            if (class_exists('Astra_Customizer') && method_exists('Astra_Customizer', 'print_footer_scripts')) {
                remove_action('customize_controls_print_footer_scripts', [\Astra_Customizer::get_instance(), 'print_footer_scripts']);
            }

        },
            9999999999999
        );
    }
}