<?php

namespace App\Services\Scoring\Rules;

use App\Ai\Agents\LeadScorerAgent;
use App\Models\Lead;
use App\Models\Setting;
use App\Services\Rag\RagRetriever;
use App\Services\Scoring\ScoringRuleInterface;
use Illuminate\Support\Facades\Log;

readonly class AiScoringRule implements ScoringRuleInterface
{
    public function __construct(
        private RagRetriever $retriever,
    ) {}

    public function score(Lead $lead): int
    {
        if (! $this->aiConfigured()) {
            return 0;
        }

        $leadData = $this->formatLeadData($lead);
        $contextDocs = $this->retriever->retrieve($leadData);
        $context = $contextDocs->map(fn ($doc) => $doc->content)->implode(PHP_EOL.PHP_EOL);

        $prompt = $context
            ? "The following context describes the ideal customer profile:\n\n$context\n\n---\n\nNow score this lead:\n$leadData"
            : "Score this lead:\n$leadData";

        try {
            $agent = LeadScorerAgent::make();
            $response = $agent->prompt($prompt);

            $parsed = json_decode($response->text, true);

            if (! isset($parsed['score']) || ! is_int($parsed['score'])) {
                return 0;
            }

            return max(0, min(50, $parsed['score']));
        } catch (\Exception $e) {
            Log::warning('AI scoring failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    private function aiConfigured(): bool
    {
        if (filled(config('ai.providers.openai.key'))) {
            return true;
        }

        return filled(Setting::getValue('ai_api_key'));
    }

    private function formatLeadData(Lead $lead): string
    {
        $data = [
            'name' => $lead->name,
            'email' => $lead->email ?? 'N/A',
            'phone' => $lead->phone ?? 'N/A',
            'budget' => $lead->budget ? (float) $lead->budget : null,
            'source' => $lead->source ?? 'N/A',
            'additional_data' => $lead->additional_data,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
