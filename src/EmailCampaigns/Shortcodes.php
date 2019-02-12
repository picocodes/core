<?php

namespace MailOptin\Core\EmailCampaigns;

class Shortcodes
{
    /** @var \WP_Post */
    protected $wp_post_obj;

    protected $post_content_length;

    protected $post_id;

    /**
     * Shortcodes constructor.
     *
     * @param int|\WP_Post $post
     */
    public function __construct($post, $post_content_length)
    {
        if ($post instanceof \stdClass) {
            $this->wp_post_obj = $post;
        } else {
            $this->wp_post_obj = get_post($post);
        }

        $this->post_content_length = $post_content_length;
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
        add_shortcode('post-title', [$this, 'post_title']);
        add_shortcode('post-content', [$this, 'post_content']);

        add_shortcode('unsubscribe', [$this, 'unsubscribe']);
        add_shortcode('webversion', [$this, 'webversion']);

        do_action('mo_define_email_automation_shortcodes', $this->wp_post_obj);
    }

    public function post_title()
    {
        return $this->wp_post_obj->post_title;
    }

    public function post_content()
    {
        $content = $this->wp_post_obj->post_content;

        if (0 !== $this->post_content_length) {
            $content = \MailOptin\Core\limit_text($content, $this->post_content_length);
        }

        return wpautop($content);
    }

    public function webversion()
    {
        return '{{webversion}}';
    }

    public function unsubscribe()
    {
        return '{{unsubscribe}}';
    }
}