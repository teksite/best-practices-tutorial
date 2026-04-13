<?php

namespace Modules\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AuthServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Auth';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auth';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }


    /**
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
        RateLimiter::for('check-user', function (Request $request) {
            if (app()->isLocal()) return Limit::none();
            return Limit::perMinute(2, 1)->by('check-user::' . $request->ip());

        });
        RateLimiter::for('send-verification-code', function (Request $request) {
            if (app()->isLocal()) return Limit::none();
            return Limit::perMinute(2, 1)->by('check-user::' . $request->ip());

        });
    }
}
