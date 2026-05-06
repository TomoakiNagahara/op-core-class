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

## Meaning

Instead of implementing all behavior directly in the class body, `\OP\OP` gathers behavior through traits.

This makes the class a composition point for framework features.

## What the Class Represents

In practical use, `\OP\OP` represents:

- the framework gateway object
- the object behind `OP()`
- the composed access surface for core features and helper methods

## Summary

`\OP\OP` is the class returned by `OP()`, and it exposes framework capabilities through trait-based composition.
