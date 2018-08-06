<?php

namespace MailOptin\Core\Admin\Customizer\EmailCampaign;


use MailOptin\Core\EmailCampaigns\NewPublishPost\Templatify;

class NewPublishPostTemplatePreview extends Templatify
{
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