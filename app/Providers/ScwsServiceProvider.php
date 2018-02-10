<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ScwsServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('scws', function (){
            require_once app_path('Helpers/pscws4/PSCWS4.php');
            $cws = new \PSCWS4('utf-8');
            $cws->set_dict(app_path('Helpers/pscws4/etc/dict.utf8.xdb'));
            $cws->set_rule(app_path('Helpers/pscws4/etc/rules.utf8.ini'));
            return $cws;
        });
    }


    public function provides (){
        return [
            'scws'
        ];
    }


}
