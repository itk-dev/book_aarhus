<!-- markdownlint-configure-file { "blanks-around-headers": { "lines_below": 0 } } -->
<!-- markdownlint-configure-file { "blanks-around-lists": false } -->

# Changelog

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information about writing changes to this log.

## [Unreleased]

## [1.2.3] - 2024-02-16

### Changed
- Updated the project, it now uses ApiResource instead of config/.yml
- Updated class Extensions and fucntions.
- Updated api/core to version 3.0.0

### Added
- Added State.
- RemovedDataProvider and DataPersister.

### Fixed
- Fixed breaking api-spec differences after the update.




## [1.2.2] - 2023-11-28

### Changed

- Fixed issue where resource display was not set when rebuilding user booking entry cache.
- Added "responseRequested" to booking invitations to fix issue with status.
- Fixed status when resource has not answered event invitation in accept flow.

## [1.2.1] - 2023-11-23

### Changed

- Changed resource display name in notifications.
- Fixed remove from cache table on booking delete.
- Moved handling of delete/update user booking cache entry into messages.

## [1.2.0] - 2023-11-15

### Added

- Added User booking cache entity.
- Added User booking cache service.
- Added API for fetching cached user bookings.
- Removed Rabbit MQ (remeber to update the DSN in local .env)
- Updated docker compose setup to newest version
- Fixed issue with booking cache entries not being created.
- Added resourceDisplayName to cache.
- Moved AddBookingToCacheMessage handling to own queue.

## [1.1.2] - 2023-08-29

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
- Added commands related to user booking cache.

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
