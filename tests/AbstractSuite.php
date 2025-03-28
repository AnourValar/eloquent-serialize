<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Tests\Models\Book;
use AnourValar\EloquentSerialize\Tests\Models\File;
use AnourValar\EloquentSerialize\Tests\Models\User;
use AnourValar\EloquentSerialize\Tests\Models\UserPhoneNote;
use Illuminate\Database\Schema\Blueprint;
use AnourValar\EloquentSerialize\Tests\Models\Post;
use AnourValar\EloquentSerialize\Tests\Models\Tag;

abstract class AbstractSuite extends \Orchestra\Testbench\TestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/factories');
        $this->setUpDatabase($this->app);
        $this->setUpSeeder();

        \DB::enableQueryLog();

        $this->service = \App::make(\AnourValar\EloquentSerialize\Service::class);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function setUpDatabase(\Illuminate\Foundation\Application $app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('sort');
            $table->jsonb('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $app['db']->connection()->getSchemaBuilder()->create('user_phones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('phone');
            $table->boolean('is_primary');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('user_phone_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_phone_id');
            $table->string('note');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('type');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->bigInteger('taggable_id');
            $table->string('taggable_type');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('book_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('book_id');
        });
        $app['db']->connection()->getSchemaBuilder()->create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    protected function setUpSeeder()
    {
        factory(UserPhoneNote::class)->times(80)->create();
        factory(File::class)->times(40)->create();

        factory(Post::class)->times(10)->create();
        factory(Tag::class)->times(30)->create();

        $users = User::get();
        factory(Book::class)->times(20)->create()->each(function ($book) use ($users) {
            foreach ($users as $user) {
                if (mt_rand(0, 1)) {
                    $user->books()->attach($book->id);
                }
            }
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param bool $execute
     * @return void
     */
    protected function compare(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder, bool $execute = true): void
    {
        $referenceBuilder = clone $builder;
        $referenceSerialize = $this->service->serialize($builder);

        for ($i = 1; $i <= 3; $i++) {
            $builder = $this->service->serialize($builder);
            $this->assertSame($referenceSerialize, $builder, "#$i");

            $builder = json_encode($builder);
            $builder = json_decode($builder, true);
            $builder = $this->service->unserialize($builder);
            $this->assertSame($this->getScheme($referenceBuilder, $execute), $this->getScheme($builder, $execute), "#$i");
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param bool $execute
     * @return string
     */
    private function getScheme(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder, bool $execute): string
    {
        \DB::flushQueryLog();
        if ($execute) {
            $result = [$builder->toSql(), $builder->getModel()->getConnectionName(), $builder->get()];
        } else {
            $result = [$builder->toSql(), $builder->getModel()->getConnectionName()];
        }
        $logs = \DB::getQueryLog();

        foreach ($logs as &$log) {
            unset($log['time']);
        }
        unset($log);

        return json_encode(['query' => $logs, 'result' => $result], JSON_PRETTY_PRINT);
    }
}
