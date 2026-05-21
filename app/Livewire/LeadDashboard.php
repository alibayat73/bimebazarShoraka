<?php

namespace App\Livewire;

use App\Http\Controllers\Api\LeadController;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
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

    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $budget = '';

    protected $queryString = ['search', 'priorityFilter'];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function generateLead(): void
    {
        $this->validate();

        $request = new Request([
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'budget' => $this->budget ? (float) $this->budget : null,
            'source' => 'manual',
        ]);

        $controller = app(LeadController::class);
        $storeRequest = StoreLeadRequest::createFrom($request);
        $storeRequest->setValidator(
            Validator::make($storeRequest->all(), (new StoreLeadRequest)->rules())
        );
        $controller->store($storeRequest);

        $this->reset(['name', 'email', 'phone', 'budget', 'showForm']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Lead::query()->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%$this->search%")
                    ->orWhere('email', 'like', "%$this->search%");
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
