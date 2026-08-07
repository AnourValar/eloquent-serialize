<?php

namespace AnourValar\EloquentSerialize\Tests;

use AnourValar\EloquentSerialize\Package;
use AnourValar\EloquentSerialize\Tests\Models\User;

class PackageTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function testFacadeAccessor()
    {
        $this->assertInstanceOf(
            \AnourValar\EloquentSerialize\Service::class,
            \AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade::getFacadeRoot()
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $package = unserialize($this->service->serialize(User::where('id', '!=', 1)));

        $this->assertSame(['model', 'connection', 'eloquent', 'query'], array_keys($package->get())); // without a key

        $this->assertSame(User::class, $package->get('model'));
        $this->assertNull($package->get('unknown_key'));
    }

    /**
     * @return void
     */
    public function testUnserializePackageInstance()
    {
        $builder = User::with('userPhones')->where('id', '!=', 1);

        $package = unserialize($this->service->serialize($builder));
        $this->assertInstanceOf(Package::class, $package);

        $unserialized = $this->service->unserialize($package); // not a string

        $this->assertEquals($builder->toSql(), $unserialized->toSql());
        $this->assertEquals($builder->getBindings(), $unserialized->getBindings());
    }

    /**
     * @return void
     */
    public function testUnserializeIncorrectArgument()
    {
        $this->expectException(\LogicException::class);
        $this->service->unserialize(serialize(['model' => User::class]));
    }

    /**
     * The packer drops nulls, but a hand-built (or legacy) package may still carry them
     *
     * @return void
     */
    public function testUnserializeExplicitNulls()
    {
        $builder = User::where('id', '!=', 1);

        $data = unserialize($this->service->serialize($builder))->get();
        $this->assertArrayNotHasKey('havings', $data['query']);

        $data['query']['havings'] = null;
        $unserialized = $this->service->unserialize(new Package($data));

        $this->assertEquals($builder->toSql(), $unserialized->toSql());
        $this->assertNull($unserialized->getQuery()->havings);
    }

    /**
     * Backward compatibility: before f8408e9 an eager load was packed as the query builder's state itself,
     * without the 'query' / 'eloquent' / 'extra' envelope
     *
     * @return void
     */
    public function testUnserializeLegacyEagerFormat()
    {
        $builder = User::with(['userPhones' => fn ($query) => $query->where('is_primary', '=', true)]);

        $data = unserialize($this->service->serialize($builder))->get();
        $data['eloquent']['with']['userPhones'] = $data['eloquent']['with']['userPhones']['query'];

        $legacy = $this->service->unserialize(new Package($data));

        $this->assertEquals($builder->toSql(), $legacy->toSql());
        $this->assertEquals(
            $builder->get()->pluck('userPhones.*.id')->all(),
            $legacy->get()->pluck('userPhones.*.id')->all()
        );
    }
}
