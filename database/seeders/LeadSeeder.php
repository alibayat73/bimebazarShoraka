<?php

namespace Database\Seeders;

use App\Enums\LeadPriority;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\HotLeadNotification;
use App\Services\Scoring\LeadScorer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(LeadScorer $scorer): void
    {
        $leads = Lead::factory(50)->make();
        $admin = User::first(); // Send notifications to the admin user

        foreach ($leads as $lead) {
            // Re-roll to ensure at least one contact method exists to bypass validation logic conceptually
            if (! $lead->email && ! $lead->phone) {
                $lead->email = fake()->unique()->safeEmail();
            }

            // Calculate realistic score
            $lead = $scorer->score($lead);
            $lead->save();

            // Fire notification if High priority (simulating the controller logic)
            if ($lead->priority === LeadPriority::HIGH->value && $admin) {
                $admin->notify(new HotLeadNotification($lead));
            }
        }
    }
}
