<?php

namespace App\Providers;

use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Eloquent\ChatRepository;
use App\Repositories\Eloquent\MessageRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ChatRepositoryInterface::class,
            ChatRepository::class
        );

        // Bind Message Repository
        $this->app->bind(
            MessageRepositoryInterface::class,
            MessageRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
