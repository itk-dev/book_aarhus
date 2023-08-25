<!-- markdownlint-configure-file { "blanks-around-headers": { "lines_below": 0 } } -->
<!-- markdownlint-configure-file { "blanks-around-lists": false } -->

# Changelog

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information about writing changes to this log.

## [Unreleased]

### Changed

- Changed search query to only allow search in subject. 

## [1.1.1] - 2023-08-24

### Changed

- Changed user bookings to paginated

## [1.1.0]

### Added

- Added acceptConflict field to resource.
- Added tests for acceptConflict.
- Ignore deprecation warnings in production.

### Changed

- Modified create booking code to allow conflicts if resource.acceptConflict is true.
- Updated symfony and dependencies.
- Changed how location email and name is found in user bookings.
- Set displayName from resource database when set.

## [1.0.4] - 2023-05-03

### Added

Added auto restart to queue container.

## [1.0.3] - 2023-04-20

### Changed
- Updated resource entity with new schema.
- Updated docker setup to itk-version: 3.0.0
- Refactored message handling and notifications with better logging and retry logic

## [1.0.2] - 2023-02-06

### Added
- Add .ics timezone
- Added tests.
- Upgraded symfony/http-kernel [Security]
- Upgraded symfony/security-bundle [Security]

### Changed
- Change .ics "to time"
- Change .ics description to use subject.

### Fixed
- Fixed timezone issues for emails.

## [1.0.1] - 2022-12-12

- Updated changelog

## [1.0.0] - 2022-12-12

- First release.
