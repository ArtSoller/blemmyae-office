# Change Log for CT: People

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2023-04-21
### Updates
- Add `Events Speaker Collection` field to the field list.

## 2023-01-11
### Updates
- Add `getPersonType` function to determine person's type.

## 2022-12-06
### Updates
- Update `people_advanced` field group to use type `text` for `twitter` field

## 2022-10-19
### Updates
- Add RI to copy `Speaker Type` and `Community Region` to a new `Regions Collection` entry for Swoogo Leadership persons. 

## 2022-09-19
### Fixes
- Replace `Our Team` with `CSC Team` in people import RI.

## 2022-09-12
### Updates
- Add `CSC People Advanced` field group.
- Add `CSC People Type` taxonomy.
- Add new constants to `People` class.
- Add a RI to import CSC people.

## 2022-08-19
### Updates
- Add `Swoogo Hash` field to `Swoogo Speaker Advanced` field group.
- Move `Community Region` and `Speaker Type` fields to `Regions Collection` repeater field.

## 1.0.0 - 2021-12-08
* Initial release.

## 1.1.0
### Features
- Make People Type taxonomy hierarchical.
- Add terms to People Type taxonomy.

## 1.1.1
### Fixes
- Fix release instructions to properly import changes in 1.1.0.

## 1.1.2
### Features
- Add People References field group.

## 1.1.3 - 2021-12-21
### Features
- Add ACF field constants.

## 1.2.0 - 2022-01-14
### Features
- Add job_title taxonomy.

## 1.2.1 - 2021-01-17
### Minor features
- Add constants for new ACF fields and taxonomies.

## 1.2.2 - 2021-01-17
### Minor features
- Add constant for acf middleName field.

## 1.3.0 - 2021-01-27
### Features
- Add Swoogo Speaker Advanced field group.
- Add constants for new ACF fields and taxonomies.
- Add job_title taxonomy to People Advanced Companies field.

## 1.4.0 - 2022-02-18
### Features
- New taxonomy (Swoogo Specific) - Swoogo Speaker Event Region.
- New taxonomy (Swoogo Specific) - Swoogo Speaker Type.
- Add these taxonomies fields to Swoogo Speaker Advanced.
- Add new constants.

## 1.4.1 - 2022-03-29
### Added
- "Populate Parental Terms" widget option to taxonomies.

## [1.5.0] - 2022-04-25
### Added
- SC Awards taxonomy & its field

## 1.6.0 - 2022-05-11
### Updated
- Replace `Speaker Event Region` taxonomy with `Community Region`.
- Update and add some constants.

## 1.6.1 - 2022-05-23
### Updated
- Add `Community Director` term to `Swoogo Speaker Type` taxonomy.

## 1.7.0 - 2022-05-25
### Updated
- Unregister `Swoogo Speaker Event Region` taxonomy.
- Hide `Swoogo Speaker Type` taxonomy from UI.
- Add `Save Terms` options to `Swoogo Speaker Advanced` taxonomies.

# 1.7.1 - 2022-05-27
### Updated
- Add subtypes for `Industy Figure` people type:
  - Leadership Board
  - Co-Chair
  - Community Director

## 1.7.2 - 2022-06-07
### Updated
- Update `community region` field with parental terms assigning.
