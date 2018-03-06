<?php

namespace MailOptin\Core\OptinForms;

use MailOptin\Core\Repositories\OptinCampaignsRepository as OCR;

trait UserTargetingRuleTrait
{
    /**
     * Determine if the optin passes user targeting rules.
     *
     * @param int $id
     * @return bool
     */
    public function user_targeting_rule_checker($id)
    {
        switch (OCR::get_customizer_value($id, 'who_see_optin')) {
            case 'show_logged_in':
                if (!is_user_logged_in()) return false;
                break;
            case 'show_non_logged_in':
                if (is_user_logged_in()) return false;
                break;
            case 'show_all':
                return true;
                break;
        }

        return true;
    }
}