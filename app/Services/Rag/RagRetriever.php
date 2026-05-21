<?php

namespace App\Services\Rag;

use App\Models\RagDocument;
use Illuminate\Support\Collection;
use Laravel\Ai\Embeddings;

class RagRetriever
{
    private int $topK;

    public function __construct(int $topK = 3)
    {
        $this->topK = $topK;
    }

    /**
     * Retrieve top-K relevant documents for the given query string.
     *
     * @return Collection<RagDocument>
     */
    public function retrieve(string $query): Collection
    {
        $queryEmbedding = $this->generateQueryEmbedding($query);

        if ($queryEmbedding !== null) {
            return $this->retrieveByEmbedding($queryEmbedding);
        }

        return $this->retrieveByKeywordFallback($query);
    }

    private function generateQueryEmbedding(string $query): ?array
    {
        try {
            $response = Embeddings::for([$query])->generate();
            $embeddings = $response->embeddings;

            return $embeddings[0] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function retrieveByEmbedding(array $queryEmbedding): Collection
    {
        $documents = RagDocument::query()->whereNotNull('embedding')->get();

        $scored = $documents->map(function (RagDocument $doc) use ($queryEmbedding) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $doc->embedding);

            return [
                'document' => $doc,
                'similarity' => $similarity,
            ];
        });

        return $scored
            ->sortByDesc('similarity')
            ->take($this->topK)
            ->pluck('document');
    }

    private function retrieveByKeywordFallback(string $query): Collection
    {
        $keywords = array_filter(explode(' ', strtolower($query)));

        return RagDocument::all()
            ->sortByDesc(function (RagDocument $doc) use ($keywords) {
                $content = strtolower($doc->content);

                return collect($keywords)->sum(fn ($word) => substr_count($content, $word));
            })
            ->take($this->topK)
            ->values();
    }

    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($vectorA as $i => $valueA) {
            $valueB = $vectorB[$i] ?? 0;
            $dotProduct += $valueA * $valueB;
            $normA += $valueA * $valueA;
            $normB += $valueB * $valueB;
        }

        $denominator = sqrt($normA) * sqrt($normB);

        if ($denominator === 0.0) {
            return 0.0;
        }

        return $dotProduct / $denominator;
    }
}
