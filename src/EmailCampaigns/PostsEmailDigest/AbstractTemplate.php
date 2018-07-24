<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use MailOptin\Core\EmailCampaigns\AbstractTemplate as ParentAbstractTemplate;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use WP_Post;

abstract class AbstractTemplate extends ParentAbstractTemplate
{
    use TemplateTrait;

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

    public function posts_email_digest()
    {
        return get_posts([
            'numberposts' => 5
        ]);
    }

    public function parsed_post_list()
    {
        $delimiter = $this->delimiter();

        ob_start();
        $posts = $this->posts_email_digest();
        $posts_count = count($posts);
        /**
         * @var int $index
         * @var WP_Post $post
         */
        foreach ($posts as $index => $post) {

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

            if (!empty($delimiter) && $index < ($posts_count - 1)) echo $delimiter;
        }

        return ob_get_clean();
    }
}