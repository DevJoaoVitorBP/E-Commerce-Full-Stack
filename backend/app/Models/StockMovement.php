<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
    ];

    /**
     * Obtém o produto associado a este movimento
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
