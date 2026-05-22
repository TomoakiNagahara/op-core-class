# Cookie GDPR And ePrivacy Notes

This document records the GDPR and ePrivacy risk review for the current `Cookie::UserID()` behavior in `asset/core/class/Cookie.class.php`.

This is an engineering compliance note, not legal advice.

## Source Documents

Primary references used for this review:

- ePrivacy Directive Article 5(3): https://eur-lex.europa.eu/eli/dir/2002/58/art_5/par_3/oj/eng
- EDPB Guidelines 05/2020 on consent under GDPR: https://www.edpb.europa.eu/our-work-tools/our-documents/guidelines/guidelines-052020-consent-under-regulation-2016679_en

## Key Rule

Cookie compliance in the EU is not only a GDPR question.

For cookies and similar client-side storage, ePrivacy rules apply first to the act of storing information on, or accessing information from, a user's device.

The practical rule is:

- prior consent is normally required before setting or reading non-essential cookies
- consent is not normally required for cookies that are strictly necessary for communication or for a service explicitly requested by the user
- when the cookie also involves personal data processing, GDPR requirements also apply

## Strictly Necessary Boundary

The most important engineering distinction is whether the cookie is strictly necessary.

Examples that may be easier to justify as strictly necessary:

- session continuity needed to deliver a requested page flow
- login/session authentication
- security controls that are required to provide the requested service
- user choices that are required for the requested service to work

Examples that usually require prior consent:

- analytics
- marketing
- advertising
- behavioral tracking
- long-term repeat-visitor identification
- personalization that is not required to provide the requested service
- fingerprinting or persistent identification beyond a strictly necessary purpose

## Current `Cookie::UserID()` Risk

[DOC-RISK] `Cookie::UserID()` currently creates a persistent identifier when it is called and no existing `UserID` cookie can be read.

The generated value is derived from:

```php
md5($_SERVER['REMOTE_ADDR'] . ', ' . microtime())
```

The cookie is stored through `Cookie::Set()`, whose default expiration is roughly 10 years.

This creates privacy risk if `UserID` is used for any purpose beyond a strictly necessary service function.

In particular, automatic first-access issuance of a long-lived `UserID` would be difficult to justify as strictly necessary unless the framework clearly defines and limits the purpose.

## Current Implementation Gap

[DOC-GAP] The inspected implementation does not currently show an unconditional startup call that issues `UserID` on every first access.

The current As-Is behavior is:

- `Cookie::UserID()` issues `UserID` only when it is called in a web request and no existing `UserID` cookie can be read
- default skeleton startup, bootstrap, and App unit `Auto()` do not call it unconditionally

This gap matters because the legal risk changes depending on whether `UserID` is automatically issued before any user choice.

## Recommended Framework Position

[DOC-FUTURE] The framework should distinguish required cookies from optional cookies before making `UserID` automatic.

A safer design direction is:

1. Do not issue `UserID` automatically on first access unless its purpose is strictly necessary.
2. If `UserID` is strictly necessary, document that exact purpose and keep the stored data and lifetime minimal.
3. If `UserID` is used for analytics, tracking, personalization, fingerprinting, or repeat-visitor recognition, gate issuance behind prior consent.
4. Make the default lifetime configurable and avoid a long default for identifiers unless the purpose requires it.
5. Keep separate identifiers for separate purposes instead of reusing one framework-wide identifier for unrelated behavior.

## Documentation Requirement

Any feature that calls `Cookie::UserID()` automatically should document:

- the call site
- the purpose of the identifier
- whether the cookie is considered strictly necessary or consent-based
- the expiration policy
- whether the identifier is used for analytics, personalization, security, session continuity, or another purpose
- whether the feature runs before or after user consent

## Implementation Guidance

For code changes, the default engineering rule should be:

- strictly necessary cookie: may be issued without consent, but the purpose and lifetime must be narrow
- optional cookie: do not issue before consent
- unclear purpose: treat as optional until clarified

This keeps the framework from silently turning a general-purpose identifier into a tracking mechanism.
