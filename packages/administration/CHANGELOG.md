# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2023-12-12
## Feature
- Update getting redirects logic.

## 2023-12-05
## Feature
- Remove some columns from editorial view, remove unnecessary filtering options
- Rename wp author column on editorial view
- Add actual author column to editorial view
- Hide post author from gutenberg inspector

## 2023-10-19
## Feature
- Trigger autosave on preview button click.

## 2023-10-17
## Fix
- Update permalink logic, use SCM app by default for content types that don't support apps.

## 2023-10-09
## Feature
- Refactor sitemap generation.

## 2023-10-05
### Fix
- Update view link logic for scm content.

## 2023-10-04
### Fix
- Do not update slug on rewrite & republish and scheduled actions.

## 2023-09-14
### Features
- Add Identity template style option for landing pages.

## 2023-09-12
### Fix
- Fix graphql errors.

## 2023-08-25
### Fix
- Update RSS feed version.

## 2023-08-15
### Fix
- Generating unique application slug fails during cron.

## 2023-08-08
### Feature
* Make application slug field writable

## 2023-07-28
### Feature
- Update URLs for RSS feeds.

## 2023-07-17
### Feature
- Add application field to sitemap generation.

## 2023-07-04
### Features
- Add sitemaps for a9s sites.

## 2023-07-06
### Features
- Add RSS feeds for a9s sites.

## 2023-07-03
### Added
- to_be_published status is made public for GraphQL.

## 2023-06-22
### Enhancement
- Remove empty post status from `postStatus` field group

## 2023-06-13
### Feature
- Add graphqlPreResolveField filter to not return 500 error in case of draft posts in newsletter collection

## 2023-06-08
### Feature
- Add multiple field for cerberus applications 
- Hide default slug field and render applications field \
- Add options `CERBERUS_APPS_ENABLE_SLUG_SUPPORT` for soft release

## 2023-04-25
### Feature
- Create class for merging duplicates purposes.

## 2023-04-23
### Fix
- Update handling image field for feeds.

## 2023-03-04
### Feature
- Decrease cache living time for filters with counters.

## 2023-02-23
### Features
- Add alpha channel to media's lqip generation.
- Use `webp` instead of `jpeg` for media library.

## 2023-02-22
### Feature
- Add application field to learning content type.
- Add ri for field groups import.

## 2023-02-16
### Feature
- Exclude common non-indexed fields from elastic search.

## 2023-02-08
### Feature
- Use preview link for specific apps, instead of using SCM link

## 2023-01-17
### Bug
- Hide redundant native taxonomy widgets.

## 2023-01-25
### Chore
- Removed cymatic script.

## 2023-01-11
### Feature
- Add all people to sitemap, instead of authors only.

## 2023-01-04
### Feature
- Add `Latest` RSS feed for Google News.

## 2022-12-14
### Feature
- Add ability to skip some terms from sitemap. 
- Remove all topics pages with `sponsor` landings from sitemap. 
 
## 2022-12-01
### Bug
- Work only with public posts, when user resolve `Post Object` ACF field.

## 2022-11-14
### Features
- Add Ppworks Segment post type to topics RSS feeds.

## 2022-11-14
### Features
- Filter out encoded elements from post slug

## 2022-11-09
### Features
- Add event start date column and allow sorting by it on learnings page in cms

## 2022-11-08
### Features
- Add filtering by vendor for learnings in cms

## 2022-10-20
### Features
- Add new `Application` taxonomy
- Create new `Application` field group
- RI: Select brand term for landings based on slug
- Update `cerberusApps` class:
  - Add `getListOfAvailableApps()`
  - Add few handy constant
  - Add function `getAppsNameByLandingPath` to get apps name directly from LP slug
- Fix sitemap based on updates from `cerberusApps`

## 2022-11-09
### Features
- Build preview link with correct preview ID.

## 2022-08-17
### Features
- Utilize `display hierarchical` field from taxonomy fields config

## [1.0.0] - 2021-03-03
### Initial
- Custom ad placement for Advanced Ads.
- Roles update for editors to manage menus.
- RIs support.

## [1.1.0] - 2021-03-04
### Added
- Cymatic script, see https://cra.myjetbrains.com/youtrack/issue/SCM-435.

## [1.2.0] - 2021-04-01
### Added
- Entity importer support for:
  - CPT_UI;
  - ACF.

## [1.2.1] - 2021-04-13
### Added
- Custom entity class for structured import/export methods.
### Removed
- Vendor folder.

## [1.2.2] - 2021-04-14
### Updated
- CustomPostType - dir path fix for extended classes.

## [1.2.3] - 2021-04-16
### Added
- ACF Extended options.
- Archive bulk option for all post types.

## [1.2.4] - 2021-04-20
### Added
- GraphQL support for Advanced Ads.

## [1.2.5] - 2021-04-26
### Added
- CustomGutenbergBlock class.

## [1.2.6] - 2021-04-30
### Fixed
- ACF entity importer.

## [1.2.7] - 2021-05-03
### Added
- GraphQL support for CoAuthors: Plus - Guest Authors.

## [1.2.8] - 2021-06-01
### Added
- Custom config import logging.
- Added ACFE options page import type.

## [1.2.9] - 2021-06-03
### Added
- Utility method for inserting taxonomy terms
- Utility method for importing CSV

## [1.2.10] - 2021-06-06
### Added
- WPGraphQL custom types support.

## [1.2.11] - 2021-06-08
### Added
- ACF options - nullify empty fields.

## [1.2.12] - 2021-06-08
### Features
- Gutenberg block definitions import for WPGraphQL.
- Imports core Gutenberg block definitions.

## [1.2.13] - 2021-06-10
### Features
- Add support for ACFE fields Post Type and Taxonomy Terms to WPGraphQL (BLEM-197)

## [1.2.14] - 2021-06-17
### Features
- Optionable allUrls graphql query
- Publicly open menus and menuItems in graphql (CERB-72)

## [1.2.15] - 2021-06-22
### Updated
- ACFE:GraphQL - taxonomy terms field output termNode instead of Float (BLEM-214)
### Added
- CPT_UI/Advanced_Ads/CoAuthors_Plus meta fields support for WPGraphQL (BLEM-214)

## [1.2.16] - 2021-06-23
### Fixed
- ACFE:GraphQL - taxonomy terms field output termNode instead of Float (BLEM-214)

## [1.2.17] - 2021-06-30
### Features
- Rewrite for co-authors entity | guest-author

## [1.2.18] - 2021-07-02
### Features
- Exposed ACF fields for CPT_UI

## [1.2.19] - 2021-07-03
### Features
- ACF fields support for ElasticPress

## [1.2.20] - 2021-07-05
### Features
- Pagination for Optionable allUrls graphql query
- Optionable allUrlsPostsCount graphql query

## [1.2.21] - 2021-07-13
### Features
- Image and author support for ACF fields in ElasticPress

## [1.2.22] - 2021-07-14
### Features
- Add sort and filter by event date to GraphQL

## [1.2.23] - 2021-07-14
### Features
- Support of unsupported Gutenberg Blocks.
- Ad tool update.

## [1.2.24] - 2021-07-15
### Fix
- Fix permalink prefix for guest authors

## [1.2.25] - 2021-07-16
### Features
- Support for sitemap generation for posts
- Support for sitemap generation for taxonomies

## [1.2.26] - 2021-07-20
### Fixes
- Reduced amount of urls per sitemap file

## [1.2.27] - 2021-07-22
### Features
- Add `content` and `expired` fields for Advanced Ads to GraphQL

## [1.2.28] - 2021-07-26
### Features
- Editors permissions to manage CoAuthors: Plus - Guest Authors.

## [1.2.29] - 2021-07-27
### Fixes
- Fix path to sitemaps in sitemap index.

## [1.2.30] - 2021-07-28
### Fixes
- Fix ACF options page import.
- Remove gb-collection plugin specific code.

## [1.2.31] - 2021-07-30
### Fixes
- Fix ad image size in newsletters.

## [1.3.0] - 2021-08-03
### Features
- ACF preview mode support.
- Discards need in a legacy WPE framework.

## [1.3.1] - 2021-08-03
### Features
- Add Google News sitemap generation.

## [1.3.2] - 2021-08-06
### Features
- Add Webhooks test support.

## [1.3.3] - 2021-08-13
### Features
- Editors taxonomy filtering.
- Restricted taxonomy management - admin only.
- Meta override fields

## [1.3.4] - 2021-08-16
### Features
- Add support for purging GraphCDN cache.

## [1.3.5] - 2021-08-19
### Fixes
- Fix broken libsyn iframes.

## [1.3.6] - 2021-08-24
### Fixes
- Fix ElasticPressOptions PHP warnings on some images update.

## [1.3.8] - 2021-09-06
### Features
- Add support for People content type to sitemap generation.

## [1.3.9] - 2021-09-09
### Fix
- Fix CMS content links.
- Fix post links in CMS admin.

## [1.3.10] - 2021-09-09
### Fix
- Update author collection config from production changes.

## [1.3.11] - 2021-09-15
### Fix
- Fix live/on-demand lists not working correctly for events on the day.

## [1.3.12] - 2021-09-17
### Features
- Person post field.
- Guest Authors cleanup.
### Fixes
- ElasticPressOptions fatal errors.

## [1.3.13] - 2021-09-17
### Updates
- Person post field.

## [1.3.14] - 2021-09-20
### Features
- Person post field - pagination args.

## [1.3.15] - 2021-09-21
### Features
- Person post field - post_type arg.

## [1.3.16] - 2021-09-22
### Fixes
- Person post field.

## [1.3.21] - 2021-10-14
### Features
- Add "Advanced Page Options" field group.

## [1.3.22] - 2021-10-21
### Features
- Add "redirects" GraphQL field.

## [1.3.23] - 2021-10-25
### Updates
- Make argument type of redirects GQL field to be ID.

## [1.3.24] - 2021-10-28
### Fixes
- Do not return redirects to itself for GQL redirects field.

## [1.3.25] - 2021-11-02
### Features
- Add author post count GraphQl field

## [1.3.26] - 2021-11-05
### Features
- S3 Media Offload: Added cdn support for images src set.

## [1.3.27] - 2021-11-23
### Features
- Add PSR compliant logger class.
- Add environment check methods to Utils.

## [1.3.28] - 2021-12-08
### Features
- Add Unpublish Date option to all post types.

## [1.3.29] - 2021-12-12
### Features
- Cron for custom gutenberg block definitions import.

## [1.3.30] - 2021-12-13
### Fixes
- Fix archival of updated posts via cron every hour
- Keep publishing options field group only on whitepapers, learnings, and newsletters

## [1.3.31] - 2021-12-13
### Features
- Add theme colour fields to landings

## [1.3.32] - 2021-12-13
### Fixes
- Run release instructions to import updated field groups.

## [1.3.33] - 2021-12-15
### Features
- Allow unsetting of the theme colour fields.

## [1.3.34] - 2021-12-16
### Features
- 3 Minutes CRON event scheduling.

## [1.3.35] - 2021-12-22
### Fixes
- Set up a proper psr-4 source inside `includes` directory.

## [1.3.36] - 2021-12-22
### Fixes
- Fix class loading on case sensitive file systems.

## [1.3.37] - 2021-12-28
### Fixes
- Remove obsolete code (lowercased directories in `includes`).

## [1.4.0] - 2021-12-29
### Features
- Add Atom + Google Publisher Center feeds for all topics.

## [1.4.1] - 2021-12-29
### Fixes
- Fix self link for feeds.

## [1.4.2] - 2021-12-29
### Fixes
- Fix fatal error on editorial save.
- Fix feed URL sent to PubSubHubbub Hub.

## [1.4.3] - 2022-01-05
### Added
- Hides taxonomy metaboxes.

## [1.4.4] - 2022-01-07
### Fixes
- Posts query for topic atom feeds.

## [1.4.5] - 2022-01-10
### Added
- Fallback for empty topic atom feeds.

## [1.5.0] - 2022-01-17
### Added
- Add Media Advanced field group to attachments.

## [1.5.1] - 2022-01-19
### Fixes
- Make Original Source field of Media Advanced field group into URL type.

## [1.5.2] - 2022-01-25
### Fixes
- Atom feed nullable array value at setup.

## [1.5.3] - 2022-01-26
### Updated
- Utils::insertTaxonomyTerm to support parent update.
### Added
- Utils::exportTaxonomy to support 1-3 level taxonomy export ready for import.

## [1.6.0] - 2022-03-07
### Added
- Utils::createFileAttachmentFromUrl to create file attachments from public URLs.

## [1.6.1] - 2022-03-08
### Fixed
- Fix import of ACF blocks.

## [1.6.2] - 2022-03-08
### Removed
- Remove custom_gutenberg_block_config_import cron job.

## [1.6.3] - 2022-03-10
### Fixed
- Fix a PHP notice in PsrLogger when used via WP CLI.

## [1.7.1] - 2022-03-18
### Added
- LQIP field for Media Attachments.
- Main Topic field for GraphQL.
- New formatting for taxonomy widgets, trimmed dash prefixes.

## [1.7.2] - 2022-03-19
### Added
- Taxonomy Terms field widget custom choices filtering and sorting.

## [1.7.3] - 2022-03-21
### Fixed
- Auto-population of parental terms logic for topics.

## [1.7.4] - 2022-03-21
### Fixed
- Corrected source value for LQIP field.

## [1.7.5] - 2022-03-29
### Fixed
- "Populate parental terms" is now a widget option.
- Prevent default parental terms populating.

## [1.7.6] - 2022-04-14
### Added
- Update parent topic field on post update
### Updated
- Exclude topic field from parent terms injection

## [1.8.0] - 2022-04-19
### Added
- Flag taxonomy and field group for all post types.

## [1.8.1] - 2022-04-19
### Fixed
- Made Flag taxonomy terms into constants.

## [1.8.2] - 2022-04-25
### Fixes
- Adds SVG to the list of allowed MIME types.

## [1.8.3] - 2022-04-27
### Updated
- Remove 3rd column from taxonomy
-

## [1.8.4] - 2022-04-27
### Fixed
- Get terms returns empty array of terms if no terms from taxonomy are assigned to some post

## [1.8.5] - 2022-05-09
### Updated
- Better log Graph CDN cache purging.

## [1.8.6] - 2022-05-10
### Updated
- Populate parental terms for sc awards categories.

## [1.8.7] - 2022-05-12
### Fixes
- Purge of GraphCDN cache times out.

## [1.8.8] - 2022-05-12
### Fixes
- Limited GCDN request timeout to purging all caches.

## [1.8.9] - 2022-05-16
### Fixes
- CLI cache purge dependencies.

## [1.8.10] - 2022-05-16
### Fixes
- CLI CDN cache purge - not supported in CLI mode message.

## 1.8.11 - 2022-05-17
### Fixes
- Remove unnecessary Logger `echo` which breaks WordPress Ajax callbacks.

## 1.8.12 - 2022-05-20
### Fixes
- Errors in GraphQL - duplicate field on Post entity.

## 1.8.13 - 2022-05-26
### Fixes
- Fix content replacement for WP GraphQL.

## 1.9.0 - 2022-05-20
### Updated
- Allow Editors to access taxonomies.
- Restrict taxonomies editing.
- Add `Community Region Advanced` field group.

## 1.9.1 - 2022-05-24
### Added
- Add `image` field to meta field group

## 1.9.2 - 2022-05-25
### Updated
- Rename slider fields in `Community Region Advanced`.
- Change GraphQL name for `Community Region Advanced`.

## 1.10.0 - 2022-05-26
### Added
- Update editorial links to feature first topic as a main topic

## 1.10.1 - 2022-05-30
### Fixes
- Fix redirects GraphQL endpoint to support redirects ending with slash.

## 1.10.2 - 2022-05-30
### Fixes
- Fix PHP Notice in `ElasticPressOptions.php`.

## 1.10.3 - 2022-06-02
### Fixes
- Update Community Region Advanced field group.

## 1.10.4 - 2022-06-02
### Fixes
- Fix redirects GQL endpoint. Searching redirects by slug was a bad idea.

## 1.10.5 - 2022-06-02
### Fixes
- Purge qa2,qa3 GraphCDN caches on qa1 cache purge.

## 1.10.6 - 2022-06-02
### Fixes
- Bump RI version to rewrite `Community Region` slug.

## 1.10.7 - 2022-06-07
### Features
- Update graphql post status to public for custom public unfinished post status

## 1.10.8 - 2022-06-07
### Fixes
- Type error - remove data argument type and add check

## 1.10.9 - 2022-06-07
### Added
- Check if `data` is instance of `WP_Post`

## 1.10.10 - 2022-06-07
### Fixes
- Incorrect instanceof check

## 1.10.11 - 2022-06-08
### Fixes
- Fix `acf/update_value` hook for taxonomies.

## 1.10.12 - 2022-05-30
### Added
- Remove special unicode characters from post title on post save

## 1.10.13 - 2022-06-10
### Added
- `taxonomyTermsByDepth` GraphQL endpoint.
- `getTaxonomyHierarchy` and `getTermsFromHierarchyByDepth` utility methods.

## 1.10.14 - 2022-06-14
### Added
- Add function which returns list of public post statuses.

## 1.11.0 - 2022-05-30
### Updates
- Add `AbstractSitemap` to extend for different sites.
- Add CISO sitemap entity `Ciso.php`.
- Rename: `Sitemap.php` ~> `Scmagazine.php`.
- Move Sitemap entities into separate folder.
- Change namespace for Sitemap entities.
- Add `isCisoPage` utility function.

## 1.11.1 - 2022-06-20
### Added
- Taxonomy filters for ppworks episode and segment pages

## 1.12.0 - 2022-06-27
### Updates
- Add `Submitter` class to submit newly generated sitemaps.
- Use `spatie/async` to submit sitemaps asynchronously.
- Log info and errors about submitted sitemaps.
### Fixes
- Submit sitemaps immediately after generation.
- Add at least 10 posts for Google News.
- Do not cut URLs after last 1,000 for Google News.
- Fix `Unterminated entity reference` error for Google News (encode HTML special chars).
- Fix URL duplicates and skips - yield posts correctly.

## 1.12.1 - 2022-06-28
### Fixes
- Add `Utils` class to `AbstractSitemap`.

## 1.13.0 - 2022-06-29
### Updates
- Do not purge all graphcdn caches on post save

## 1.13.1 - 2022-07-21
### Fixes
- Remove `/` from the end of the URL for all posts in sitemap.

## 1.13.2 - 2022-07-21
### Fixes
- Remove `/` from the end of the URL for all posts in sitemap for AbstractSitemap.

## 1.13.3 - 2022-08-02
### Fixes
- Fix PsrLogger when logging non-strings.

## 1.14.0 - 2022-08-02
### Features
`ElasticPress`:
- Filter out any meta items that are part of repeater or flexible content except for the first one
- Filter out private meta fields and layouts field of landing posts
