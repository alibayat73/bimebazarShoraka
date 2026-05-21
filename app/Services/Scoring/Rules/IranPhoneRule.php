<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class IranPhoneRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        if (! $lead->phone) {
            return 0;
        }

        // Extremely basic Iranian phone validation
        // Matches 0912... or +98912...
        if (preg_match('/^(0|\+98)?9\d{9}$/', $lead->phone)) {
            return 10;
        }

        return 0;
    }
}
