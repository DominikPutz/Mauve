<?php
/**
 * This trait provide legacy classes for campaign.
 */
namespace Hurrytimer;

trait CampaignBuilderLegacy
{
    private function legacyLabelClass()
    {
        return 'hurrytimer-cdt__label';
    }

    private function legacyDigitClass()
    {
        return 'hurrytimer-cdt__time';
    }

    private function legacyBlockClass()
    {
        return 'hurrytimer-cdt__dur';
    }

    private function legacyHeadlineClass()
    {
        return 'hurrytimer-cdt__headline';
    }

    private function legacyTimerClass()
    {
        return 'hurrytimer-cdt__inner';
    }

    private function legacySeparatorClass()
    {
        return 'hurrytimer-cdt__sep';
    }

    function legacyCampaignClass($id){
        return "hurrytimer-cdt hurrytimer-cdt--{$id}";
    }
}
