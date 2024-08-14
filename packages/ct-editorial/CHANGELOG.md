# Change Log for CT: Editorial

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## 2023-07-07
### Feature
- Remove topic from the editorial path

## 2023-06-06
### Feature
- Enable hierarchical view for Editorial Taxonomy topic field.

## 2023-05-15
### Fixed
- Enable hierarchy view for related block's term field.

## 2023-04-23
### Fixed
- Add `FEATURED_IMAGE_CAPTION` field to the list of fields.

## 2023-04-04
### Fixed
- `findMainTopic` returns term ids instead of term objects and causes fatal errors

## 2023-01-09
### Fixed
- Fill `Year` field of editorials with year of creation, not current year on save

## 2022-10-26
### Added
- Fill `year` utility taxonomy field of editorials with current year, if not filled

## 2022-10-26
### Fixed
- `Business contunuity` term renamed to `Business continuity`

## 2022-08-12
### Added
- Add `winner` term as a child of `sc awards 2022`
- Add `display hierarchical` flag to `editorial taxonomy` field group

## 2022-11-30
### Fixed
- Update `Editorial Advanced` collection widget
  - Mark `Terms` field in `Related Block` as required field to avoid sending `null` instead of `Array`


## [Unreleased]

* CT: Editorial
* Related taxonomies: Editorial Types, Industries, Topics, Regions.

## [1.0.0] - 2022-03-21
### Fixed
- Editorial entity get tax methods fatal error.
### Added
- Support for new innodata xmls.
### Update
- Made brief advanced field group immutable.

## [1.0.1] - 2022-03-29
### Added
- "Populate Parental Terms" widget option.

## [1.0.2] - 2022-04-12
### Added
- Remove non-printable symbols from fields in innodata content

## [1.0.3] - 2022-04-12
### Added
- Add parent topic field to editorial taxonomy field group
### Fixed
- "Save terms" and "Load terms" set to false for topic field

## [1.0.4] - 2022-04-18
### Added
- Add resource editorial type

## [1.0.5] - 2022-04-21
### Added
- Add related block config to editorial advanced field group

## [1.1.0] - 2022-04-21
### Added
- Introduce editorial related block pre-resolving

## [1.1.1] - 2022-04-25
### Fixed
- Do not strip a tag from innodata content body

## [1.2.0] - 2022-04-25
### Added
- SC Awards taxonomy & its field

## [1.2.1] - 2022-04-29
### Updated
- Main topic selection strategy update

## [1.2.2] - 2022-05-02
### Fixed
- Empty response when topic field is empty

## [1.2.3] - 2022-05-10
### Added
- SC Award Nominee CT

## [1.2.4] - 2022-05-10
### Updated
- SC Award Nominee CT: Finalist filtering

## [1.2.5] - 2022-05-17
### Added
- `Hide Author` true/false option

## [1.2.6] - 2022-05-19
### Fixed
- Resolved Blocks: SC Award Nominee CT: Finalist custom sorting

## [1.2.7] - 2022-05-23
### Fixed
- Topic widget restored uncategorized and childs

## [1.3.0] - 2022-05-26
### Updated
- Select first topic of editorial as main topic

## 1.3.1 - 2022-05-30
### Fixed
- Fix PHP warning in `Editorial::findMainTopic()`.

## [1.3.2] - 2022-05-30
### Updated
- Treat `nonDfpNatives` option as `nativeAds`

## [1.3.3] - 2022-06-01
### Updated
- Restore support for non-dfp natives in blocks
- Return `{ editorialWithRelatedBlock: null }` when no landing with specified slug was found

## [1.3.4] - 2022-06-15
### Updated
- Innodata sync - populate deck with a first string of description
- Replace new lines with html break tags

## [1.3.5] - 2022-06-15
### Fixed 1.3.4
- Updated nl2br logic
- Added strip_tags for deck

## [1.3.6] - 2022-06-16
### Updated
- Innodata sync - improved logic for detecting first sentence

## [1.3.7] - 2022-06-16
### Fixed 1.3.6
- Innodata sync - improved logic for detecting first sentence

## [1.3.8] - 2022-06-16
### Fixed 1.3.7
- Innodata sync - improved logic for detecting first sentence, added prior sanitation

## [1.3.9] - 2022-06-17
### Fixed
- Internal server error for editorials with empty topic field

## [1.3.10] - 2022-06-23
### Fixed
- Fatal error:  Uncaught Error: Typed property Cra\\CtEditorial\\Entity\\Vendor\\Innodata\\Fields::$published must not be accessed before initialization
