# Config

This document describes the current configuration loading behavior implemented by `asset/core/class/Config.class.php`.

## Overview

`Config::Get($name)` loads configuration in layers.

The current order is:

1. `asset/unit/<name>/config.php` if it exists
2. `asset/config/<name>.php`
3. `asset/config/_<name>.php`

Later layers overwrite earlier layers using `array_replace_recursive()`.

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
