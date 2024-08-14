# Change Log for CT: Landing

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2023-01-17
### Fixes
- Hide all unnecessary blocks from sidebar for `landing` and `sc_award_nominee` content types.
- Hide ad settings block from sidebar.

## 2022-11-28
### Fixes
* Attach apps prefix for slug and permalink.

## 2022-10-18
### Fixes
- Block weights not mapped correctly

## [Unreleased]

_Nothing yet._

## 2022-11-22
### Fixes
* Prepare application prefix for slug for all posts, including any state.

## 1.0.0 - 2021-03-18

* Initial release.
* Body field support removal for landings.

[1.0.0]: https://github.com/cra-repo/ct-landing/1.0.0...HEAD

## 1.1.0 - 2022-03-22

* Add custom resolver for LandingBySlug field
* Add endpoints for offset-based pagination

## 1.2.0 - 2022-03-28

* Update pagination to use offsets
* Update phpdoc

## 1.2.1 - 2022-03-30

* Add "Populate Parental Terms" widget option to landing taxonomy field group.

## 1.2.2 - 2022-04-05

* Update condition for dfp natives injection
* Update condition for calculating page offset
* Introduce hasNextPage field to pagination endpoints
* Restructure pagination endpoints

## 1.2.3 - 2022-04-05

* Fix offset calculation for no natives case

## 1.2.4 - 2022-04-06

* Update typing of pagination endpoint

## 1.2.5 - 2022-04-18

* Initialize blocks field in block queue with empty array
* Add fallback to array_map argument

## 1.2.6 - 2022-04-18

* Add check for countability of collection widget field

## 1.2.7 - 2022-04-20

* Return null when post does not exist
* Update to tax query for non-dfp natives

## 1.3.0 - 2022-04-15

* Move non plugin-specific classes to blemmyae-blocks plugin

## 1.3.1 - 2022-05-23

* Add default values to args array before destructuring
* Add fallback values for all variables of contentTeaserPosts endpoint

## 1.3.2 - 2022-05-23

* Return empty array when no posts were found

## 1.3.3 - 2022-05-26

* More strict taxonomy query existence check

## 1.3.4 - 2022-05-30

* Treat `nonDfpNatives` option as `nativeAds`

## 1.3.5 - 2022-05-31

* Add fetched post ids list to content teaser block level in landing response
* Add fallbacks to collection and post block fields

## 1.3.6 - 2022-05-31

* Update fetched post ids list of content teaser block to exclude post ids resolved by it

## 1.3.7 - 2022-06-01

* Restore support for non-dfp natives in blocks
* Add support for nativeAdTopics field for contentTeaserPosts endpoint
* Update non dfp natives injection process
* Return `{ landingBySlug: null }` when no landing with specified slug was found

## 1.4.0 - 2022-07-12

* Add native ad sponsor support to blocks

## 2022-10-20

### Features

* Add `postSave` actions to attach apps prefix before slug for all landings
