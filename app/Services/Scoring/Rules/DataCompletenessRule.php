<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class DataCompletenessRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        if ($lead->email && $lead->phone) {
            return 10;
        }

        return 0;
    }
}
