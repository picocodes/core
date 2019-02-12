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

        return $mock_post;
    }

    public function post_title()
    {
        return SolitaryDummyContent::title();
    }

    public function post_content()
    {
        return SolitaryDummyContent::content();
    }

    public function post_url()
    {
        return '#';
    }

    public function feature_image($post_id)
    {
        // we need the default image shown, so we are gonna supply invalid post ID
        return parent::feature_image('');
    }
}