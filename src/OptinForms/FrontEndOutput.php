<?php

namespace MailOptin\Core\OptinForms;

use MailOptin\Core\Admin\Customizer\OptinForm\OptinFormFactory;
use MailOptin\Core\Repositories\OptinCampaignsRepository as Repository;

class FrontEndOutput
{
    public function __construct()
    {
        add_action('wp_footer', array($this, 'load_optin'), 999999999);
    }

    public function load_optin()
    {
        if (is_customize_preview() || is_admin()) return;

        if (apply_filters('mo_disable_frontend_optin_output', false)) return;

        /**
         * get_queried_object_id() could be the ID of a term, category, author etc depending on view context.
         * @see \WP_Query::get_queried_object() from ln 4106
         */
        $post_id = is_singular() || is_front_page() ? get_queried_object_id() : 0;

        if (!$post_id) {
            if ('page' == get_option('show_on_front')) {
                $post_id = get_option('page_for_posts');
            }
        }

        /** @var array $post_categories categories of current post */
        $post_categories = wp_get_post_categories($post_id);

        /** @var string $post_post_type post type of current post */
        $post_post_type = get_post_type($post_id);

        $optin_ids = get_transient('mo_get_optin_ids_footer_display');

        if ($optin_ids === false) {
            $optin_ids = Repository::get_optin_campaign_ids(['sidebar', 'inpost']);
            set_transient('mo_get_optin_ids_footer_display', $optin_ids, HOUR_IN_SECONDS);
        }

        foreach ($optin_ids as $id) {

            $id = absint($id);

            do_action('mailoptin_before_footer_optin_display', $id, $optin_ids, $post_id);

            // if optin is not enabled, pass.
            if (!Repository::get_customizer_value($id, 'activate_optin')) {
                continue;
            }

            // if optin is disabled for logged users, continue
            if (Repository::get_customizer_value($id, 'hide_logged_in') && is_user_logged_in()) {
                continue;
            }

            // if display rule definition not defined, load globally
            if (!defined('MAILOPTIN_DISPLAY_RULES_FLAG') || Repository::get_customizer_value($id, 'load_optin_globally')) {
                echo OptinFormFactory::build($id);
            } else {
                $load_optin_index = Repository::get_customizer_value($id, 'load_optin_index');
                $posts_never_load = Repository::get_customizer_value($id, 'posts_never_load');
                $pages_never_load = Repository::get_customizer_value($id, 'pages_never_load');
                $cpt_never_load = Repository::get_customizer_value($id, 'cpt_never_load');
                $post_categories_load = Repository::get_customizer_value($id, 'post_categories_load');
                $exclusive_post_types_posts_load = Repository::get_customizer_value($id, 'exclusive_post_types_posts_load');
                $post_types_load = Repository::get_customizer_value($id, 'exclusive_post_types_load');

                // for custom display rules.
                if (apply_filters('mailoptin_footer_optin_output', false, $id, $optin_ids)) {
                    continue;
                }
                // if current view is neither frontpage, homepage, archive page or search page, return false.
                if (!empty($load_optin_index) && (!(is_front_page() || is_home() || is_archive() || is_search()))) {
                    continue;
                }

                // if current post should never contain optin, return false.
                if (!empty($posts_never_load) && in_array($post_id, $posts_never_load)) {
                    continue;
                }

                // if current page should never contain optin, return false.
                if (!empty($pages_never_load) && is_page($post_id) && in_array($post_id, $pages_never_load)) {
                    continue;
                }

                // if current CPT post should never contain optin, return false.
                if (!empty($cpt_never_load) && in_array($post_id, $cpt_never_load)) {
                    continue;
                }

                // if current post category contain a category that optin should load for, return true.
                // array_intersect() return array element that exist in both comparison arrays.
                if (!empty($post_categories_load)) {
                    $intersect = array_intersect($post_categories, $post_categories_load);
                    if (empty($intersect)) {
                        continue;
                    }
                }

                // if current post isn't found in a set of all cpt posts to display optin for, return false.
                if (!empty($exclusive_post_types_posts_load) && !in_array($post_id, $exclusive_post_types_posts_load)) {
                    continue;
                }

                if (!empty($post_types_load) && !in_array($post_post_type, $post_types_load)) {
                    continue;
                }

                echo OptinFormFactory::build($id);

                do_action('mailoptin_after_footer_optin_display', $id, $optin_ids, $post_id);
            }
        }
    }

    /**
     * @return FrontEndOutput
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