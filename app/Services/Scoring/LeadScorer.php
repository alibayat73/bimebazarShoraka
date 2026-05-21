<?php

namespace App\Services\Scoring;

use App\Enums\LeadPriority;
use App\Models\Lead;

class LeadScorer
{
    /**
     * @var ScoringRuleInterface[]
     */
    private array $rules;

    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function addRule(ScoringRuleInterface $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    public function score(Lead $lead): Lead
    {
        $totalScore = 0;

        foreach ($this->rules as $rule) {
            $totalScore += $rule->score($lead);
        }

        $lead->score = $totalScore;
        $lead->priority = $this->determinePriority($totalScore)->value;

        return $lead;
    }

    private function determinePriority(int $score): LeadPriority
    {
        if ($score >= 35) {
            return LeadPriority::HIGH;
        }

        if ($score >= 15) {
            return LeadPriority::MEDIUM;
        }

        return LeadPriority::LOW;
    }
}
