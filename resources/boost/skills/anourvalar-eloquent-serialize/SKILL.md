---
name: anourvalar-eloquent-serialize
description: Load when working with the anourvalar/eloquent-serialize package — it exposes the EloquentSerialize facade (resolving to AnourValar\EloquentSerialize\Service) for converting Eloquent query builders into serializable strings and restoring them, useful for queued jobs, caching queries, deferring execution, or copying builders across processes.
---

# AnourValar Eloquent Serialize

`anourvalar/eloquent-serialize` is a Laravel package (Laravel 6–12 compatible) that packs an `Illuminate\Database\Eloquent\Builder` (including eager loads, global/local scopes, joins, unions, casts, locks, and a subset of relation objects) into a plain string and unpacks it back into a working builder. It is designed for cases where Laravel's native closure serialization is too fragile — e.g. sending a query to a queue, storing it in cache, or transferring it across requests.

## When to use

- The user wants to serialize / store / queue an Eloquent query builder and rebuild it later.
- The user references `EloquentSerialize`, `EloquentSerializeFacade`, or `AnourValar\EloquentSerialize\Service`.
- The user needs to deep-clone or transport a builder with `with(...)`, scopes, casts, joins, or unions intact.
- The user wants to persist a configured Eloquent query (search filters, eager loads) between requests.
- The user is dispatching a job and needs to pass a query builder safely without relying on closure serialization.

Do NOT use this skill for plain `serialize()`/`unserialize()` of models, or for serializing query results (collections) — only for the builder itself.

## Facades

### `AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade`

Aliased as `EloquentSerialize` (registered automatically via Laravel package discovery, see `composer.json`'s `extra.laravel.aliases`). Resolves to the singleton `AnourValar\EloquentSerialize\Service` from the container.

Public static methods (declared via `@method` annotations and proxied to `Service`):

- `static string serialize(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder)` — Packs the builder and returns the serialized string.
- `static \Illuminate\Database\Eloquent\Builder unserialize(mixed $package)` — Accepts the string produced by `serialize()` (or an already-unserialized `Package` instance) and returns a rebuilt Eloquent `Builder`.

```php
use EloquentSerialize; // facade alias
use App\Models\User;

$package = EloquentSerialize::serialize(
    User::query()->with('userPhones')->where('id', '>', 10)->limit(20)
);

$builder = EloquentSerialize::unserialize($package);

foreach ($builder->get() as $user) {
    // ...
}
```

## Services

### `AnourValar\EloquentSerialize\Service`

The concrete class behind the facade. It composes three traits that do the heavy lifting: `Grammars\ModelGrammar`, `Grammars\EloquentBuilderGrammar`, and `Grammars\QueryBuilderGrammar`. You can resolve it directly from the container when you prefer not to use the facade.

Public methods:

- `serialize(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder): string`
  - Accepts an Eloquent `Builder` OR one of these relations: `HasOne`, `HasMany`, `BelongsTo` (incl. `MorphTo`), `MorphOne` — they are reduced to their underlying query via `$relation->getQuery()`.
  - Throws `\RuntimeException` for unsupported relations (e.g. `HasOneThrough`, `HasManyThrough`, `BelongsToMany`).
  - Applies and clears global scopes before packing so they are not re-applied twice on unserialize.
  - Returns a PHP `serialize()`-encoded string wrapping a `Package` value object.
- `unserialize(mixed $package): \Illuminate\Database\Eloquent\Builder`
  - Accepts the string returned by `serialize()` or a `Package` instance.
  - Throws `\LogicException` if the argument is not a valid package.
  - Returns a fresh Eloquent `Builder` on the original model and connection, with eager loads, removed global scopes, casts, wheres, joins, etc. re-applied.

```php
use AnourValar\EloquentSerialize\Service;
use App\Models\User;

$service = app(Service::class);

$payload = $service->serialize(User::query()->withTrashed()->where('id', '<', 100));
$builder = $service->unserialize($payload);
```

### `AnourValar\EloquentSerialize\Package`

Internal value object returned by `pack()` and consumed by `unpack()`. Most consumers will never touch it directly; the facade and `Service::serialize/unserialize` hide it.

- `__construct(array $data)` — stores the packed representation.
- `get(?string $key = null): mixed` — returns the full data array, or the value at `$key` (`model`, `connection`, `eloquent`, `query`), or `null`.

### Grammar traits (internal, but consumed by `Service`)

These live in `AnourValar\EloquentSerialize\Grammars\` and are mixed into `Service`. They are not part of the public API surface — do not call them directly. They are listed only so an agent can recognize what code does what:

- `ModelGrammar` — `pack()` / `unpack()` entry points; handles global scopes and morphable eager loads via runtime macros on `Builder` and `Relation`.
- `EloquentBuilderGrammar` — `packEloquentBuilder()` / `unpackEloquentBuilder()`: eager loads (`with`), removed global scopes, model casts.
- `QueryBuilderGrammar` — `packQueryBuilder()` / `unpackQueryBuilder()`: bindings, aggregate, columns, distinct, from, wheres, groups, havings, groupLimit, orders, limit/offset, unions (+ unionLimit/unionOffset/unionOrders), lock, joins.

## Usage examples

### 1. Queue a query for later execution

```php
use EloquentSerialize;
use App\Models\User;

// In the dispatcher
$payload = EloquentSerialize::serialize(
    User::query()
        ->with('userPhones')
        ->where('active', true)
        ->orderBy('id')
);

ProcessUsers::dispatch($payload);

// In the job
class ProcessUsers implements ShouldQueue
{
    public function __construct(public string $payload) {}

    public function handle(): void
    {
        $builder = EloquentSerialize::unserialize($this->payload);

        $builder->chunk(500, function ($chunk) {
            // ...
        });
    }
}
```

### 2. Cache a built-up query between requests

```php
use EloquentSerialize;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;

$payload = EloquentSerialize::serialize(
    Post::query()
        ->with(['tags', 'author'])
        ->where('published', true)
        ->where('created_at', '>=', now()->subWeek())
);

Cache::put("post-query:{$userId}", $payload, now()->addHour());

// Later
$builder = EloquentSerialize::unserialize(Cache::get("post-query:{$userId}"));
$posts   = $builder->paginate(20);
```

### 3. Round-trip via JSON (the package supports this)

The serialized string is a plain PHP serialization, so it round-trips through `json_encode` / `json_decode` as long as the binary stays intact as a string. The package's own test suite does exactly this (`tests/AbstractSuite.php::compare`).

```php
$payload = EloquentSerialize::serialize(User::query()->where('id', '>', 1));
$json    = json_encode($payload);

// ...transport...

$builder = EloquentSerialize::unserialize(json_decode($json, true));
```

### 4. Serialize a relation (reduced to its underlying query)

```php
use EloquentSerialize;
use App\Models\User;

$user = User::first();

// HasMany / HasOne / BelongsTo / MorphOne are supported.
$payload = EloquentSerialize::serialize($user->posts()); // HasMany

$builder = EloquentSerialize::unserialize($payload);
$builder->where('published', true)->get();
```

### 5. Resolve the service explicitly (no facade)

```php
use AnourValar\EloquentSerialize\Service;
use App\Models\User;

$service = app(Service::class);

$payload = $service->serialize(User::query()->withCount('userPhones'));
$builder = $service->unserialize($payload);
```

## Conventions / gotchas

- **Input must be a `Builder` or a supported `Relation`.** Supported relations: `HasOne`, `HasMany`, `BelongsTo` (and therefore `MorphTo`), `MorphOne`. Unsupported relations throw `\RuntimeException`: `HasOneThrough`, `HasManyThrough`, `BelongsToMany`, etc. Convert to a plain query (`->getQuery()` / `->newQuery()`) if you need to ship a different relation type.
- **Chaperone / inverse relations are not preserved** when serializing a relation directly — the package strips the relation wrapper and packs only its query.
- **Global scopes are baked in at serialize time.** `Service::serialize` calls `applyScopes()` and then `withoutGlobalScopes(...)` so the scopes' constraints are part of the packed query and won't be re-applied on the rebuilt builder. Removed scopes are remembered via `removedScopes()` and re-applied on `unserialize`.
- **`unserialize()` returns a fresh `Eloquent\Builder`, not a `Relation`.** Even if you serialized a relation, you get back a builder rooted on the related model with all the relation's constraints already applied.
- **Closures inside `with(...)` callbacks are unwrapped during packing**, not stored as closures, by invoking them against the relation's query and packing the resulting state. Recursion (a model relation chain referencing the same model more than twice) falls back to `Laravel\SerializableClosure\SerializableClosure`, so the project must allow that package's closure signing (Laravel's default config) for deeply recursive eager loads.
- **Connection is preserved.** `Service::serialize` records `$builder->getModel()->getConnectionName()` and `unserialize` calls `Model::on($connection)` to rebuild — make sure the same connection name exists in the target environment.
- **Model casts are merged on unserialize** via `Model::mergeCasts()` (skipped automatically on very old Laravel versions where the method doesn't exist).
- **The returned string is binary `serialize()` output**, not text-safe per se. It survives JSON transport because PHP's `serialize` output is ASCII, but treat it as opaque — do not edit it by hand.
- **No service provider to register manually.** The facade alias is wired up through `extra.laravel.aliases` in `composer.json`; `Service` is a plain class resolved by the container (no bindings required).
- **Requires PHP ^8.0 and Laravel ^8.0–^13.0** (per `composer.json`). For Laravel 6/7 (mentioned in the README) older tags of the package apply.
