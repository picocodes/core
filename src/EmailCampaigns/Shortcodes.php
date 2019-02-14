<?php

namespace MailOptin\Core\EmailCampaigns;

class Shortcodes
{
    use TemplateTrait;

    /** @var \WP_Post */
    protected $wp_post_obj;

    protected $email_campaign_id;

    protected $post_id;

    /**
     * Shortcodes constructor.
     *
     * @param int|\WP_Post $post
     */
    public function __construct($post, $email_campaign_id)
    {
        if ($post instanceof \stdClass) {
            $this->wp_post_obj = $post;
        } else {
            $this->wp_post_obj = get_post($post);
        }

        $this->email_campaign_id = $email_campaign_id;
    }

    public function parse($content)
    {
        global $shortcode_tags;
        $old_shortcode_tags = $shortcode_tags;
        $shortcode_tags     = array();

        $this->define_shortcodes();

        $parsed_content = do_shortcode($content);

        // restore back old shortcode
        $shortcode_tags = $old_shortcode_tags;

        return $parsed_content;
    }

    public function define_shortcodes()
    {
        add_shortcode('post-title', [$this, 'post_title_tag']);
        add_shortcode('post-content', [$this, 'post_content_tag']);
        add_shortcode('post-excerpt', [$this, 'post_excerpt_tag']);
        add_shortcode('post-feature-image', [$this, 'post_feature_image_tag']);
        add_shortcode('post-feature-image-url', [$this, 'post_feature_image_url_tag']);
        add_shortcode('post-url', [$this, 'post_url_tag']);
        add_shortcode('post-date', [$this, 'post_date_tag']);
        add_shortcode('post-date-gmt', [$this, 'post_date_gmt_tag']);
        add_shortcode('post-category', [$this, 'post_category_tag']);
        add_shortcode('post-id', [$this, 'post_id_tag']);
        add_shortcode('post-author-name', [$this, 'post_author_name_tag']);
        add_shortcode('post-author-website', [$this, 'post_author_website_tag']);
        add_shortcode('post-author-email', [$this, 'post_author_email_tag']);
        add_shortcode('post-meta', [$this, 'post_meta_tag']);

        add_shortcode('unsubscribe', [$this, 'unsubscribe']);
        add_shortcode('webversion', [$this, 'webversion']);
        add_shortcode('company-name', [$this, 'company_name']);

        do_action('mo_define_email_automation_shortcodes', $this->wp_post_obj);
    }

    public function post_title_tag()
    {
        return $this->wp_post_obj->post_title;
    }

    public function post_feature_image_tag()
    {
        return sprintf(
            '<img class="mo-post-feature-image" src="%s" alt="%s">',
            $this->feature_image($this->wp_post_obj->ID),
            $this->wp_post_obj->post_title
        );
    }

    public function post_feature_image_url_tag()
    {
        return $this->feature_image($this->wp_post_obj->ID);
    }

    public function post_url_tag()
    {
        return $this->post_url($this->wp_post_obj);
    }

    public function post_date_tag()
    {
        return $this->wp_post_obj->post_date;
    }

    public function post_date_gmt_tag()
    {
        return $this->wp_post_obj->post_date_gmt;
    }

    public function post_content_tag()
    {
        return $this->post_content($this->wp_post_obj);
    }

    public function post_excerpt_tag()
    {
        return wpautop($this->wp_post_obj->post_excerpt);
    }

    public function webversion()
    {
        return '{{webversion}}';
    }

    public function unsubscribe()
    {
        return '{{unsubscribe}}';
    }

    public function company_name()
    {
        return '{{company_name}}';
    }

    public function post_category_tag($atts)
    {
        $atts = shortcode_atts(['link' => 'true'], $atts);

        $output = '';

        $categories = get_the_term_list($this->wp_post_obj->ID, 'category', '', ', ');

        if ( ! is_wp_error($categories)) {
            $output = $atts['link'] == 'true' ? $categories : strip_tags($categories);
        }

        return $output;
    }

    public function post_id_tag()
    {
        return $this->wp_post_obj->ID;
    }

    public function post_author_name_tag($atts)
    {
        $atts    = shortcode_atts(['link' => 'true'], $atts);
        $wp_user = get_user_by('id', $this->wp_post_obj->post_author);

        $output = $wp_user->display_name;

        if ($atts['link'] == 'true') {
            $output = '<a href="' . $wp_user->user_url . '">' . $wp_user->display_name . '</a>';
        }

        return $output;
    }

    public function post_author_website_tag()
    {
        return get_user_by('id', $this->wp_post_obj->post_author)->user_url;
    }

    public function post_author_email_tag()
    {
        return get_user_by('id', $this->wp_post_obj->post_author)->user_email;
    }

    public function post_meta_tag($atts)
    {
        $atts = shortcode_atts(['key' => ''], $atts);

        $key = sanitize_key($atts['key']);

        return get_post_meta($this->wp_post_obj->ID, $key, true);
    }
}