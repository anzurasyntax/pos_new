<?php

namespace App\Services;

use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    /**
     * Persist a stock movement row. Exactly one of $productId or $productVariantId must be set.
     *
     * @param  array<string, mixed>|null  $meta
     */
    public function record(
        string $type,
        int $quantity,
        ?string $referenceType,
        ?int $referenceId,
        ?int $productId,
        ?int $productVariantId,
        ?int $userId = null,
        ?array $meta = null,
    ): StockMovement {
        $allowed = [
            StockMovement::TYPE_PURCHASE,
            StockMovement::TYPE_SALE,
            StockMovement::TYPE_ADJUSTMENT,
            StockMovement::TYPE_RETURN,
        ];

        if (! in_array($type, $allowed, true)) {
            throw ValidationException::withMessages([
                'stock_movement' => ['Invalid stock movement type.'],
            ]);
        }

        $hasProduct = $productId !== null && $productId > 0;
        $hasVariant = $productVariantId !== null && $productVariantId > 0;

        if ($hasProduct && $hasVariant) {
            throw ValidationException::withMessages([
                'stock_movement' => ['Specify either product_id or product_variant_id, not both.'],
            ]);
        }

        if (! $hasProduct && ! $hasVariant) {
            throw ValidationException::withMessages([
                'stock_movement' => ['Either product_id or product_variant_id is required.'],
            ]);
        }

        return StockMovement::create([
            'product_id' => $hasProduct ? $productId : null,
            'product_variant_id' => $hasVariant ? $productVariantId : null,
            'quantity' => $quantity,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'meta' => $meta,
        ]);
    }
}
