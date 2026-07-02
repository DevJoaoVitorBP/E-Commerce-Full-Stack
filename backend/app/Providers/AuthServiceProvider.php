<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Mapeamentos de modelo para política da aplicação.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Registra quaisquer serviços de autenticação / autorização.
     */
    public function boot(): void
    {
        //
    }
}
