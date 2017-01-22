<?php

namespace App\Providers;

use App\Auth\CustomGuard;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\SalePolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Sale::class    => SalePolicy::class,
        User::class    => UserPolicy::class,
        Branch::class  => BranchPolicy::class,
        Setting::class => SettingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('custom', function ($app, $name, array $config) {
            $guard = new CustomGuard($name, Auth::createUserProvider($config['provider']), $app['session.store']);

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }
}
