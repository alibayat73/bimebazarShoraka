<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class SourceRule implements ScoringRuleInterface
{
    public function score(Lead $lead): int
    {
        return match ($lead->source) {
            'partner_api' => 15,
            'web' => 5,
            default => 0,
        };
    }
}
