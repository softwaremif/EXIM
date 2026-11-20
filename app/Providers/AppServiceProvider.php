<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Request ke API Packing List: header X-Api-Key wajib (services.packing.key).
        \Illuminate\Support\Facades\Http::macro('packing', function () {
            return \Illuminate\Support\Facades\Http::withHeaders(['X-Api-Key' => (string) config('services.packing.key')]);
        });

        setlocale(LC_TIME, 'id_ID.UTF-8'); // Format Indonesia
        Carbon::setLocale('id'); 
    }
}
