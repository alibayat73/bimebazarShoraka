<?php

namespace App\Services\Scoring;

use App\Models\Lead;

interface ScoringRuleInterface
{
    /**
     * Calculate and return the score for the given lead.
     */
    public function score(Lead $lead): int;
}
