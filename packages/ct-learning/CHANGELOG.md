# Change Log for CT: Learning

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2024-01-10
### Updates
- Update display and return format for start and end dates.

## 2023-06-28
### Updates
- Add validation for speakers migration.

## 2023-06-15
### Updates
- Temporarily disable every field except `Speaker` in the `Speakers (Performers)` field of the `Learning Advanced` group.

## 2023-04-21
### Updates
- Convert gutenberg speaker block into person post if it doesn't exist.

## 2023-03-22
### Updates
- Add `Ask a Question Form Link` field to `Learning Advanced` field group

## 2023-03-09
### Updates
- Fill `Application` field for events from swoogo sync

## 2022-09-19
### Updates
- Add RI to copy CSC Event speakers from CSC specific fields to a main one.

## 2022-09-12
### Updates
- Add CSC Specific event fields to `Learning Advanced` group.
- Add `YouTube` and `CSC Member Portal` vendors.
- Add new Learning Types for CSC Events.

## 1.0.0 - 2022-03-31
### Features
* Initial release.
* CT: Learning and Learning taxonomy
* Add utility fields.
* Additional Intrado utility fields.
* Cron job which archives old on-demand events
* Additional agenda & speaker & status fields
* New vendor types and add some consts.
* Add "populate parental terms" option to taxonomies.

## 1.1.0 - 2022-05-11
### Features
- Add new post type - `Event Session`.
- Add new taxonomy - `Community Region`.
- Extract `Location` to a separate field group.
- Merge `Swoogo Session` and `Swoogo Event` vendor types into `Swoogo`.
- Update `Learning Advanced` field group.
- Update and add some constants.

## 1.1.1 - 2022-05-17
### Fixes
- Add `Custom Date and Time` field to avoid GraphQL errors
- Show field group in GraphQL

### 1.2.0 - 2022-05-25
### Updates
- Add new constants.
- Add a few Learning Types.
- Configs: enable `Save Terms` option to taxonomy fields.
- Remove `SwoogoSession` and `SwoogoEvent` vendor type terms.

## 1.2.1 - 2022-05-26
### Updates
- Add `Virtual Event` learning type.

## 1.2.2 - 2022-06-01
### Updates
- Rewrite Community Region slug to `community`.

## 1.2.3 - 2022-06-07
### Updates
- Update `community region` field with parental terms assigning.
