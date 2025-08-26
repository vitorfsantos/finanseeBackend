<?php

namespace App\Providers;

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
    // // Register Auth module
    // $this->app->register(\App\Modules\Auth\ModuleServiceProvider::class);

    // // Register Users module
    // $this->app->register(\App\Modules\Users\ModuleServiceProvider::class);
  }
}
