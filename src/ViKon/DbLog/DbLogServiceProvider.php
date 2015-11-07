<?php

namespace ViKon\DbLog;

use Illuminate\Support\ServiceProvider;

/**
 * Class DbLogServiceProvider
 *
 * @package ViKon\DbLog
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class DbLogServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
                             __DIR__ . '/../../database/migrations' => database_path('migrations'),
                         ], 'migrations');
    }
}