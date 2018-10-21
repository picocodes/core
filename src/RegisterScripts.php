<?php

namespace MailOptin\Core;


class RegisterScripts
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_css'));
        add_action('admin_enqueue_scripts', [$this, 'admin_js']);
        add_action('admin_enqueue_scripts', [$this, 'fancybox_assets']);
        add_action('wp_enqueue_scripts', array($this, 'public_css'));
        add_action('wp_enqueue_scripts', array($this, 'public_js'));
    }

    public function fancybox_assets()
    {
        if ( ! defined('MAILOPTIN_DETACH_LIBSODIUM'))
            return;

        wp_register_script('mailoptin-fancybox', MAILOPTIN_LIBSODIUM_ASSETS_URL . 'fancybox/jquery.fancybox.min.js', ['jquery'], false, true);
        wp_register_script('mailoptin-init-fancybox', MAILOPTIN_ASSETS_URL . 'js/admin/fancybox-init.js', ['jquery'], false, true);
        wp_register_style('mailoptin-fancybox', MAILOPTIN_LIBSODIUM_ASSETS_URL . 'fancybox/jquery.fancybox.min.css', false, true);
        wp_register_style('mailoptin-activate-fancybox', MAILOPTIN_ASSETS_URL . 'css/admin/fancybox.css', false, true);
    }

    public static function fancybox_scripts()
    {
        if ( ! defined('MAILOPTIN_DETACH_LIBSODIUM'))
            return;

        wp_enqueue_script('mailoptin-fancybox');
        wp_enqueue_script('mailoptin-init-fancybox');
        wp_enqueue_style('mailoptin-fancybox');
        wp_enqueue_style('mailoptin-activate-fancybox');
    }

    /**
     * Admin JS
     */
    public function admin_js()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('underscore');
        wp_enqueue_script('mailoptin-admin-tooltipster', MAILOPTIN_ASSETS_URL . 'tooltipster/bundle.min.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        wp_enqueue_script('mailoptin-admin-tooltipster-init', MAILOPTIN_ASSETS_URL . 'tooltipster/init.js', array('jquery', 'mailoptin-admin-tooltipster'), MAILOPTIN_VERSION_NUMBER, true);
        wp_enqueue_script('mailoptin-admin-script', MAILOPTIN_ASSETS_URL . 'js/admin/admin-script.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        wp_enqueue_script('mailoptin-ab-test-script', MAILOPTIN_ASSETS_URL . 'js/admin/ab-test.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        if (defined('MAILOPTIN_LIBSODIUM_ASSETS_URL')) {
            wp_enqueue_script('mailoptin-highcharts', MAILOPTIN_LIBSODIUM_ASSETS_URL . 'js/admin/highcharts.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        }
        wp_enqueue_script('mailoptin-add-optin-campaign', MAILOPTIN_ASSETS_URL . 'js/admin/new-optin-campaign.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        wp_enqueue_script('mailoptin-optin-type-selection', MAILOPTIN_ASSETS_URL . 'js/admin/optin-type-selection.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        wp_enqueue_script('mailoptin-add-email-campaign', MAILOPTIN_ASSETS_URL . 'js/admin/new-email-campaign.js', array('jquery'), MAILOPTIN_VERSION_NUMBER, true);
        $this->global_js_variables('mailoptin-add-optin-campaign');
        do_action('mo_admin_js_enqueue');
    }

    /**
     * Enqueue public scripts and styles.
     */
    public function public_js()
    {
        wp_enqueue_script('jquery');
        $this->modal_scripts();
    }

    /**
     * Enqueue modal optin scripts.
     */
    public function modal_scripts()
    {
        // trailing "true" function argument not needed because we want it loaded before hidden optin markup display in footer.
        wp_enqueue_script('mo-google-webfont', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js', false, MAILOPTIN_VERSION_NUMBER, true);

        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) {
            wp_enqueue_style('mo-animate', MAILOPTIN_ASSETS_URL . 'css/animate.css', false, MAILOPTIN_VERSION_NUMBER);
            wp_enqueue_script('mo-requirejs', MAILOPTIN_ASSETS_URL . 'js/src/require.js', [], MAILOPTIN_VERSION_NUMBER, true);
            wp_enqueue_script('mailoptin', MAILOPTIN_ASSETS_URL . 'js/src/main.js', ['jquery', 'mo-requirejs'], MAILOPTIN_VERSION_NUMBER, true);
        } else {
            if (is_customize_preview()) {
                // when plugin like nextgen gallery is active, loading mailoptin.js in footer do not make lightbox, slidein, bar load
                // in customizer. but on header works.
                wp_enqueue_script('mailoptin', MAILOPTIN_ASSETS_URL . 'js/mailoptin.min.js', ['jquery'], MAILOPTIN_VERSION_NUMBER);
            } else {
                wp_enqueue_script('mailoptin', MAILOPTIN_ASSETS_URL . 'js/mailoptin.min.js', ['jquery'], MAILOPTIN_VERSION_NUMBER, true);
            }
        }

        $this->global_js_variables('mailoptin');
    }

    /**
     * Global JS variables by required by mailoptin.
     *
     * @param string $handle handle to cling to.
     */
    public function global_js_variables($handle)
    {
        wp_localize_script(
            $handle, 'mailoptin_globals',
            apply_filters('mo_mailoptin_js_globals', array(
                'admin_url'                   => admin_url(),
                'public_js'                   => MAILOPTIN_ASSETS_URL . 'js/src',
                'nonce'                       => wp_create_nonce('mailoptin-admin-nonce'),
                'mailoptin_ajaxurl'           => AjaxHandler::get_endpoint(),
                'ajaxurl'                     => admin_url('admin-ajax.php'),
                'split_test_start_label'      => __('Start Test', 'mailoptin'),
                'split_test_pause_label'      => __('Pause Test', 'mailoptin'),
                'is_customize_preview'        => is_customize_preview() ? 'true' : 'false',
                // for some weird reason, boolen false is converted to empty and true to "1" hence the use of 'false' in string form.
                'disable_impression_tracking' => apply_filters('mo_disable_impression_tracking', 'false'),
                'chosen_search_placeholder'   => __('Type to search', 'mailoptin'),
                'js_confirm_text'             => __('Are you sure you want to do this?', 'mailoptin'),
                'js_clear_stat_text'          => __('Are you sure you want to do this? Clicking OK will delete all your optin analytics records.', 'mailoptin')
            ))
        );
    }

    /**
     * Admin CSS.
     */
    public function admin_css()
    {
        wp_enqueue_style('mailoptin-admin-tooltipster', MAILOPTIN_ASSETS_URL . 'tooltipster/bundle.min.css', [], MAILOPTIN_VERSION_NUMBER);
        wp_enqueue_style('mailoptin-admin-tooltipster-borderless', MAILOPTIN_ASSETS_URL . 'tooltipster/borderless.min.css', [], MAILOPTIN_VERSION_NUMBER);
        wp_enqueue_style('mailoptin-admin-tooltipster-light', MAILOPTIN_ASSETS_URL . 'tooltipster/light.min.css', [], MAILOPTIN_VERSION_NUMBER);
        wp_enqueue_style('mailoptin-admin', MAILOPTIN_ASSETS_URL . 'css/admin/admin.css', [], MAILOPTIN_VERSION_NUMBER);
        wp_enqueue_style('mailoptin-font-awesome', MAILOPTIN_ASSETS_URL . 'css/font-awesome/css/font-awesome.min.css', array(), MAILOPTIN_VERSION_NUMBER);

        wp_enqueue_style('mo-pure-css-toggle-buttons', MAILOPTIN_ASSETS_URL . 'js/customizer-controls/toggle-control/pure-css-togle-buttons.css', array(), false);

        $css = '
			.disabled-control-title {
				color: #a0a5aa;
			}
			input[type=checkbox].tgl-light:checked + .tgl-btn {
				background: #0085ba;
			}
			input[type=checkbox].tgl-light + .tgl-btn {
			  background: #a0a5aa;
			}
			input[type=checkbox].tgl-light + .tgl-btn:after {
			  background: #f7f7f7;
			}

			input[type=checkbox].tgl-ios:checked + .tgl-btn {
			  background: #0085ba;
			}

			input[type=checkbox].tgl-flat:checked + .tgl-btn {
			  border: 4px solid #0085ba;
			}
			input[type=checkbox].tgl-flat:checked + .tgl-btn:after {
			  background: #0085ba;
			}

		';
        wp_add_inline_style('mo-pure-css-toggle-buttons', $css);
    }

    /**
     * Front-end CSS
     */
    public function public_css()
    {
    }

    /**
     * @return RegisterScripts
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