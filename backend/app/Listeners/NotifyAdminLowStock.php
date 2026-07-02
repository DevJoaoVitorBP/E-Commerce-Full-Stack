<?php

namespace App\Listeners;

use App\Events\StockLow;
use Illuminate\Support\Facades\Log;

class NotifyAdminLowStock
{
    public function handle(StockLow $event): void
    {
        Log::warning('Estoque baixo', [
            'product_id' => $event->product->id,
            'product_name' => $event->product->name,
            'current_quantity' => $event->product->quantity,
            'min_quantity' => $event->product->min_quantity,
        ]);
    }
}
