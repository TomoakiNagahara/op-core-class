# `\OP\OP` クラス

## 場所

`asset/core/class/OP.class.php`

## 役割

`\OP\OP` class は、`OP()` から返される framework 中央 access class です。

これは、trait の合成によって framework 機能を公開する facade 的 object として機能します。

## 現在の構成

この class は現在、次の trait を使っています。

- `OP_CORE`
- `OP_CI`
- `OP_ENV`
- `OP_TEMPLATE`
- `OP_ONEPIECE`
- `OP_DEPRECATE`

## 意味

`\OP\OP` は、自身の class body にすべての実装を書くのではなく、trait を通じて振る舞いを集約しています。

そのため、この class は framework 機能の composition point になっています。

## この class が表すもの

実務上、`\OP\OP` は次を表します。

- framework の gateway object
- `OP()` の背後にある object
- core 機能や helper method をまとめた access surface

## まとめ

`\OP\OP` は `OP()` から返される class であり、trait ベースの構成によって framework 機能を公開します。
