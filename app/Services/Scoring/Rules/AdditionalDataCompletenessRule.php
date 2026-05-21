<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class AdditionalDataCompletenessRule implements ScoringRuleInterface
{
    private array $keyWeights = [
        'job_title' => 3,
        'company_size' => 2,
        'industry' => 2,
        'website' => 1,
    ];

    public function score(Lead $lead): int
    {
        if (empty($lead->additional_data)) {
            return 0;
        }

        $data = $lead->additional_data;
        $score = 0;
        $matchedKeys = 0;

        foreach ($this->keyWeights as $key => $weight) {
            $value = data_get($data, $key);
            if ($this->isValueMeaningful($value)) {
                $score += $weight;
                $matchedKeys++;
            }
        }

        // Bonus for having multiple meaningful keys
        if ($matchedKeys >= 3) {
            $score += 3;
        } elseif ($matchedKeys >= 2) {
            $score += 1;
        }

        // Penalty: data exists but is mostly empty / meaningless
        if ($matchedKeys === 0) {
            return 1; // Minimal score — data key is present but empty
        }

        return $score;
    }

    private function isValueMeaningful(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '-' || $trimmed === 'N/A' || $trimmed === 'none') {
                return false;
            }

            // URL validation for website field
            if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return true;
            }

            return strlen($trimmed) >= 2;
        }

        if (is_int($value) || is_float($value)) {
            return $value > 0;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return true;
    }
}
