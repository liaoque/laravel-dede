<?php

namespace App\Providers;

use App\Admin;
use App\Exceptions\EloquentMd5UserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('eloquentmd5', function ($app, array $config) {
            // 返回 Illuminate\Contracts\Auth\UserProvider 实例...
            return new EloquentMd5UserProvider($app['hash'],$config['model']);
        });
        //
    }
}
