# Cookie GDPR And ePrivacy Notes

この文書は、`asset/core/class/Cookie.class.php` の現行 `Cookie::UserID()` behavior に関する GDPR / ePrivacy risk review を記録します。

これは engineering compliance note であり、legal advice ではありません。

## 参照文書

この review で使った primary reference は次です。

- ePrivacy Directive Article 5(3): https://eur-lex.europa.eu/eli/dir/2002/58/art_5/par_3/oj/eng
- EDPB Guidelines 05/2020 on consent under GDPR: https://www.edpb.europa.eu/our-work-tools/our-documents/guidelines/guidelines-052020-consent-under-regulation-2016679_en

## 基本ルール

EU における cookie compliance は、GDPR だけの問題ではありません。

cookie や類似の client-side storage では、user の device に information を保存する行為、または user の device から information に access する行為に対して、まず ePrivacy rule が適用されます。

実務上の rule は次です。

- non-essential cookie の設定または読取には、通常、事前 consent が必要
- communication または user が明示的に要求した service に strictly necessary な cookie は、通常 consent なしで扱える
- cookie が personal data processing も伴う場合は、GDPR requirement も適用される

## strictly necessary の境界

engineering 上もっとも重要な区別は、その cookie が strictly necessary かどうかです。

strictly necessary と説明しやすい例:

- request された page flow の提供に必要な session continuity
- login/session authentication
- request された service の提供に必要な security control
- request された service を動作させるために必要な user choice

通常は事前 consent が必要になる例:

- analytics
- marketing
- advertising
- behavioral tracking
- long-term repeat-visitor identification
- request された service の提供に不要な personalization
- strictly necessary な目的を超える fingerprinting や persistent identification

## 現行 `Cookie::UserID()` の risk

[DOC-RISK] `Cookie::UserID()` は、呼び出された時点で既存の `UserID` cookie を読めない場合、persistent identifier を作成します。

生成値は次に由来します。

```php
md5($_SERVER['REMOTE_ADDR'] . ', ' . microtime())
```

cookie は `Cookie::Set()` 経由で保存されます。`Cookie::Set()` の default expiration はおよそ 10 年です。

`UserID` が strictly necessary な service function を超える目的で使われる場合、この挙動には privacy risk があります。

特に、長期保存される `UserID` を first access で自動発行する仕様は、framework が目的を明確に定義し、用途を限定しない限り、strictly necessary と説明しにくい可能性があります。

## 現行実装の gap

[DOC-GAP] 確認した実装では、first access ごとに `UserID` を無条件発行する startup call site は確認できません。

現行 As-Is behavior は次です。

- `Cookie::UserID()` は、web request 中に呼び出され、既存の `UserID` cookie を読めない場合だけ `UserID` を発行する
- default skeleton startup、bootstrap、App unit `Auto()` は、`Cookie::UserID()` を無条件には呼び出していない

この gap は重要です。user choice より前に `UserID` を自動発行するかどうかで、legal risk が変わるためです。

## 推奨する framework position

[DOC-FUTURE] `UserID` を automatic にする前に、framework は required cookie と optional cookie を区別するべきです。

より安全な design direction は次です。

1. 目的が strictly necessary でない限り、first access で `UserID` を自動発行しない。
2. `UserID` が strictly necessary なら、その正確な目的を文書化し、保存 data と lifetime を最小化する。
3. `UserID` を analytics、tracking、personalization、fingerprinting、repeat-visitor recognition に使うなら、発行を prior consent の後にする。
4. default lifetime は configurable にし、目的上必要でない限り identifier の長期 default を避ける。
5. 1 つの framework-wide identifier を無関係な behavior に使い回さず、目的ごとに identifier を分ける。

## documentation requirement

`Cookie::UserID()` を自動的に呼び出す feature は、次を文書化するべきです。

- call site
- identifier の目的
- cookie を strictly necessary と見るのか、consent-based と見るのか
- expiration policy
- identifier が analytics、personalization、security、session continuity、または別目的のどれに使われるか
- user consent の前に動作するのか、後に動作するのか

## implementation guidance

code change における default engineering rule は次にするべきです。

- strictly necessary cookie: consent なしで発行できる余地はあるが、purpose と lifetime は狭くする
- optional cookie: consent 前には発行しない
- purpose が不明な cookie: clarify されるまでは optional として扱う

これにより、framework が一般目的 identifier を暗黙に tracking mechanism へ変えてしまうことを避けられます。
