<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeadPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Notifications\HotLeadNotification;
use App\Services\Scoring\LeadScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class LeadController extends Controller
{
    public function __construct(private readonly LeadScorer $scorer) {}

    /**
     * Store or Update a lead based on email or phone.
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $data = $request->validated();

        $lead = Lead::query();
        if (! empty($data['email'])) {
            $lead->where('email', $data['email']);
        } elseif (! empty($data['phone'])) {
            $lead->where('phone', $data['phone']);
        }

        $lead = $lead->first() ?? new Lead;

        $lead->fill($data);

        $lead = $this->scorer->score($lead);

        $lead->save();

        if ($lead->priority === LeadPriority::HIGH->value) {
            Notification::route('mail', 'sales@example.com')
                ->notify(new HotLeadNotification($lead));
        }

        return response()->json([
            'message' => 'Lead processed successfully',
            'lead' => $lead,
        ], $lead->wasRecentlyCreated ? 201 : 200);
    }
}
