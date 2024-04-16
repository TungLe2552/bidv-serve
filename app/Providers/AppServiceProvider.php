<?php

namespace App\Providers;

use App\Models\Base\IrOptions;
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
        try {
            $options = IrOptions::where('autoload', true)->get();
            $config = $options->keyBy('key')->transform(function ($setting) {
                return $setting->value;
            })
                ->toArray();
            config($config);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
