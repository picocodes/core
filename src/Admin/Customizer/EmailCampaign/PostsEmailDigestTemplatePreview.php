<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;


use MailOptin\Core\EmailCampaigns\PostsEmailDigest\Templatify;
use MailOptin\Core\Repositories\EmailCampaignRepository;

class PostsEmailDigestTemplatePreview extends Templatify
{
    public function __construct($email_campaign_id, array $posts = [])
    {
        parent::__construct($email_campaign_id, $this->post_collection($email_campaign_id));
    }

    public function post_collection($email_campaign_id)
    {
        $item_count = EmailCampaignRepository::get_merged_customizer_value($email_campaign_id, 'item_number');

        $parameters = [
            'posts_per_page' => $item_count,
            'post_status' => 'publish',
            'post_type' => 'post',
            'order' => 'DESC',
            'orderby' => 'post_date'
        ];

        return get_posts($parameters);
    }
}