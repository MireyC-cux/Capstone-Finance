<?php

namespace App\Services\Inventory;

use App\Models\InventoryBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BalanceService
{
    public function adjust(int $itemId, int $delta): InventoryBalance
    {
        return DB::transaction(function () use ($itemId, $delta) {
            $balance = InventoryBalance::lockForUpdate()->find($itemId);
            if (!$balance) {
                $balance = new InventoryBalance(['item_id' => $itemId, 'current_stock' => 0]);
            }
            $newStock = $balance->current_stock + $delta;
            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for this operation.'
                ]);
            }
            $balance->current_stock = $newStock;
            $balance->last_updated = now();
            $balance->save();

            return $balance;
        });
    }
}
