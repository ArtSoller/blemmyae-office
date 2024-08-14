# Change Log for Blemmyae Deployment

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2024-02-25
### Fix
- Reset transients for author feed.

## 2024-01-29
### Feature
- RI to migrate from legacy layout options(mt-x, mb-x) to paddings(pt-x, pb-x)

## 2023-12-11
### Fix
- Fix graphql media error. Migrate media IDs in the post content for A9s sites.

## 2023-12-06
### Feature
- Switch to stellate wp plugin from graphcdn.

## 2023-11-20
### Feature
- RI to populate the existing cross application redirects.

## 2023-10-17
### Fix
- Migrate media IDs in the post content for A9s sites.

## 2023-10-09
### Feature
- Move content types, taxonomies and Google News sitemap configs to corresponding classes in the administration plugin.

## 2023-10-05
### Feature
- Allow "cra" app.

## 2023-07-17
### Feature
- Add application field to sitemap generation.

## 2023-07-04
### Features
- Add sitemaps for a9s sites.

## 2023-04-26
### Features
- Release instruction to convert gutenberg speaker block into person post if it doesn't exist.

## 2023-04-25
### Features
- Release instruction to merge persons duplicates.

## 2023-04-19
### Features
- Release instruction that flattens application field values for some learnings

## 2023-04-19
### Features
- Release instruction that fills person fields and replaces author with multiple people listed with actual people profiles

## 2023-02-23
### Features
- Release instruction that fills editorial_type field of editorials that miss it based on article types on staging

## 2023-02-23
### Features
- Empty lqip media data.

## 2023-02-22
### Feature
- Update learnings via ri, set application based on brand.

## 2022-12-28
### Features
- Release instruction that converts draft convertr whitepapers to published internal

## 2022-12-21
### Features
- Release instruction to trash draft whitepapers that do not have public duplicate and are older than a week
=======
## 2023-01-11
### Feature
- Update permalink for People CT to `contributor` instead of person type.

## 2023-01-09
### Features
- Release instruction to restore non migrated youtube embed blocks for director's cut editorials

## 2022-12-07
### Features
- Release instruction to create terms of `year` utility taxonomy

## 2022-11-08
### Features
- Release instruction to fill `vendor` field of learnings that do not have it filled

## 2022-10-26
### Features
- Release instruction to remove `business-contunuity` duplicate topic

## 2022-10-19
### Features
- Release instruction to migrate data from WYSIWYG to new `Grid - Card List` block for CSC content

## Unreleased
### Fixes
- GraphCDN purge tokens
- Use field id constants to reference acf fields

## 2022-10-13
### Features
- Release instruction to clean up after deprecated convertr sync

## 2022-08-25
### Features
- Release instruction to fill ppworks shows content with subscribe links

## 1.1.6 - 2022-08-01
### Updates
- Use field id constants to reference acf fields

## 1.1.5 - 2022-07-27
### Features
- Empty `topic` field - process posts without terms and set topic of those to `Content`

## 1.1.4 - 2022-07-07
### Fixes
- `Undefined array key 0` error for some editorials - reindex array keys

## 1.1.3 - 2022-07-27
### Features
- Release instruction to fix `Business Continuity` term having incorrect slug

## 1.1.2 - 2022-07-07
### Fixes
- `Topic` field is empty for older editorials

## 1.1.1 - 2022-07-07
### Fixes
- `Show Featured Image` toggle true value for post-types not processed by `blem-346`

## 1.1.0 - 2022-06-14
### Updates
- Update sitemap generation - change namespaces and add function to generate separate sitemaps for sites.

## 2022-08-12
### Features
* Release instructions to tag 2021 sc awards editorials with sc award taxonomy

## 1.0.0 - 2022-06-14
### Features
* Initial release.
* BLEM-128 - add ri to remove newsml duplicates by @Zinkutal in #2
* BLEM-175 - Update Intrado meta fields for the existing webcasts by @guvkon #3
* BLEM-175 - Fix deployment on blemmyae by @guvkon in #4
* BLEM-194 - Resave webcasts with Intrado registration links by @guvkon #5
* BLEM-194 - Re-run update of webcasts by @guvkon #6
* BLEM-171 - theme update by @Zinkutal #7
* BLEM-197 - Add core Gutenberg blocks definitions for WPGraphql by @guvkon #9
* Revert "BLEM-171 - theme update" by @Zinkutal #10
* BLEM-179 - Add general HUM CDP settings by @guvkon #14
* 
