<?php

namespace App\Support;

use App\Models\RequiredDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Display order for required documents: bounded list positions (max 99) and normalized sequence.
 */
final class RequiredDocumentOrdering
{
    public const MAX_ORDER = 99;

    /**
     * @return array<string, string> value => label for HTML select
     */
    public static function slotChoices(?RequiredDocument $editing): array
    {
        $others = RequiredDocument::query()
            ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        $n = $others->count();
        $currentIx = $editing ? (int) $editing->order_index : 0;
        // Include current position in range (handles legacy high numbers until normalized).
        $maxPos = min(self::MAX_ORDER, max($n + 1, $currentIx, 1));

        $choices = ['' => __('— End of list (auto) —')];

        for ($p = 1; $p <= $maxPos; $p++) {
            if ($p <= $n) {
                $target = $others->get($p - 1);
                $choices[(string) $p] = __('Slot :n — before «:name»', [
                    'n' => $p,
                    'name' => $target?->name ?? '',
                ]);
            } else {
                $choices[(string) $p] = __('Slot :n — last in list', ['n' => $p]);
            }
        }

        return $choices;
    }

    /**
     * Desired order_index before normalization (1..MAX_ORDER).
     */
    public static function resolveOrderIndex(?RequiredDocument $editing, ?string $orderSlotRaw): int
    {
        $slot = $orderSlotRaw;
        if ($slot === null || $slot === '') {
            $max = (int) (RequiredDocument::query()
                ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
                ->max('order_index') ?? 0);

            return min(self::MAX_ORDER, max(1, $max + 1));
        }

        $p = (int) $slot;
        if ($p < 1) {
            $p = 1;
        }

        $otherCount = RequiredDocument::query()
            ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
            ->count();

        $maxAllowed = min(self::MAX_ORDER, max(1, $otherCount + 1));

        return min($maxAllowed, max(1, $p));
    }

    /**
     * Persist a document at the requested slot and then rewrite the full sequence to 1..N.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function saveAtSlot(?RequiredDocument $editing, array $attributes, ?string $orderSlotRaw): RequiredDocument
    {
        return DB::transaction(function () use ($editing, $attributes, $orderSlotRaw) {
            $desiredPosition = self::resolveOrderIndex($editing, $orderSlotRaw);

            /** @var Collection<int, RequiredDocument> $orderedDocs */
            $orderedDocs = RequiredDocument::query()
                ->when($editing, fn ($q) => $q->whereKeyNot($editing->id))
                ->orderBy('order_index')
                ->orderBy('name')
                ->get()
                ->values();

            $document = $editing ?? new RequiredDocument;
            $document->fill($attributes);
            $insertIndex = max(0, min($orderedDocs->count(), $desiredPosition - 1));
            $orderedDocs->splice($insertIndex, 0, [$document]);

            $orderedDocs->values()->each(function (RequiredDocument $doc, int $index) {
                $doc->order_index = $index + 1;
                $doc->save();
            });

            return $document->fresh();
        });
    }

    /**
     * Re-assign order_index to 1..N by sort order (fixes duplicates / gaps).
     */
    public static function normalizeSequence(): void
    {
        $docs = RequiredDocument::query()->orderBy('order_index')->orderBy('name')->get();
        foreach ($docs as $i => $doc) {
            $next = $i + 1;
            if ((int) $doc->order_index !== $next) {
                $doc->forceFill(['order_index' => $next])->saveQuietly();
            }
        }
    }
}
