<?php

namespace MailOptin\Core\EmailCampaigns\PostsEmailDigest;

use MailOptin\Core\EmailCampaigns\AbstractTemplate as ParentAbstractTemplate;
use MailOptin\Core\EmailCampaigns\TemplateTrait;
use MailOptin\Core\Repositories\EmailCampaignMeta;
use MailOptin\Core\Repositories\EmailCampaignRepository;
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
        $item_count = EmailCampaignRepository::get_merged_customizer_value($this->email_campaign_id, 'item_number');

        $newer_than_timestamp = EmailCampaignMeta::get_meta_data($this->email_campaign_id, 'created_at', true);

        $last_sent_timestamp = EmailCampaignMeta::get_meta_data($this->email_campaign_id, 'last_sent', true);

        if (!empty($last_sent_timestamp)) {
            $newer_than_timestamp = $last_sent_timestamp;
        }

        $parameters = [
            'posts_per_page' => $item_count,
            'post_status' => 'publish',
            'post_type' => 'post',
            'order' => 'DESC',
            'orderby' => 'post_date'
        ];

        if (!is_customize_preview()) {
            $parameters['date_query'] = array(
                array(
                    'column' => 'post_date',
                    'after' => $newer_than_timestamp
                )
            );
        }

        return get_posts(apply_filters('mo_post_digest_get_posts_args', $parameters));
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