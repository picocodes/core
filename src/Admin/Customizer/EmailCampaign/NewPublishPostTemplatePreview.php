<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;


use MailOptin\Core\EmailCampaigns\NewPublishPost\Templatify;

class NewPublishPostTemplatePreview extends Templatify
{
    public function __construct($email_campaign_id)
    {
        parent::__construct($email_campaign_id, $this->postMockObject());
    }

    public function postMockObject()
    {
        $mock_post             = new \stdClass();
        $mock_post->ID = false;
        $mock_post->post_title = SolitaryDummyContent::title();
        $mock_post->post_content = SolitaryDummyContent::content();
        $mock_post->post_url = '#';

        return $mock_post;
    }
}