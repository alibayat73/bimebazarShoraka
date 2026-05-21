<?php

namespace App\Livewire;

use App\Models\RagDocument;
use Laravel\Ai\Embeddings;
use Livewire\Component;
use Livewire\WithPagination;

class CriteriaDocuments extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $title = '';

    public string $content = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    public function addDocument(): void
    {
        $this->validate();

        $doc = RagDocument::create([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        try {
            $response = Embeddings::for([$doc->content])->generate();
            $embeddings = $response->embeddings;

            if (! empty($embeddings)) {
                $doc->update(['embedding' => $embeddings[0]]);
            }
        } catch (\Exception $e) {
            // Embedding generation not available; skip
        }

        $this->reset(['title', 'content', 'showForm']);
        $this->resetPage();
    }

    public function deleteDocument(int $id): void
    {
        RagDocument::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.criteria-documents', [
            'documents' => RagDocument::latest()->paginate(10),
        ]);
    }
}
