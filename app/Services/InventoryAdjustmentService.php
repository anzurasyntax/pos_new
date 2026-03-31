<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class InventoryAdjustmentService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
    ) {}

    /**
     * Record a manual stock correction (product-level movement).
     * Positive delta increases on-hand stock; negative decreases.
     * Used when stock is set from the product form; purchases and sales use their own movement types.
     */
    public function recordProductLevelAdjustment(Product $product, int $delta, ?int $userId = null, ?string $note = null): void
    {
        if ($delta === 0) {
            return;
        }

        $this->stockMovementService->record(
            StockMovement::TYPE_ADJUSTMENT,
            $delta,
            'product',
            (int) $product->id,
            (int) $product->id,
            null,
            $userId ?? Auth::id(),
            $note ? array_filter(['note' => $note]) : null,
        );
    }
}
