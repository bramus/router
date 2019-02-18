# Changelog for `bramus/router`

## 1.4 – 2019.02.18

- Added: Support for Cyrillic chars and Emoji in placeholder values and placeholder names _(@bramus)_
- Added: `composer test` shorthand _(@bramus)_
- Added: Changelog _(@bramus)_
- Changed: Documentation Improvements _(@bramus)_

## 1.3.1 – 2017.12.22

- Added: Extra Tests _(@bramus)_
- Changed: Documentation Improvements _(@artyuum)_

## 1.3 – 2017.12.21

- Added: Support ‘Class@method’ callbacks in `set404()` _(@bramus)_
- Changed: Refactored callback invocation _(@bramus)_
- Changed: Documentation Improvements _(@artyuum)_

## 1.2.1 – 2017.10.06

- Changed: Documentation Improvements _(@bramus)_

## 1.2 – 2017.10.06

- Added: Support route matching using _“placeholders”_ (e.g. curly braces) _(@ovflowd)_
- Added: Default Namespace Capability using `setNamespace()`, for use with `Class@Method` calls _(@ovflowd)_
- Added: Extra Tests _(@bramus)_
- Bugfix: Make sure callable are actually callable _(@ovflowd)_
- Demo: Added a multilang demo _(@bramus)_
- Changed: Documentation Improvements _(@lai0n)_

## 1.1 – 2016.05.26

- Added: Return `true` if a route was handled, `false` otherwise _(@tleb)_
- Added: `getBasePath()` _(@ovflowd)_
- Added: Support `Class@Method` calls _(@ovflowd)_
- Changed: Tweak a few method signaturs so that they're protected _(@tleb)_
- Changed: Documentation Improvements _(@tleb)_

## 1.0 – 2015.02.04

- First 1.x release

## _(Unversioned Releases)_
 – 2013.04.08 - 2015.02.04

- Initial release with suppport for:
	- Static and Dynamic Route Handling
	- Shorthands: `get()`, `post()`, `put()`, `delete()`, and `options()`
	- Before Route Middlewares / Before Route Middlewares: `before()`
	- After Router Middlewares / Run Callback

- Added: Optional Route Patterns
- Added: Subrouting (mount callables onto a subroute/prefix)
- Added: `patch()` shorthand
- Added: Support for `X-HTTP-Method-Override` header
- Bugfix: Use the HTTP version as found in `['SERVER_PROTOCOL']`
- Bugfix: Nested Subpatterns / Multiple Matching _(@jbleuzen)_