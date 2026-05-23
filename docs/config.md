# Config

This document describes the current configuration loading behavior implemented by `asset/core/class/Config.class.php`.

## Overview

`Config::Get($name)` loads configuration in layers.

The current order is:

1. `asset/unit/<name>/config.php` if it exists
2. `asset/config/<name>.php`
3. `asset/config/_<name>.php`

Later layers overwrite earlier layers using `array_replace_recursive()`.

The config name is normalized to lowercase before loading.
For example, `Config::Get('CI')` and `Config::Get('ci')` target the same internal config key.

Configuration is lazy-loaded.
The first `Get()` or `Set()` for a name initializes and caches that config in `Config::$_config`.
Later calls return or merge into the cached value.

## Current Loading Scope

The current `Config.class.php` implementation automatically loads UNIT default config only:

```text
asset/unit/<name>/config.php
```

It does not automatically load MODULE default config from:

```text
asset/module/<name>/config.php
```

This is intentional.
If MODULE default config were loaded automatically only when there is no same-named UNIT, developers would have to remember that MODULE config is sometimes loaded and sometimes not loaded.
To avoid that inconsistent mental model, MODULE default config is never loaded automatically by `Config::Get($name)`.

When a MODULE ships default settings, keep them as a template in `asset/module/<module-name>/config.php`.
Users should explicitly copy that file to `asset/config/<module-name>.php` when they want to enable or customize the MODULE config.
This keeps runtime config ownership visible in the application config area instead of depending on automatic resolution.

## Layout Config Exception

Layout config files are not automatically loaded by `Config::Get($name)`.

If this file exists:

```text
asset/layout/<name>/config.php
```

`Config::Get($name)` still does not load it.
The implementation only suppresses the missing-config error in that case, because layout names may conflict with unit config names.

## Missing Config

If none of the supported config files exists, `Config::Get($name)` records an error:

```text
This config file does not exists: <name>
```

The method still returns the cached config value, which is initialized as an empty array.

## File Return Contract

Config files must return an array.

If an included config file returns a non-array value, the framework records an error and treats that layer as an empty array.

## Private Local Override Pattern

The underscore-prefixed file is a built-in local override mechanism.

Examples:

- `asset/config/admin.php`
- `asset/config/_admin.php`
- `asset/config/database.php`
- `asset/config/_database.php`
- `asset/config/php.php`
- `asset/config/_php.php`

This allows a public base configuration to remain in Git while local-only settings are stored separately in the underscore file.

## Historical Position

Historically, the underscore-ignore rule came first.

The repository already had the convention that underscore-prefixed files are normally not included in standard Git flows.

The config override mechanism was added later as a feature built on top of that pre-existing rule.

So `_name.php` override behavior should be understood as a later convenience feature riding on the earlier underscore-ignore policy.

## Operational Intent

This is also connected to the repository `.gitignore` strategy.

Because `.gitignore` ignores:

- `.*`
- `_*`

underscore-prefixed local override files are normally excluded from `git add .`.

This idea is broader than config files alone.

As a practical repository technique, underscore-prefixed files and directories are good candidates for content that should remain local by default.

That makes it easier to keep environment-specific values local, such as:

- development database credentials
- admin IP or mail settings for a local environment
- PHP runtime overrides only needed on one machine

## Safety Characteristic

This mechanism is designed to reduce accidental publication of local-only configuration.

It helps prevent mistakes such as:

- pushing development-only settings to a shared repository
- deploying local-only values to production
- publishing sensitive local configuration to a public repository

More broadly, it helps reduce the chance of accidentally committing and publishing something that was only meant to exist in one local environment.

It is still possible to add these files explicitly with `git add -f`, so this is an operational safeguard, not a hard security boundary.

## Merge Behavior

The merge uses:

- `array_replace_recursive()`

This means the underscore override file replaces matching values from the earlier layers while preserving unrelated keys.

`Config::Set($name, $config)` uses the same merge behavior.
It initializes the target config first if needed, then merges the passed associative array into the cached config.

Passing a numeric array to `Config::Set()` is invalid.
If `$config[0]` exists, the implementation records an error because config updates are expected to be associative arrays.

## `OP()->Config()` Wrapper

`OP()->Config()` and `OP::Config()` are wrappers around this class:

- `OP()->Config('name')` calls `Config::Get('name')`.
- `OP()->Config('name', ['key' => 'value'])` calls `Config::Set('name', ...)`.
- `OP()->Config()` returns a `Config` instance.
