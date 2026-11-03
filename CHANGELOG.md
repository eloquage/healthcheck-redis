# Changelog

All notable changes to `healthcheck-redis` will be documented in this file.

## v0.1.6 - 2026-11-03

Release the package with Composer identity under the eloquage vendor namespace. This release contains the package metadata and documentation rename; runtime behavior is unchanged.

## 0.1.5 - 2026-10-23

- Support Laravel 12 and 13 on PHP 8.3+.
- Implement Redis memory warning thresholds and fail at or above `maxmemory`.
- Add the `connection()` fluent alias and validate threshold inputs.
- Add package banner artwork and concise installation and usage examples.
