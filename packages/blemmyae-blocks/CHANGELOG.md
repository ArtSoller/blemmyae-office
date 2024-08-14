# Change Log for Blemmyae Blocks

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2023-12-21
### Features
- Make collection widget blocks editable in a modal
- Turn on async load of layouts(https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#async-settings)

## 2023-10-26
### Features
- Add `Hum Recommendation Widget` collection widget block

## 2023-10-17
### Features
- Add additional options to marketo widget

## 2023-10-03
### Features
- Modify threat, ransomware, daily scan, cloud to use marketo template v2

## 2023-10-09
### Features
- Add Application Security newsletter type

## 2023-09-25
### Features
- Add ai newsletter application config
- Add ai newsletter type

## 2023-09-19
### Features
- Add Newsletter Ad collection widget block

## 2023-08-30
### Features
- Add Identity template option for marketo form block

## 2023-08-17
### Features
- Update Landing block weight type.

## 2023-08-08
### Features
- Add `applications` param for authorContentTeaserPosts GraphQL field

## 2023-07-13
### Features
- Add `Custom Background Color` and `Custom Text Color` fields to columns and all collection widget blocks

## 2023-07-13
### Features
- Update `Person Profile Card` block - rename `hideImage` to `hideHeadshot`, `hideDeck` to `hideBio`, remove `hidden` headshot style

## 2023-07-03
### Features
- Update `Simple List` block - add show/hide options and company profile support.

## 2023-07-03
### Features
- Add `Person Profile Card` block.
- Add `Company Profile Card` block.
- Update config file.

## 2023-06-06
### Features
- Enable hierarchical view for Featured Feed Post field.

## 2023-05-11
### Features
- Add new layouts for collection widget.

## 2023-03-28
### Features
- Add `Ongoing events` options for the `Horizontal list with image` block.

## 2023-03-21
### Features
- Add support for `postType` predefined value in block config

## 2022-12-07
### Features
- Add `Featured Feed Post` block
- Update options for featured post block and for horizontal list with image

## 2022-12-07
### Features
- Add template field to Marketo block for choosing marketo form variant.
 
## 2022-12-05
### Bug
- Remove unnecessary `cerberus_dfp_native` gql type from schema for blocks that don't support it

## 2022-11-30
### Bug
- Check that `terms` is not `null`

## 2022-11-09

### Features

- Create new `landingPreviewById` endpoint to load previews with resolved post collections.

## 2022-08-30

### Features

- Move block classes from ct-landing and ct-editorial to blemmyae-blocks
- Update Native Ads Manager to support dynamic list of endpoints
- Redo block and resolver structure to allow blocks resolving terms
- Move collection widget config to blemmyae-blocks
- Add unit tests for most important classes

## [Unreleased]

_Nothing yet._

* Initial release.

## 1.0.0 - 2022-04-21
* Initial release.

## 1.0.1 - 2022-04-22
* Add block config for editorial related block

## 1.0.2 - 2022-04-22
* Restore blocks field initialization with empty array

## 1.0.3 - 2022-04-26
* Replace WordPress post type name 'person' with 'people'

## 1.0.4 - 2022-04-29
* Update list with image supported post types

## 1.0.5 - 2022-05-11
* Added support for SC Award Nominee CT for editorial related block

## 1.1.0 - 2022-05

### Features

- Support ppworks episodes and segments.

## 1.1.1 - 2022-05-23

### Fixes

- Incorrect feed content in case of empty taxonomyQuery variable

## 1.1.2 - 2022-05-30

* Treat `nonDfpNatives` option as `nativeAds`

## 1.1.3 - 2022-06-01

* Restore support for non-dfp natives in blocks
* Update non dfp natives injection process
* Update tax query construction for non dfp natives

## 1.1.4 - 2022-06-08

* Update tax query construction for landing pages

## 1.1.5 - 2022-06-17

* Use term ids when constructing taxonomy query

## 1.2.0 - 2022-07-12

* Add meta query by native ad sponsor field value
