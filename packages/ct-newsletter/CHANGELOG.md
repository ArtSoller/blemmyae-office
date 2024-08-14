# Change Log for CT: Newsletter

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

_Nothing yet._

## 1.0.0 - 2022-03-31
### Features
* Initial release.
* CT: Newsletter and related type taxonomy.
* Newsletter autofill.
* Populate allowed topics by type on deploy.
* Newsletter briefs field group.
* Move briefs field to newsletter collection.
* New terms to newsletter type taxonomy.
* Marketo integration.
* Add "populate parental terms" option to taxonomies.

[Unreleased]: https://github.com/GaryJones/plugin-boilerplate/1.0.0...HEAD

## 1.0.1 - 2022-04-20
### Features
* Remove non-printable characters from body

## 1.1.0 - 2022-05-11
### Features
* Add `Resource` content type to newsletters.

## 1.1.1 - 2022-06-06
### Minor features
* Add ppworks episode and segment post types to newsletters.

## 1.1.2 - 2022-06-06
### Fixes
* Remove restriction by editorial type.

## 2022-10-05
### Features
* Add new `Ransomware` newsletter type

## 2022-10-12
### Features
* Add unsubscribe link for `Ransomware` newsletter
* Add new fields for Generate Newsletter: `schedule date`, `subject` and `available topics` 
* Load available topics from newsletter form

## 2022-10-14
### Fix
* Decode url in HTML body for newsletter before pushing to Marketo.

## 2022-10-18
### Features.
* Remove posts filtration by allowed topics list in newsletter->Editorial & Posts fields.

## 2023-06-07
### Features
* Update approach of retrieving newsletter's html.