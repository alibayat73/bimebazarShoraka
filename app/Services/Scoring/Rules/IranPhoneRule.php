<?php

namespace App\Services\Scoring\Rules;

use App\Models\Lead;
use App\Services\Scoring\ScoringRuleInterface;

class IranPhoneRule implements ScoringRuleInterface
{
    private const array MCI_PREFIXES = ['0910', '0911', '0912', '0913', '0914', '0915', '0916', '0917', '0918', '0919', '0990', '0991'];

    private const array IRANCELL_PREFIXES = ['0901', '0902', '0903', '0905', '0930', '0933', '0934', '0935', '0936', '0937', '0938', '0939'];

    private const array RIGHTEL_PREFIXES = ['0920', '0921', '0922'];

    private const array LANDLINE_AREA_CODES = ['021', '031', '041', '051', '061', '071', '081', '011', '026', '023', '024', '025', '028', '034', '035', '036', '038', '044', '045', '054', '056', '058', '066', '074', '076', '077', '084', '086', '087'];

    public function score(Lead $lead): int
    {
        if (! $lead->phone) {
            return 0;
        }

        $phone = preg_replace('/[\s\-\(\)]/', '', $lead->phone);

        $score = 0;

        // Normalize: strip +98 prefix for processing
        if (str_starts_with($phone, '+98')) {
            $phone = '0'.substr($phone, 3);
            $score += 2; // International format bonus
        } elseif (str_starts_with($phone, '0098')) {
            $phone = '0'.substr($phone, 4);
            $score += 1;
        }

        // Mobile phone scoring
        foreach ([...self::MCI_PREFIXES, ...self::IRANCELL_PREFIXES, ...self::RIGHTEL_PREFIXES] as $prefix) {
            if (str_starts_with($phone, $prefix)) {
                $score += 5;

                // Correct length check (0 + 3 prefix + 7 subscriber = 11 digits)
                if (strlen($phone) === 11) {
                    $score += 3;
                }

                // Operator-specific scores for business-friendliness
                if (in_array($prefix, self::MCI_PREFIXES)) {
                    $score += 3; // MCI: widest business coverage
                } elseif (in_array($prefix, self::IRANCELL_PREFIXES)) {
                    $score += 2; // Irancell: good coverage
                }

                return $score;
            }
        }

        // Landline check
        foreach (self::LANDLINE_AREA_CODES as $code) {
            if (str_starts_with($phone, '0'.$code) || str_starts_with($phone, $code)) {
                $score += 4;

                if (strlen($phone) === 11) {
                    $score += 3;
                }

                return $score;
            }
        }

        // Valid-looking but unmatched prefix — minimal score
        if (preg_match('/^(0|\+98)?9\d{9}$/', $lead->phone)) {
            return 3;
        }

        return 0;
    }
}
