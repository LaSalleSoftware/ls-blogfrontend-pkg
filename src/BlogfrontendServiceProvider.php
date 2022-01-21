<?php

/**
 * This file is part of the Lasalle Software blog front-end package
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright  (c) 2019-2022 The South LaSalle Trading Corporation
 * @license    http://opensource.org/licenses/MIT
 * @author     Bob Bloom
 * @email      bob.bloom@lasallesoftware.ca
 * @link       https://lasallesoftware.ca
 * @link       https://packagist.org/packages/lasallesoftware/ls-blogfrontend-pkg
 * @link       https://github.com/LaSalleSoftware/ls-blogfrontend-pkg
 *
 */

namespace Lasallesoftware\Blogfrontend;

// Laravel class
// https://github.com/laravel/framework/blob/5.6/src/Illuminate/Support/ServiceProvider.php
use Illuminate\Support\ServiceProvider;


class BlogfrontendServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * "Within the register method, you should only bind things into the service container.
     * You should never attempt to register any event listeners, routes, or any other piece of functionality within
     * the register method. Otherwise, you may accidentally use a service that is provided by a service provider
     * which has not loaded yet."
     * (https://laravel.com/docs/5.6/providers#the-register-method(
     *
     * @return void
     */
    public function register()
    {
        //
    }


    /**
     * Bootstrap any package services.
     *
     * "So, what if we need to register a view composer within our service provider?
     * This should be done within the boot method. This method is called after all other service providers
     * have been registered, meaning you have access to all other services that have been registered by the framework"
     * (https://laravel.com/docs/5.6/providers)
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutes();

        //$this->loadTranslations();
    }

    /**
     * Load this package's routes
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/blog.php');
    }

    /**
     * Load this package's translations
     *
     * @return void
     */
    protected function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../translations/', 'lasallesoftwareblogbackend');
    }

}
