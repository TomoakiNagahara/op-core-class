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

## なぜここで `OP_ENV` を use するのか

`OP_ENV` を `\OP\OP` で use している理由は、もともと `Env.class.php` 経由で到達していた環境関連 method 群を、今は `OP()` から直接呼べるようにするためです。

これは 2030 世代の簡素化の一部です。

- 古い世代では、それらの method は `Env.class.php` 配下にまとまっていた
- 新しい世代では、その method 群を `OP_ENV` に移す
- `\OP\OP` が `OP_ENV` を use することで、`OP()->isAdmin()` や `OP()->isLocalhost()` のような直接呼び出しが可能になる

同時に、`OP()->Env()` のような互換 access は `OP_DEPRECATE` によって別経路で維持されます。

つまり、`OP_ENV` を `\OP\OP` が use していることは、移行期間中に両方の style を共存させる中核的な理由のひとつです。

## 意味

`\OP\OP` は、自身の class body にすべての実装を書くのではなく、trait を通じて振る舞いを集約しています。

そのため、この class は framework 機能の composition point になっています。

## この class が表すもの

実務上、`\OP\OP` は次を表します。

- framework の gateway object
- `OP()` の背後にある object
- core 機能や helper method をまとめた access surface

## 関連 method 文書

- `asset/core/class/docs/op-class-session.md`
  `OP()->Session()` の現行 As-Is の挙動。

## まとめ

`\OP\OP` は `OP()` から返される class であり、trait ベースの構成によって framework 機能を公開します。
