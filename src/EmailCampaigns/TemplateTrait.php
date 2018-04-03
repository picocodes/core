<?php

namespace MailOptin\Core\EmailCampaigns;

use MailOptin\Core\PluginSettings\Settings;

trait TemplateTrait
{
    /**
     * Replace placeholders in email template's footer description with their contact details saved values.
     *
     * @param string $content
     *
     * @return mixed
     */
    public function replace_footer_placeholder_tags($content)
    {
        $search = [
            '{{company_name}}',
            '{{company_address}}',
            '{{company_address_2}}',
            '{{company_city}}',
            '{{company_state}}',
            '{{company_zip}}',
            '{{company_country}}'
        ];

        $replace = [
            Settings::instance()->company_name(),
            Settings::instance()->company_address(),
            Settings::instance()->company_address_2(),
            Settings::instance()->company_city(),
            Settings::instance()->company_state(),
            Settings::instance()->company_zip(),
            \MailOptin\Core\country_code_to_name(Settings::instance()->company_country()),
        ];

        return str_replace($search, $replace, $content);
    }
}