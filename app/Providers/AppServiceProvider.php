<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // Custom response macros
        Response::macro('success', function ($data = null, $message = 'Success', $status = 200) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => $message
            ], $status);
        });

        Response::macro('error', function ($message = 'Error', $status = 500, $errors = null) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], $status);
        });
    }
}
