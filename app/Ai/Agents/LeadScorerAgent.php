<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class LeadScorerAgent implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
You are a lead scoring AI for an insurance marketplace. Your job is to evaluate lead data and
the provided context documents, then return a numeric score between 0 and 50 representing
how well the lead matches the ideal customer profile.

Scoring Guidelines:
- Read the context documents carefully. They describe the ideal criteria for high-value leads.
- Consider factors like: budget amount, lead source, email domain quality, data completeness,
  phone number validity, and any additional data provided.
- Assign higher scores to leads that match multiple criteria from the context.
- A score of 0 means the lead does not match any criteria.
- A score of 50 means the lead perfectly matches all ideal criteria.

You must return your response as a JSON object with the exact structure defined in your schema.
PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'score' => $schema->integer()->required(),
        ];
    }
}
