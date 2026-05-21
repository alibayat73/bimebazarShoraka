<?php

namespace App\Livewire;

use App\Http\Controllers\Api\LeadController;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use Faker\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class LeadDashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $priorityFilter = '';

    public string $newLeadName = '';

    public string $newLeadEmail = '';

    public string $newLeadPhone = '';

    public string $newLeadBudget = '';

    protected $queryString = ['search', 'priorityFilter'];

    /**
     * Simulate a new lead via the API logic directly here for demo purposes
     */
    public function simulateLead(): void
    {
        $faker = Factory::create();

        $request = new Request([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'phone' => '0912'.$faker->randomNumber(7, true), // Iran phone rule
            'budget' => $faker->randomElement([5000, 15000, 60000]),
            'source' => $faker->randomElement(['web', 'partner_api']),
        ]);

        $controller = app(LeadController::class);
        $storeRequest = StoreLeadRequest::createFrom($request);
        $storeRequest->setValidator(
            Validator::make($storeRequest->all(), (new StoreLeadRequest)->rules())
        );
        $controller->store($storeRequest);

        $this->resetPage();
    }

    public function render(): \Illuminate\Contracts\View\Factory|View|\Illuminate\View\View
    {
        $query = Lead::query()->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        return view('livewire.lead-dashboard', [
            'leads' => $query->paginate(10),
            'totalLeads' => Lead::query()->count(),
            'highPriorityLeads' => Lead::query()->where('priority', 'High')->count(),
            'avgBudget' => Lead::query()->avg('budget') ?? 0,
        ]);
    }
}
