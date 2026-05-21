<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class SourceRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        $score = match ($lead->source) {
            'partner_api' => 15,
            'web' => 5,
            'manual' => 3,
            'csv' => 2,
            default => 0,
        };

        // Bonus: confirmed contact info from a high-quality source
        if ($lead->source === 'partner_api' && $lead->email && $lead->phone) {
            $score += 5;
        }

        // Freshness bonus: leads ingested within the last hour get a bump
        if ($lead->created_at && $lead->created_at->diffInHours(now()) < 1) {
            $score += 3;
        }

        return $score;
    }
}
