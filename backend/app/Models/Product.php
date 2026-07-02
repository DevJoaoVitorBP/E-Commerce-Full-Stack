<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'quantity',
        'min_quantity',
        'active',
        'category_id',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Obter a categoria associada a este produto
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Obter as tags associadas a este produto
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    /**
     * Obter os itens de pedido associados a este produto
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Obter os movimentos de estoque associados a este produto
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Escopo para obter apenas produtos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Escopo para obter produtos com estoque disponível
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Escopo para obter produtos com baixo estoque
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'min_quantity');
    }

    /**
     * Obter a URL completa da imagem do produto
     */
    public function getImageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return asset('storage/products/'.$this->image_path);
    }
}
