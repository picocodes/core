<?php

namespace MailOptin\Core\Admin\Customizer\OptinForm;

use MailOptin\Core\OptinForms\AbstractOptinForm;
use MailOptin\Core\Repositories\OptinCampaignsRepository;
use MailOptin\Core\OptinForms\OptinFormInterface;

/**
 * Create a optin form class instance from its ID or class name.
 *
 * @package MailOptin\Admin\Customizer\OptinForm\
 */
class OptinFormFactory
{
    /**
     * @param int $optin_campaign_id
     * @param null|\WP_Customize_Manager $wp_customize
     *
     * @return false|AbstractOptinForm
     */
    public static function make($optin_campaign_id, $wp_customize = null)
    {
        $db_optin_class = OptinCampaignsRepository::get_optin_campaign_class($optin_campaign_id);
        $optin_type = ucfirst(OptinCampaignsRepository::get_optin_campaign_type($optin_campaign_id));

        // first $db_optin_class is the template class namespace.
        $optin_class = apply_filters(
            'mailoptin_register_optin_class',
            "\\MailOptin\\Core\\OptinForms\\$optin_type\\$db_optin_class",
            $optin_campaign_id,
            $db_optin_class,
            $optin_type
        );

        if (!class_exists($optin_class)) {
            return false;
        }

        return new $optin_class($optin_campaign_id, $wp_customize);
    }

}