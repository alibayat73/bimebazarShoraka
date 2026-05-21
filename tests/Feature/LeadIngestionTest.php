<?php

namespace Tests\Feature;

use App\Enums\LeadPriority;
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

        $lead = Lead::query()->first();
        $this->assertEquals('John Doe', $lead->name);

        $this->assertEquals(93, $lead->score);
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
            'priority' => LeadPriority::LOW->value,
        ]);

        $response = $this->postJson('/api/leads', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'budget' => 70000,
        ]);

        $response->assertStatus(200);

        $this->assertEquals(1, Lead::query()->count());
        $lead->refresh();
        $this->assertEquals('Jane Smith', $lead->name);
        $this->assertEquals(70000, $lead->budget);
        $this->assertEquals(LeadPriority::HIGH->value, $lead->priority);
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
