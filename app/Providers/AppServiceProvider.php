<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Jobs\CrawlerJob;
use Illuminate\Support\Facades\Queue;

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
        //
        // Queue::before(function (CrawlerJob $event) {
        //     dump($event->connectionName;
        //     dump($event->job);
        //     // $event->job->payload()
        // });
    }
}
