<?php

namespace Database\Seeders;

use App\Models\RagDocument;
use Illuminate\Database\Seeder;
use Laravel\Ai\Embeddings;

class RagDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'title' => 'High Budget Enterprise Criteria',
                'content' => 'Leads with budgets exceeding $50,000 are considered high-value enterprise prospects. These leads typically represent organizations with significant purchasing power and longer sales cycles. They require personalized follow-up and dedicated account management. Priority should be given to leads with budgets over $100,000 as they indicate strategic investment capacity.',
            ],
            [
                'title' => 'Partner API Leads',
                'content' => 'Leads originating from partner API integrations are pre-qualified and have higher conversion rates. Partners have already vetted these leads, so they represent warmer opportunities. These leads should be prioritized as they come with an implicit endorsement from trusted partners. Partner API leads typically have more complete data and clearer intent signals.',
            ],
            [
                'title' => 'Corporate Email Domain Value',
                'content' => 'Leads using corporate or company email domains (non-Gmail, non-Yahoo, non-Hotmail, non-Outlook) indicate employment at an organization. This strongly correlates with B2B intent and purchasing authority. Corporate email domains suggest the lead is acting on behalf of a business rather than as an individual consumer, which typically indicates higher lifetime value.',
            ],
            [
                'title' => 'Complete Contact Information Indicator',
                'content' => 'Leads that provide both email and phone contact information demonstrate higher intent and engagement. Providing multiple contact channels indicates the lead is genuinely interested and willing to be reached. These leads convert at significantly higher rates and should be scored accordingly. Complete data also enables more effective follow-up strategies.',
            ],
            [
                'title' => 'Iranian Phone Number Validation',
                'content' => 'Leads with valid Iranian mobile phone numbers (starting with 09 or +989) represent the primary target market. Valid Iranian phone numbers confirm the lead is reachable within the local market. Mobile numbers are preferred over landlines as they indicate the lead is accessible via messaging apps and mobile channels, which are the preferred communication methods in the region.',
            ],
            [
                'title' => 'Additional Data Completeness',
                'content' => 'Leads that provide additional metadata such as company size, job title, or industry sector demonstrate higher engagement and transparency. Supplementary information enables better lead qualification, personalized communication, and more accurate scoring. Leads with rich additional data profiles are typically more serious about making purchasing decisions.',
            ],
            [
                'title' => 'Budget Range Scoring Guidelines',
                'content' => 'Budget is a primary indicator of lead quality. Budgets between $10,000 and $49,999 indicate mid-market potential. Budgets of $50,000 or more indicate enterprise-grade opportunities. Higher budgets correlate with faster decision-making and larger deal sizes. Budget information should be weighted heavily in overall lead scoring calculations.',
            ],
            [
                'title' => 'Lead Source Quality Hierarchy',
                'content' => 'Lead sources have varying quality levels. Partner API integrations produce the highest quality leads. Web forms are medium quality. CSV imports and manual entries are lower quality and require additional verification. The source of a lead significantly impacts conversion probability and should factor into lead prioritization decisions.',
            ],
        ];

        foreach ($documents as $data) {
            $doc = RagDocument::query()->create($data);

            try {
                $response = Embeddings::for([$doc->content])->generate();
                $embeddings = $response->embeddings;

                if (! empty($embeddings)) {
                    $doc->update(['embedding' => $embeddings[0]]);
                }
            } catch (\Exception $e) {
                // API key may not be configured; skip embedding generation
                // Scoring will fall back to text similarity or keyword matching
            }
        }
    }
}
