<?php

namespace MailOptin\Core\EmailCampaigns;

class Shortcodes
{
    /** @var \WP_Post */
    protected $post;

    protected $post_id;

    public function __construct($post_id)
    {
        $this->post_id = $post_id;
        $this->post    = get_post($post_id);

        add_shortcode('post_title', [$this, 'post_title']);
    }

    public function post_title()
    {
        return $this->post->post_title;
    }
}