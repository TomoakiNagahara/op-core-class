# `OP()->Session()`

この文書は、`\OP\OP` class 上の `OP()->Session()` の現行 As-Is の挙動を説明します。

所有 class は次です。

- `asset/core/class/OP.class.php`

この method は、`\OP\OP` が use している `OP_ONEPIECE` trait から提供されます。

## 役割

`OP()->Session()` は、session handling のための framework facade access point です。

これは instantiated `\OP\Session` object を返します。

`\OP\Session` wrapper の挙動は次に記録します。

- `asset/core/class/docs/session.md`

低レベルの session storage trait は次に記録します。

- `asset/core/trait/docs/op-session.md`

## 現行挙動

`OP()->Session()` は static local instance を保持します。

初回呼び出しでは次を作成します。

```php
new Session()
```

以後の呼び出しでは、保持済みの同じ instance を返します。

## 例

現行ドキュメント上の access pattern は次です。

```php
$count = OP()->Session()->Get('count', 0);
$count = $count + 1;
OP()->Session()->Set('count', $count);
```

## 範囲

この文書は、`\OP\OP` 上の `OP()->Session()` access method のみを記録します。

`\OP\Session` wrapper の保存挙動や、`OP_SESSION` trait の namespace 挙動は再定義しません。
