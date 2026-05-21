<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;
use Illuminate\Support\Str;

class EmailDomainRule implements ScoringRuleInterface
{
    private array $genericDomains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'aol.com',
        'live.com', 'msn.com', 'icloud.com', 'mail.com', 'inbox.com',
    ];

    private array $premiumDomains = [
        'protonmail.com', 'proton.me', 'fastmail.com', 'tutanota.com',
        'hey.com', 'mailbox.org',
    ];

    private array $educationalDomains = [
        '.edu', 'ac.ir',
    ];

    private array $governmentDomains = [
        '.gov', '.mil', 'org.ir',
    ];

    private array $disposableDomains = [
        'mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com',
        'yopmail.com', 'throwaway.email', 'sharklasers.com', 'trashmail.com',
        'mailnesia.com', 'temp-mail.org',
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

        // Disposable domains — negative score (penalize)
        if (in_array($domain, $this->disposableDomains)) {
            return -5;
        }

        // Educational or government domains — highest score
        if (array_any([...$this->educationalDomains, ...$this->governmentDomains], fn ($suffix) => str_ends_with($domain, $suffix))) {
            return 20;
        }

        // Known corporate / non-generic domains — high score
        if (! in_array($domain, $this->genericDomains) && ! in_array($domain, $this->premiumDomains)) {
            return 15;
        }

        // Premium privacy-focused domains — medium score
        if (in_array($domain, $this->premiumDomains)) {
            return 5;
        }

        return 0;
    }
}
