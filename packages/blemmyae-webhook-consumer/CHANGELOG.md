# Change Log for Webhook Consumer

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2023-12-14
### Fixed
- Array in `trim` function error during Swoogo Speaker community values update
- Create DateTime object from array for Swoogo Event and Swoogo Session to avoid using PHP Object in messages

## 2023-12-06
### Changed
- Webhook mapper for PPWorks Person now also saves names into PPWorks specific fields.

## 2023-08-21
### Added
- Update application slug in post insert every sync
 
## 2023-06-30
### Added
- Webhook mapper for PPWorks Sponsor Program.
- RI which deletes previous webhook mappings for sponsors.

### Fixed
- Various PHPCS warnings.

### Changed
- Webhook mappers for PPWorks Segment and Sponsor are updated to accommodate the new PPWorks Sponsor Program post type.

## 2023-06-27
### Updates
- Deprecate complex update logic of regionsCollection in SwoogoSpeaker mapper, populate it as is

## 2023-03-21
### Fixes
- Remove `Custom Series` field from ppworks_show_subscribe field group

## 2022-10-01
### Updates
- Update `Regions Collection` updating for Swoogo Speakers.
- Remove, add or update region collection entries based on previous value from Swoogo.

## 2022-08-22
### Updates
- Use `Swoogo Hash` to find person post ID.
- Process `Community Region` and `Speaker Type` with new method for speakers. 

## 2022-08-11
### Minor features
- Update Learning Post Content on upsert.

## 2022-08-11
### Minor features
- Add prefix to log messages.

### Refactoring
- Fix some PHPCS warnings/errors.

## 1.0.0 - 2021-11-23

* Initial release with basic manipulation of webhook messages and putting them to processed/failed queues afterwards.

## 1.1.0 - 2021-12-21
### Features
- Add ppworks mappers.

## 1.1.1 - 2021-12-22
### Misc
- Code clean up.

## 1.1.2 - 2021-12-30
### Fixes
- Fix webhook_mappings table not being created on the first install.
- Fix several typos in ppworks mappers.

## 1.1.3 - 2021-12-30
### Fixes
- Fix ppworks person mapper when saving guest/host company info.


## 1.2.0 - 2022-01-14
## Features
- Uses TTL Stamp for webhook messages for retries.

## 1.3.0 - 2022-01-17
## Features
- Implement saving of images from ppworks.
- Implement saving vendor specific job titles as taxonomy terms.
- Implement saving vendor specific company for a person as a post reference.

## 1.3.1 - 2022-01-19
### Fixes
- Do not delete person or company posts on exception.

## 1.3.2 - 2022-01-26
### Fixes
- Fix ppworks guest/host mapper.

## 1.3.3 - 2022-01-26
### Fixes
- Fix ppworks image saving.

## 1.3.4 - 2022-01-26
### Fixes
- Fix ppworks image saving again.

## 1.4.0 - 2022-01-27
### Features
- Add swoogo mappers.

## 1.4.1 - 2022-02-01
### Fixes
- Fix SQS visibility timeouts.
- Fix fetching multiple WebhookMappings without IDs.

## 1.4.2 - 2022-02-02
### Fixes
- Fix saving slug for posts in mappers.

## 1.5.0 - 2022-02-08
### Features
- Implement saving of podcast thumbnails from ppworks. 

## 1.6.0 - 2022-02-11
### Features
- Implement saving of podcast S3 video and audio links.

## 1.6.1 - 2022-02-14
### Fixes
- Fix saving of ppworks segments.

## 1.7.0 - 2022-02-15
### Features
- Update saving of episode and segment fields.
- Optimise webhook consumption -- webhook cron job quits on an empty queue.

## 1.8.0 - 2022-02-18
### Features
- Update Swoogo Speaker Mapping with taxonomies publishing.
  - Add Event Region to Swoogo Speakers.
  - Set 'People Type' taxonomy to 'Speaker' for everyone, 'Industry Figure' for Leadership in addition.
  - Set 'Swoogo Speaker Type' taxonomy according to Swoogo data.

## 1.9.0 - 2022-02-24
### Features
- Update saving ppworks article mapper to new structure.
### Fixes
- Make sure DB changes are made when plugin's activated.

## 1.9.1 - 2022-03-03
### Fixes
- Tune SQS visibility timeout.

## 1.9.2 - 2022-03-03
### Fixes
- Fix post dates.
- Do not save company info for people if it's missing.

## 1.9.3 - 2022-03-03
### Fixes
- Fix saving post dates.

## 1.9.4 - 2022-04-14
### Features
- Add support for `position` field to Article and Segment.
- Add support for `publish_date` field to Episode and Segment.

## 1.10.0 - 2022-04-19
### Features
- Add support for `show_in_feeds` field to Episode and Segment.
### Fixes
- Fix import of ppworks' episodes and segments.
- Fix saving of taxonomy terms on episodes and segments.

## 1.10.1 - 2022-04-19
### Fixes
- Fix saving of show taxonomy term on episodes.

## 1.10.2 - 2022-04-21
### Fixes
- Fix saving taxonomy terms to post.

## 1.10.3 - 2022-04-22
### Fixes
- Fix publish date timezone of episodes and segments.

## 1.10.4 - 2022-04-25
### Fixes
- Do not fail webhook consumption on faulty images.
- Do not save all terms on empty array.

## 1.10.5 - 2022-04-26
### Fixes
- Fix saving ppworks' guests and hosts when job title is empty.

## 1.11.0 - 2022-05-09
### Features
- Save image field for ppworks' announcements.
- Save host field for ppworks' article.
- Save new social fields for ppworks' persons.

### Fixes
- Fix processing `delete` events.

## 1.11.1 - 2022-05-09
### Fixes
- Fix saving of images.

## 1.11.2 - 2022-05-10
### Features
- Add locking to webhook processing.

## 1.11.3 - 2022-05-11
### Fixes
- Fix error saving ppworks' articles.

## 1.12.0 - 2022-05-11
### Features
- Update Swoogo Mappers:
  - Map Session into 'Event Session' post type.
  - Merge 'Swoogo Event' and 'Swoogo Session' vendor types into 'Swoogo'.
  - Populate 'Community Region' taxonomy for Events and Speakers.
  - Assign taxonomies to the post on field update.
- Update Abstract Mapper:
  - Add `$field` parameter (ACF field key) to `updateLearningPostSpeakers` and `updateLearningPostLocation`.
  - Update title for Sessions and Events on populating.

## 1.12.1 - 2022-05-13
### Fixes
- Fix publish date of ppworks' segments and episodes.

## 1.12.2 - 2022-05-16
### Fixes
- Fix lock timeout

## 1.12.3 - 2022-05-17
### Fixes
- Make message processing more robust

## 1.13.0 - 2022-05-17
### Features
- Save gallery images for segments.

## 1.13.1 - 2022-05-17
### Fixes
- Fix saving host field for ppworks' articles.

## 1.13.2 - 2022-05-20
### Fixes
- Fix saving show field for ppworks' segments.

## 1.13.3 - 2022-05-24
### Minor features
- Use `Unfinished` post status instead of `Show in Feeds` flag for ppworks' episodes and segments.

### Fixes
- Fix saving topics on ppworks' episodes and segments.

## 1.13.4 - 2022-05-24
### Fixes
- Actually fix saving topics on ppworks' episodes.

## 1.14.0 - 2022-05-25
### Fixes
- Fix taxonomies attachment to the post for Swoogo items.
- Fix Speakers update for Swoogo Events.
### Updates
- Populate `Learning Taxonomy` field group for Swoogo Events.

## 1.14.1 - 2022-05-26
### Fixes
- Event type is now always a string.
- Move `updateTermOnTaxonomyField` to `AbstractPostWebhookMapper`.
- Tag Swoogo Leadership persons with `Industry Figure` subtypes.
- Fix Swoogo Session speakers update.

## 1.14.2 - 2022-06-01
### Fixes
- Remove entries from webhook mappings table when posts and terms are deleted from WordPress.
- Do not make existing posts into drafts.
- Do not delete already existing posts during cleanup.

### Minor features
- Add RI which cleans up bad ppworks data.

## 1.15.0 - 2022-06-02
### Features
- Add `webhook reconsume` command to WP CLI which allows to re-consume webhook data.  

## 1.15.1 - 2022-06-06
### Minor features
- Add `--id` argument to the `webhook reconsume` command.

## 1.15.2 - 2022-06-06
### Fixes
- Fix referenced objects order in Mapper classes.

## 1.15.3 - 2022-06-07
### Fixes
- Use array instead of single term in `updateTermsOnTaxonomyField` method.

## 1.15.4 - 2022-06-14
### Fixes
- Fix post status being draft when updating existing posts. 

## 1.15.5 - 2022-06-15
### Features
- Add redirects for episodes from short URLs.

## 1.15.6 - 2022-06-17
### Features
- Save default_image for ppworks Shows.

## 1.16.0 - 2022-06-30
### Features
- Consume transcription fields on ppworks episodes and segments.

## 1.16.1 - 2022-07-06
### Fixes
- Fix fatal error in WebhookMapping::deleteByEntityIdAndType().
- Fix fatal error due to missing Red_Group class.
