# `\OP\OP` Class

## Location

`asset/core/class/OP.class.php`

## Role

The `\OP\OP` class is the central framework access class returned by `OP()`.

It works as a facade-like object that exposes framework capabilities through trait composition.

## Current Composition

The class currently uses these traits:

- `OP_CORE`
- `OP_CI`
- `OP_ENV`
- `OP_TEMPLATE`
- `OP_ONEPIECE`
- `OP_DEPRECATE`

## Why `OP_ENV` Is Used Here

`OP_ENV` is used in `\OP\OP` so that environment-related methods that used to be reached through `Env.class.php` can now be reached directly from `OP()`.

This is part of the 2030-generation simplification:

- older generations grouped those methods under `Env.class.php`
- newer generations move those methods into `OP_ENV`
- `\OP\OP` uses `OP_ENV`, so direct calls such as `OP()->isAdmin()` and `OP()->isLocalhost()` work

At the same time, compatibility access like `OP()->Env()` is preserved separately through `OP_DEPRECATE`.

That means `\OP\OP` using `OP_ENV` is one of the core reasons both styles can coexist during migration.

## Meaning

Instead of implementing all behavior directly in the class body, `\OP\OP` gathers behavior through traits.

This makes the class a composition point for framework features.

## What the Class Represents

In practical use, `\OP\OP` represents:

- the framework gateway object
- the object behind `OP()`
- the composed access surface for core features and helper methods

## Related Method Documents

- `asset/core/class/docs/op-class-session.md`
  Current As-Is behavior of `OP()->Session()`.

## Summary

`\OP\OP` is the class returned by `OP()`, and it exposes framework capabilities through trait-based composition.
