<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;
use Illuminate\Support\Str;

class EmailDomainRule implements ScoringRuleInterface
{
    private array $genericDomains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com',
    ];

    public function score(Lead $lead): int
    {
        if (! $lead->email) {
            return 0;
        }

        $parts = explode('@', $lead->email);
        if (count($parts) !== 2) {
            return 0;
        }

        $domain = Str::lower($parts[1]);

        if (! in_array($domain, $this->genericDomains)) {
            return 15;
        }

        return 0;
    }
}
