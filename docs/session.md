# `\OP\Session`

This document describes the current As-Is behavior of the `\OP\Session` wrapper class.

The implementation owner is:

- `asset/core/class/Session.class.php`

The low-level storage trait is documented in:

- `asset/core/trait/docs/op-session.md`

## Role

`\OP\Session` is the public wrapper class for framework-managed session values.

It uses `OP_SESSION`, so its storage location is resolved by the trait.

## Current Storage Location

For `\OP\Session`, `OP_SESSION::Session()` currently resolves the storage location effectively as:

```php
$_SESSION[_OP_NAME_SPACE_]['CORE']['Session'][_APP_ID_]
```

This is the class-specific result of the trait's namespace construction.

## `Get()`

`Get()` reads a value by key from the scoped session array.

```php
Session::Get(string $key, $default = null)
```

Current implementation:

```php
return self::Session()[$key] ?? $default;
```

If the key is not set or resolves through `??` as null, the default value is returned.

## `Set()`

`Set()` writes a value by key into the scoped session array.

```php
Session::Set(string $key, $val)
```

Current implementation:

```php
self::Session()[$key] = $val;
```

## Null Value Behavior

`Session::Set('key', null)` stores `null` at the key.

It does not unset the key, because `Set()` writes directly into `self::Session()[$key]` and does not call the trait's direct `Session($key, null)` path.

The direct trait unset behavior is documented in `asset/core/trait/docs/op-session.md`.

## Scope

This document records `\OP\Session` wrapper behavior only.

`OP()->Session()` as an access method on `\OP\OP` is documented in `asset/core/class/docs/op-class-session.md`.
