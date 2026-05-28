# `\OP\Session`

この文書は、`\OP\Session` wrapper class の現行 As-Is の挙動を説明します。

実装の所有者は次です。

- `asset/core/class/Session.class.php`

低レベルの保存 trait は次に記録します。

- `asset/core/trait/docs/op-session.md`

## 役割

`\OP\Session` は、framework 管理下の session 値に対する public wrapper class です。

この class は `OP_SESSION` を use しているため、保存先は trait によって解決されます。

## 現行の保存先

`\OP\Session` の場合、`OP_SESSION::Session()` は現行では実質的に次の保存先を解決します。

```php
$_SESSION[_OP_NAME_SPACE_]['CORE']['Session'][_APP_ID_]
```

これは trait の namespace 組み立てに対する、この class 固有の結果です。

## `Get()`

`Get()` は、scope 済み session array から key 指定で値を読みます。

```php
Session::Get(string $key, $default = null)
```

現行実装は次です。

```php
return self::Session()[$key] ?? $default;
```

key が未設定、または `??` により null と扱われる場合、default value が返ります。

## `Set()`

`Set()` は、scope 済み session array に key 指定で値を書き込みます。

```php
Session::Set(string $key, $val)
```

現行実装は次です。

```php
self::Session()[$key] = $val;
```

## null value の挙動

`Session::Set('key', null)` は key に `null` を保存します。

`Set()` は `self::Session()[$key]` に直接書き込み、trait の直接経路である `Session($key, null)` を呼ばないため、key を unset しません。

trait の直接 unset 挙動は `asset/core/trait/docs/op-session.md` に記録します。

## 範囲

この文書は `\OP\Session` wrapper の挙動のみを記録します。

`\OP\OP` 上の access method としての `OP()->Session()` は `asset/core/class/docs/op-class-session.md` に記録します。
