<?php

namespace MailOptin\Core\EmailCampaigns;

class Shortcodes
{
    /** @var \WP_Post */
    protected $post;

    protected $post_id;

    protected $old_shortcode_tags;

    public function __construct($post_id)
    {
        $this->post_id = $post_id;
        $this->post    = get_post($post_id);
    }

    public function init()
    {
        global $shortcode_tags;

        $this->old_shortcode_tags = $shortcode_tags;

        $shortcode_tags = array();

        add_shortcode('post_title', [$this, 'post_title']);

        do_action('mo_email_automation_shortcodes', $this->post_id, $this->post);
    }

    public function shut_down()
    {
        global $shortcode_tags;
        $shortcode_tags = $this->old_shortcode_tags;
    }

    public function post_title()
    {
        return $this->post->post_title;
    }
}