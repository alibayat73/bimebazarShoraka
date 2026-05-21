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

        $score = 0;

        $budget = (float) $lead->budget;

        if ($budget >= 200000) {
            $score += 40;
        } elseif ($budget >= 100000) {
            $score += 35;
        } elseif ($budget >= 50000) {
            $score += 30;
        } elseif ($budget >= 25000) {
            $score += 20;
        } elseif ($budget >= 10000) {
            $score += 15;
        } elseif ($budget >= 5000) {
            $score += 10;
        } elseif ($budget >= 1000) {
            $score += 5;
        }

        // Precision bonus: non-round budgets indicate research and serious intent
        if (fmod($budget, 1000) !== 0.0) {
            $score += 5;
        }

        // Source aligns with budget: partner_api leads with high budget show qualified intent
        if ($lead->source === 'partner_api' && $budget >= 50000) {
            $score += 5;
        }

        return $score;
    }
}
