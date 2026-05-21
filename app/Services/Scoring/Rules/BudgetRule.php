<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class BudgetRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        if (! $lead->budget) {
            return 0;
        }

        if ($lead->budget >= 50000) {
            return 30;
        }

        if ($lead->budget >= 10000) {
            return 15;
        }

        return 0;
    }
}
