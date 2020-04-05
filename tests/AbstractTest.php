<?php

namespace AnourValar\EloquentSerialize\Tests;

use Illuminate\Database\Schema\Blueprint;

abstract class AbstractTest extends \Orchestra\Testbench\TestCase
{
    /**
     * @var \AnourValar\EloquentSerialize\Service
     */
    protected $service;

    /**
     * Init
     *
     * @return void
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        \DB::enableQueryLog();

        $this->service = \App::make(\AnourValar\EloquentSerialize\Service::class);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function setUpDatabase(\Illuminate\Foundation\Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('title');
            $table->integer('sort');
            $table->timestamps();
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('user_phones', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('phone');
            $table->timestamps();
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder
     * @return void
     */
    protected function compare(\Illuminate\Database\Eloquent\Builder $builder) : void
    {
        $package = $this->service->serialize($builder);
        $package = $this->service->unserialize($package);

        $package = $this->service->serialize($package);
        $package = json_encode($package);
        $package = json_decode($package, true);
        $package = $this->service->unserialize($package);

        $builder = $this->getScheme($builder);
        $package = $this->getScheme($package);
        $this->assertTrue($builder == $package, "Builder:\n$builder\n\nPackage:\n$package\n\n");
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return string
     */
    private function getScheme(\Illuminate\Database\Eloquent\Builder $builder) : string
    {
        \DB::flushQueryLog();
        $builder->get();
        $logs = \DB::getQueryLog();

        foreach ($logs as &$log) {
            unset($log['time']);
        }
        unset($log);

        return json_encode($logs, JSON_PRETTY_PRINT);
    }
}
