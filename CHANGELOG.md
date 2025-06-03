<!-- markdownlint-configure-file { "blanks-around-headers": { "lines_below": 0 } } -->
<!-- markdownlint-configure-file { "blanks-around-lists": false } -->

# Changelog

All notable changes to this project will be documented in this file.

See [keep a changelog](https://keepachangelog.com/en/1.0.0/) for information about writing changes to this log.

## [Unreleased]

- Add excluded resources environment variable.
- Added create/cancel bookings endpoints and tests.
- Updated GitHub workflow images.

## [1.3.0] - 2024-11-14

- Added metric to system.
- Added webformId to notification mail.
- Upgraded bundles.
- Updated api/core to version 3.2.x
- Updated to use ApiResource attributes instead of yaml.
- Updated class extensions and functions.
- Updated data providers and persisters to state providers and processers respectively.
- Removed locations and GET user bookings endpoints.

## [1.2.3] - 2024-10-25

### Changed

- Upgrade `symfony/cache` to `6.4.12` because of PHP Redis driver error

## [1.2.2] - 2023-11-28

- Fixed issue where resource display was not set when rebuilding user booking entry cache.
- Added "responseRequested" to booking invitations to fix issue with status.
- Fixed status when resource has not answered event invitation in accept flow.

## [1.2.1] - 2023-11-23

- Changed resource display name in notifications.
- Fixed remove from cache table on booking delete.
- Moved handling of delete/update user booking cache entry into messages.

## [1.2.0] - 2023-11-15

- Added User booking cache entity.
- Added User booking cache service.
- Added API for fetching cached user bookings.
- Removed Rabbit MQ (remeber to update the DSN in local .env)
- Updated docker compose setup to newest version
- Fixed issue with booking cache entries not being created.
- Added resourceDisplayName to cache.
- Moved AddBookingToCacheMessage handling to own queue.

## [1.1.2] - 2023-08-29

- Changed search query to only allow search in subject.

## [1.1.1] - 2023-08-24

- Changed user bookings to paginated

## [1.1.0]

- Added acceptConflict field to resource.
- Added tests for acceptConflict.
- Ignore deprecation warnings in production.
- Added commands related to user booking cache.
- Modified create booking code to allow conflicts if resource.acceptConflict is true.
- Updated symfony and dependencies.
- Changed how location email and name is found in user bookings.
- Set displayName from resource database when set.

## [1.0.4] - 2023-05-03

Added auto restart to queue container.

## [1.0.3] - 2023-04-20

- Updated resource entity with new schema.
- Updated docker setup to itk-version: 3.0.0
- Refactored message handling and notifications with better logging and retry logic

## [1.0.2] - 2023-02-06

- Add .ics timezone
- Added tests.
- Upgraded symfony/http-kernel [Security]
- Upgraded symfony/security-bundle [Security]
- Change .ics "to time"
- Change .ics description to use subject.
- Fixed timezone issues for emails.

## [1.0.1] - 2022-12-12

- Updated changelog

## [1.0.0] - 2022-12-12

- First release.
