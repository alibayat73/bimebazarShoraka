<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class AdditionalDataCompletenessRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        if (! empty($lead->additional_data)) {
            return 5;
        }

        return 0;
    }
}
