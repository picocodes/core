<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use MailOptin\Core\EmailCampaigns\AbstractTemplate as ParentAbstractTemplate;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use WP_Post;

abstract class AbstractTemplate extends ParentAbstractTemplate
{
    use TemplateTrait;

    public $posts;

    public function __construct($email_campaign_id, $posts)
    {
        $this->posts = $posts;

        parent::__construct($email_campaign_id);
    }

    /**
     * HTML structure for single post item
     *
     * @return mixed
     */
    abstract function single_post_item();

    /**
     * Eg a Divider
     *
     * @return mixed
     */
    abstract function delimiter();

    public function parsed_post_list()
    {
        $delimiter = $this->delimiter();

        ob_start();
        $posts_count = count($this->posts);
        /**
         * @var int $index
         * @var WP_Post $post
         */
        foreach ($this->posts as $index => $post) {
            // index starts at 0. so we increment by one.
            $index++;

            $search = array(
                '{{post.title}}',
                '{{post.content}}',
                '{{post.feature.image}}',
                '{{post.url}}',
            );

            $replace = array(
                $this->post_title($post),
                $this->post_content($post),
                $this->feature_image($post),
                $this->post_url($post),
            );

            echo str_replace($search, $replace, $this->single_post_item());

            if (!empty($delimiter) && ($index % $posts_count) > 0) echo $delimiter;
        }

        return ob_get_clean();
    }
}