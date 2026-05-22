# Cookie

この文書は、`asset/core/class/Cookie.class.php` が実装している現行の cookie behavior を説明します。

## 概要

`Cookie` は framework code のために encrypted cookie storage を提供します。

主な public method は次です。

- `Cookie::Get($key, $default = null)`
- `Cookie::Set($key, $val, $expire = null, $option = null)`
- `Cookie::UserID(&$init = null)`

`OP()->Cookie()` は、framework facade 経由で同じ class-level cookie API を返します。

## 保存形式

cookie を保存する前に、logical key は次で変換されます。

```php
Hasha1($key, 16)
```

value は PHP の `setcookie()` に渡される前に serialize され、encrypt されます。

cookie を読む時は、保存された value を decrypt し、unserialize します。

そのため、browser に保存される cookie name と value は、framework 上の logical key/value とは一致しません。

automatic identifier cookie に関する GDPR / ePrivacy note は `cookie-gdpr.ja.md` を参照してください。

## default expiration

`Cookie::Set()` の expiration value が `null` の場合、現行実装は現在の server time からおよそ 10 年後を cookie expiration にします。

expiration が non-numeric string の場合は、`strtotime()` で変換されます。

expiration が numeric で、かつ現在 timestamp より小さい場合は、relative seconds として扱われ、現在時刻に加算されます。

## default path

PHP 7.3 以降では、default cookie path は次です。

```php
OP()->URL('app:/')
```

PHP 7.3 より前では、default cookie path は次です。

```php
ConvertURL('app:/')
```

## shell behavior

shell execution では cookie operation は利用できません。

shell mode では次の挙動になります。

- `Cookie::Get()` は framework error を記録し、`null` を返す
- `Cookie::Set()` は framework error を記録し、`null` を返す
- `Cookie::UserID()` は `null` を返す

## `UserID()` の現行挙動

`Cookie::UserID()` は logical cookie key として次を使います。

```text
UserID
```

cookie がすでに存在する場合は、decrypt された保存済み value を返します。

cookie が存在しない場合は、次で新しい値を生成します。

```php
md5($_SERVER['REMOTE_ADDR'] . ', ' . microtime())
```

その後、`Cookie::Set('UserID', $user_id)` で保存し、optional by-reference の `$init` flag に `true` を設定します。

したがって、生成される値は request IP address と current microtime から作られる MD5 string です。

## 初回アクセス時の挙動

[DOC-GAP] 現行実装上は、framework startup が毎回無条件に `UserID` を発行する call site は確認できません。

現行 As-Is の挙動は、より限定的です。

- Web request 中に `Cookie::UserID()` が呼ばれ、既存の `UserID` cookie を読めない場合に `UserID` が発行される。
- default skeleton startup path、bootstrap flow、App unit の `Auto()` flow は、現時点では `Cookie::UserID()` を無条件には呼び出していない。

もし意図している framework specification が「application への初回アクセス時に `UserID` を発行する」ことであれば、確認した現行実装では automatic call site が未実装、または無効化されています。

## 確認できた call site

確認できた現行 call site は次です。

- `asset/core/testcase/Cookie.php`
- `asset/unit/app/App.class.php` の `FingerPrint()` 内

`FingerPrint()` の利用は App unit の fingerprint feature 内にありますが、それを呼び出す `App::Content()` 側の output block は現在 comment out されています。

## 関連するが別の API

`asset/unit/app/function/UUID.php` は、別の logical cookie key を保存します。

```text
uuid
```

この function は core の `Cookie::UserID()` とは別物であり、同じ identifier として扱うべきではありません。
