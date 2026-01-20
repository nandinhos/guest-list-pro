<?php

namespace App\Services;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Serviço de busca de convidados com suporte a:
 * - Busca normalizada (sem acentos)
 * - Busca por similaridade (fuzzy search)
 * - Busca por documento normalizado
 *
 * Algoritmo escolhido: Combinação de LIKE com wildcards + Levenshtein em PHP
 * Motivo: Compatível com MySQL sem extensões, bom equilíbrio entre performance e precisão
 */
class GuestSearchService
{
    /**
     * Threshold mínimo de similaridade (0.0 a 1.0)
     * Valores abaixo disso são considerados não-match
     */
    private const SIMILARITY_THRESHOLD = 0.6;

    /**
     * Normaliza uma string removendo acentos e convertendo para minúsculas.
     */
    public function normalize(string $value): string
    {
        return mb_strtolower(Str::ascii(trim($value)));
    }

    /**
     * Normaliza um documento removendo caracteres não alfanuméricos.
     */
    public function normalizeDocument(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }

    /**
     * Aplica busca normalizada (exata, ignorando acentos) ao query builder.
     */
    public function applyNormalizedSearch(Builder $query, string $search): Builder
    {
        $normalizedSearch = $this->normalize($search);
        $normalizedDocument = $this->normalizeDocument($search);

        return $query->where(function (Builder $q) use ($normalizedSearch, $normalizedDocument) {
            $q->where('name_normalized', 'like', "%{$normalizedSearch}%")
                ->orWhere('document_normalized', 'like', "%{$normalizedDocument}%");
        });
    }

    /**
     * Aplica busca fuzzy (similaridade) ao query builder.
     * Busca registros que contenham partes do termo de busca.
     */
    public function applyFuzzySearch(Builder $query, string $search): Builder
    {
        $normalizedSearch = $this->normalize($search);
        $searchTerms = explode(' ', $normalizedSearch);

        return $query->where(function (Builder $q) use ($searchTerms, $normalizedSearch) {
            // Busca cada termo individualmente
            foreach ($searchTerms as $term) {
                if (strlen($term) >= 2) {
                    $q->orWhere('name_normalized', 'like', "%{$term}%");
                }
            }

            // Também busca o documento normalizado
            $normalizedDocument = $this->normalizeDocument($normalizedSearch);
            if (strlen($normalizedDocument) >= 3) {
                $q->orWhere('document_normalized', 'like', "%{$normalizedDocument}%");
            }
        });
    }

    /**
     * Busca convidados por nome com suporte a fuzzy search.
     *
     * @return Collection<int, array{guest: Guest, similarity: float, match_type: string}>
     */
    public function searchByName(string $query, int $eventId, bool $fuzzy = false): Collection
    {
        $normalizedQuery = $this->normalize($query);

        $guests = Guest::query()
            ->where('event_id', $eventId)
            ->when($fuzzy, function (Builder $q) use ($query) {
                return $this->applyFuzzySearch($q, $query);
            }, function (Builder $q) use ($query) {
                return $this->applyNormalizedSearch($q, $query);
            })
            ->with(['sector', 'promoter'])
            ->get();

        // Calcula similaridade para cada resultado
        return $guests->map(function (Guest $guest) use ($normalizedQuery) {
            $similarity = $this->calculateSimilarity($normalizedQuery, $guest->name_normalized ?? '');
            $matchType = $similarity >= 0.95 ? 'exact' : ($similarity >= 0.7 ? 'high' : 'partial');

            return [
                'guest' => $guest,
                'similarity' => $similarity,
                'match_type' => $matchType,
            ];
        })
            ->filter(fn (array $item) => $item['similarity'] >= self::SIMILARITY_THRESHOLD)
            ->sortByDesc('similarity')
            ->values();
    }

    /**
     * Busca convidados por documento.
     */
    public function searchByDocument(string $query, int $eventId): Collection
    {
        $normalizedDocument = $this->normalizeDocument($query);

        return Guest::query()
            ->where('event_id', $eventId)
            ->where('document_normalized', $normalizedDocument)
            ->with(['sector', 'promoter'])
            ->get();
    }

    /**
     * Busca convidados similares (possíveis duplicados).
     *
     * @return Collection<int, array{guest: Guest, similarity: float}>
     */
    public function searchSimilar(string $query, int $eventId, float $threshold = 0.7): Collection
    {
        $normalizedQuery = $this->normalize($query);

        // Busca todos os convidados do evento
        $guests = Guest::query()
            ->where('event_id', $eventId)
            ->whereNotNull('name_normalized')
            ->get(['id', 'name', 'name_normalized', 'document', 'sector_id']);

        // Calcula similaridade para cada convidado
        return $guests->map(function (Guest $guest) use ($normalizedQuery) {
            return [
                'guest' => $guest,
                'similarity' => $this->calculateSimilarity($normalizedQuery, $guest->name_normalized ?? ''),
            ];
        })
            ->filter(fn (array $item) => $item['similarity'] >= $threshold && $item['similarity'] < 1.0)
            ->sortByDesc('similarity')
            ->values();
    }

    /**
     * Encontra possíveis duplicados dentro de um evento.
     *
     * @return Collection<int, array{name_normalized: string, count: int, guests: Collection}>
     */
    public function findPossibleDuplicates(int $eventId): Collection
    {
        return Guest::query()
            ->where('event_id', $eventId)
            ->whereNotNull('name_normalized')
            ->selectRaw('name_normalized, COUNT(*) as count')
            ->groupBy('name_normalized')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($eventId) {
                $guests = Guest::query()
                    ->where('event_id', $eventId)
                    ->where('name_normalized', $item->name_normalized)
                    ->with(['sector'])
                    ->get();

                return [
                    'name_normalized' => $item->name_normalized,
                    'count' => $item->count,
                    'guests' => $guests,
                ];
            });
    }

    /**
     * Calcula a similaridade entre duas strings usando Levenshtein.
     * Retorna um valor entre 0.0 (completamente diferente) e 1.0 (idêntico).
     */
    public function calculateSimilarity(string $str1, string $str2): float
    {
        if ($str1 === $str2) {
            return 1.0;
        }

        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1.0;
        }

        // Usa levenshtein para strings até 255 caracteres
        if (strlen($str1) <= 255 && strlen($str2) <= 255) {
            $distance = levenshtein($str1, $str2);

            return 1.0 - ($distance / $maxLen);
        }

        // Para strings maiores, usa similar_text
        similar_text($str1, $str2, $percent);

        return $percent / 100;
    }

    /**
     * Verifica se uma string contém outra (parcialmente).
     */
    public function containsPartial(string $haystack, string $needle): bool
    {
        return str_contains($this->normalize($haystack), $this->normalize($needle));
    }
}
