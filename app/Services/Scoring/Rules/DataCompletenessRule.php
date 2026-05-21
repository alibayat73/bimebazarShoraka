<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class DataCompletenessRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        $score = 0;

        // Weighted scoring per field
        if ($lead->email) {
            $score += 4;
        }

        if ($lead->phone) {
            $score += 3;
        }

        if ($lead->name && str_contains(trim($lead->name), ' ')) {
            // Full name (first + last) indicates a real person
            $score += 2;
        }

        if ($lead->budget) {
            $score += 2;
        }

        if ($lead->source) {
            $score += 1;
        }

        // Contradiction penalty: source is partner_api but no contact info
        if ($lead->source === 'partner_api' && ! $lead->email && ! $lead->phone) {
            $score -= 5;
        }

        return max(0, $score);
    }
}
