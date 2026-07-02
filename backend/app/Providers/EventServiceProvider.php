<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\ProductCreated;
use App\Events\StockLow;
use App\Listeners\LogProductCreation;
use App\Listeners\NotifyAdminLowStock;
use App\Listeners\SendOrderNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Mapeamentos de eventos para listeners da aplicação.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ProductCreated::class => [
            LogProductCreation::class,
        ],
        OrderCreated::class => [
            SendOrderNotification::class,
        ],
        StockLow::class => [
            NotifyAdminLowStock::class,
        ],
    ];

    /**
     * Registra quaisquer eventos para a aplicação.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determina se eventos e listeners devem ser descobertos automaticamente.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
