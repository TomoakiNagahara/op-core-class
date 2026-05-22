# Cookie

This document describes the current cookie behavior implemented by `asset/core/class/Cookie.class.php`.

## Overview

`Cookie` provides encrypted cookie storage for framework code.

The main public methods are:

- `Cookie::Get($key, $default = null)`
- `Cookie::Set($key, $val, $expire = null, $option = null)`
- `Cookie::UserID(&$init = null)`

`OP()->Cookie()` returns the same class-level cookie API through the framework facade.

## Storage Format

Before a cookie is stored, the logical key is converted through:

```php
Hasha1($key, 16)
```

The value is serialized and encrypted before it is passed to PHP's `setcookie()`.

When a cookie is read, the stored value is decrypted and unserialized.

This means the browser cookie name and value are not the same as the logical framework key and value.

See `cookie-gdpr.md` for GDPR and ePrivacy notes about automatic identifier cookies.

## Default Expiration

When `Cookie::Set()` receives `null` as the expiration value, the current implementation sets the cookie expiration to roughly 10 years from the current server time.

If the expiration is a non-numeric string, it is converted with `strtotime()`.

If the expiration is numeric and smaller than the current timestamp, it is treated as a relative number of seconds and added to the current time.

## Default Path

On PHP 7.3 and later, the default cookie path is:

```php
OP()->URL('app:/')
```

On PHP versions before 7.3, the default cookie path is:

```php
ConvertURL('app:/')
```

## Shell Behavior

Cookie operations are not available in shell execution.

In shell mode:

- `Cookie::Get()` records a framework error and returns `null`
- `Cookie::Set()` records a framework error and returns `null`
- `Cookie::UserID()` returns `null`

## `UserID()` Current Behavior

`Cookie::UserID()` uses the logical cookie key:

```text
UserID
```

When the cookie already exists, it returns the decrypted stored value.

When the cookie does not exist, it generates a new value with:

```php
md5($_SERVER['REMOTE_ADDR'] . ', ' . microtime())
```

Then it stores that value through `Cookie::Set('UserID', $user_id)` and sets the optional by-reference `$init` flag to `true`.

The generated value is therefore an MD5 string derived from the request IP address and current microtime.

## First Access Behavior

[DOC-GAP] The current implementation does not show an unconditional framework startup call that issues `UserID` on every first access.

The current As-Is behavior is narrower:

- `UserID` is issued when `Cookie::UserID()` is called in a web request and no existing `UserID` cookie can be read.
- The default skeleton startup path, bootstrap flow, and App unit `Auto()` flow do not currently call `Cookie::UserID()` unconditionally.

If the intended framework specification is "issue `UserID` on the first access to the application", the automatic call site is currently missing or disabled in the inspected implementation.

## Known Call Sites

The current inspected call sites are:

- `asset/core/testcase/Cookie.php`
- `asset/unit/app/App.class.php` inside `FingerPrint()`

The `FingerPrint()` usage is inside the App unit fingerprint feature, but the output block that calls it is currently commented out in `App::Content()`.

## Related But Separate API

`asset/unit/app/function/UUID.php` stores a separate logical cookie key:

```text
uuid
```

That function is separate from core `Cookie::UserID()` and should not be treated as the same identifier.
