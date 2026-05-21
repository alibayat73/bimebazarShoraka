<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Models\RagDocument;
use App\Services\Rag\RagRetriever;
use App\Services\Scoring\Rules\AiScoringRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiScoringRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_zero_when_no_api_key_configured(): void
    {
        // Ensure no API key
        config(['ai.providers.openai.key' => '']);

        $lead = Lead::factory()->make([
            'budget' => 100000,
            'source' => 'partner_api',
        ]);

        $rule = new AiScoringRule(new RagRetriever);
        $score = $rule->score($lead);

        $this->assertEquals(0, $score);
    }

    public function test_keyword_fallback_retrieval_works(): void
    {
        RagDocument::create([
            'title' => 'High Budget Criteria',
            'content' => 'Leads with budgets over $50,000 are high value.',
        ]);

        RagDocument::create([
            'title' => 'Low Budget Criteria',
            'content' => 'Leads under $5,000 are low value.',
        ]);

        $retriever = new RagRetriever(topK: 2);
        $results = $retriever->retrieve('high budget');

        $this->assertCount(2, $results);
        $this->assertEquals('High Budget Criteria', $results->first()->title);
    }
}
