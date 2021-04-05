# Serialization for Eloquent's QueryBuilder

## Installation

```bash
composer require anourvalar/eloquent-serialize
```


## Usage

### Serialize
```php
$package = \EloquentSerialize::serialize(
    \App\User
        ::with('userPhones')
        ->where('id', '>', '10')
        ->limit(20)
);
```


### Unserialize
```php
$builder = \EloquentSerialize::unserialize($package);

foreach ($builder->get() as $item) {
    // ...
}
```
