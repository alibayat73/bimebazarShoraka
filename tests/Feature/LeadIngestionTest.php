<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Notifications\HotLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_ingest_a_lead_and_calculate_score(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/leads', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '09121234567',
            'budget' => 60000,
            'source' => 'partner_api',
        ]);

        $response->assertStatus(201);

        $lead = Lead::first();
        $this->assertEquals('John Doe', $lead->name);

        // Let's verify score (with enhanced rules):
        // Budget (30 base + 5 source alignment) = 35 pts
        // Source (15 base + 5 contact bonus) = 20 pts
        // Data Completeness (4+3+2+2+1+3) = 15 pts
        // Email Domain (non-generic) = 15 pts
        // Iran Phone (MCI + correct length) = 11 pts
        // Total = 35 + 20 + 15 + 15 + 11 = 96
        $this->assertEquals(96, $lead->score);
        $this->assertEquals('High', $lead->priority);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            HotLeadNotification::class
        );
    }

    public function test_upsert_logic_updates_existing_lead(): void
    {
        Notification::fake();

        $lead = Lead::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'score' => 10,
            'priority' => 'Low',
        ]);

        $response = $this->postJson('/api/leads', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'budget' => 70000,
        ]);

        $response->assertStatus(200);

        $this->assertEquals(1, Lead::count());
        $lead->refresh();
        $this->assertEquals('Jane Smith', $lead->name);
        $this->assertEquals(70000, $lead->budget);
        $this->assertEquals('High', $lead->priority);
    }

    public function test_fails_if_neither_email_nor_phone_provided(): void
    {
        $response = $this->postJson('/api/leads', [
            'name' => 'Anonymous',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'phone']);
    }
}
