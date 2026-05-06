# Config

この文書は、`asset/core/class/Config.class.php` が実装している現行の設定読込仕様を説明します。

## 概要

`Config::Get($name)` は、設定を層構造で読み込みます。

現行の読込順は次です。

1. `asset/unit/<name>/config.php` が存在すればそれ
2. `asset/config/<name>.php`
3. `asset/config/_<name>.php`

後段の層は、`array_replace_recursive()` により前段の設定を上書きします。

## private local override pattern

`_` で始まるファイルは、built-in の local override 機構です。

例:

- `asset/config/admin.php`
- `asset/config/_admin.php`
- `asset/config/database.php`
- `asset/config/_database.php`
- `asset/config/php.php`
- `asset/config/_php.php`

これにより、Git 管理される公開用の基礎設定を残しつつ、local 専用の設定は `_` 付きファイルへ分離できます。

## 歴史的な位置付け

歴史的には、underscore-ignore のルールの方が先にありました。

つまり repository には、`_` で始まる file は通常の Git flow に乗らない、という運用が先に存在していました。

config override 機構は、その既存ルールの上に追加された後発機能です。

したがって `_name.php` override の挙動は、先にあった underscore-ignore policy に乗る convenience feature として理解するべきです。

## 運用上の意図

この仕組みは repository の `.gitignore` 方針とも連動しています。

`.gitignore` が次を ignore しているためです。

- `.*`
- `_*`

そのため、`_` で始まる local override file は通常の `git add .` では取り込まれません。

この考え方は config file だけに限りません。

repository 運用の practical technique として、`_` で始まる file や directory は、既定で local に閉じ込めておきたい内容の候補として有効です。

これにより、例えば次のような環境依存値を local に閉じ込めやすくなります。

- 開発用 database credential
- local 環境だけの admin IP や mail 設定
- 特定マシンでだけ必要な PHP runtime override

## 安全性の特徴

この仕組みの目的は、local 専用設定の accidental publication を減らすことです。

例えば次のようなミスを防ぎやすくします。

- 開発用設定を共有 repository に push してしまう
- local 専用値を production に deploy してしまう
- 公開 repository に機微な local 設定を載せてしまう

より広く言えば、1 台の local 環境にだけ存在していてほしかったものを、うっかり commit して公開してしまう事故を減らす助けになります。

ただし、`git add -f` を使えば明示的に追加できるため、これは hard security boundary ではなく、運用上の safeguard です。

## merge の挙動

設定の merge には次を使っています。

- `array_replace_recursive()`

そのため、`_` override file は、前段の同名 key を置き換えつつ、無関係な key は保持します。
