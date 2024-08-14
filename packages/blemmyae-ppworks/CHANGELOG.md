# Change Log for Blemmyae ppworks

All notable changes to this project will be documented in this file.

# Changelog

List of updates in chronological order. Fresh update should be first

## 2023-12-06
### Added
- First and Last Name fields for "PPWorks People Advanced" field group.

## 2023-07-03
### Added
- `to_be_published` public status. Similar to `future` built-in status but directly accessible via GraphQL (similar to `unfinished`).
- Hourly cron job which publishes posts with `to_be_published` status if the post date has passed.

### Changed
- Refactored webhook mappers for PPWorks Segment and Episode to use AbstractPodcast class. There are just too much of shared code between them.

## 2023-06-30
### Added
- "PPWorks Sponsor Program" post type.
- "PPWorks Sponsor Program Advanced" field group.
- "Sponsor Programs" field to "PPWorks Segment Advanced" field group.

## 2022-12-01
### Fix
- Remove `resolveSegments` filter from `Plugin`, because we filter non-public posts in higher level 
in `administration` module.

## 2022-11-28
### Fix
- Return only public segments for Ppworks episode

# Semver changelog
It's old approach, and we will remove it in near future.

## 1.0.0 - 2021-12-06
* Initial release.

## 1.1.0 - 2021-12-08
### Features
- Implement structure for ppworks content

## 1.2.0 - 2022-01-14
### Features
- Make job_title and company into references in ppworks_people_advanced field group.

## 1.2.1 - 2022-01-17
### Minor features
- Add constants for new ACF fields and taxonomies.

## 1.3.0 - 2022-02-08
### Features
- Add featured image to Podcast Basic field group.

## 1.4.0 - 2022-02-11
### Features
- Update Podcast Basic fields.

## 1.5.0 - 2022-02-15
### Features
- Update episode and segment fields.

## 1.6.0 - 2022-02-24
### Features
- Update article post type structure.
- Set permalink structure for episodes and segments.

## 1.6.1 - 2022-02-28
### Fixes
- Return content field back to articles so GraphQL types are consistent for posts.

## 1.6.2 - 2022-03-03
### Fixes
- Clean up code for updating permalinks for entities.

## 1.6.3 - 2022-03-07
### Fixes
- Fix field type of podcast show's description.

## 1.6.4 - 2022-03-11
### Fixes
- Hide ppworks structure from admin dashboard.

## 1.6.5 - 2022-04-14
### Features
- Add `position` field to article and segment.

## 1.6.6 - 2022-04-19
### Minor features
- Show ppworks taxonomies in admin menus when debug is on.

## 1.6.7 - 2022-04-21
### Fixes
- Remove "required" from some fields.

## 1.7.0 - 2022-05-09
### Features
- Add social fields to people.
- Add host field to articles.
- Add image field to announcements.

## 1.7.1 - 2022-05-17
### Features
- Add gallery images to Segment.

## 1.7.2 - 2022-05-17
### Fixes
- Fix host field type for articles.

## 1.7.3 - 2022-05-20
### Fixes
- Add show field for segments.

## 1.7.4 - 2022-05-24
### Minor features
- Update logic for showing ppworks admin menus.
- Introduce `Unfinished` post status (to be used by episodes and segments).

## 1.8.0 - 2022-05-30
### Features
- Create redirects from podcast and group podcast editorials into segments and episodes.
- Remove podcast and group podcast editorials.

## 1.8.1 - 2022-05-30
### Fixes
- Fix redirects creation from podcast editorials to segments.

## 1.8.2 - 2022-05-31
### Fixes
- Make redirects from podcast editorials to use generic `%topic%`.

## 1.8.3 - 2022-06-01
### Fixes
- Fix redirects for podcasts yet again.

## 1.8.4 - 2022-06-01
### Fixes
- Fix name of BLEM-497 RIs.

## 1.8.5 - 2022-06-14
### Features
- Add `unfinished` status to list of public post statuses.

## 1.8.6 - 2022-06-17
### Features
- Add default_image field to Shows.

## 1.9.0 - 2022-06-30
### Features
- Add transcription fields to "PPWorks Podcast Basic" field group.

## 1.9.1 - 2022-07-07
### Minor features
- Allow CISO podcast redirects to be done.

## 2022-08-19
### Features
- Add new field group `PPWorks Show Subscribe`.
