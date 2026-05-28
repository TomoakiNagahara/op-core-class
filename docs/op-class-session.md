# `OP()->Session()`

This document describes the current As-Is behavior of `OP()->Session()` on the `\OP\OP` class.

The owner class is:

- `asset/core/class/OP.class.php`

The method is provided through the `OP_ONEPIECE` trait used by `\OP\OP`.

## Role

`OP()->Session()` is the framework facade access point for session handling.

It returns an instantiated `\OP\Session` object.

The `\OP\Session` wrapper behavior is documented in:

- `asset/core/class/docs/session.md`

The low-level session storage trait is documented in:

- `asset/core/trait/docs/op-session.md`

## Current Behavior

`OP()->Session()` keeps a static local instance.

On first call, it creates:

```php
new Session()
```

Later calls return the same stored instance.

## Example

The current documented access pattern is:

```php
$count = OP()->Session()->Get('count', 0);
$count = $count + 1;
OP()->Session()->Set('count', $count);
```

## Scope

This document records only the `OP()->Session()` access method on `\OP\OP`.

It does not redefine `\OP\Session` wrapper storage behavior or `OP_SESSION` trait namespace behavior.
