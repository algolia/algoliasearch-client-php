# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

# Release Notes

## [Unreleased](https://github.com/algolia/algoliasearch-client-php/compare/2.2.6...master)

### Added
- `SearchClient::exists` method ([#565](https://github.com/algolia/algoliasearch-client-php/pull/565))

## [v2.2.6](https://github.com/algolia/algoliasearch-client-php/compare/2.2.5...2.2.6)

### Fixed
- Syntax error in old php versions ([#548](https://github.com/algolia/algoliasearch-client-php/pull/548))

## [v2.2.5](https://github.com/algolia/algoliasearch-client-php/compare/2.2.4...2.2.5)

### Fixed
- Usage of non-supported guzzle versions ([#544](https://github.com/algolia/algoliasearch-client-php/pull/544))

## [v2.2.4](https://github.com/algolia/algoliasearch-client-php/compare/2.2.3...2.2.4)

### Added
- `SearchIndex::searchForFacetValues` method, and deprecates `SearchIndex::searchForFacetValue` ([#523](https://github.com/algolia/algoliasearch-client-php/pull/523))

### Fixed
- Missing autoload in `bin/algolia-doctor` ([#534](https://github.com/algolia/algoliasearch-client-php/pull/534))
- Issue when manually installing dependencies within a folder name with spaces ([#540](https://github.com/algolia/algoliasearch-client-php/pull/540))

## [v2.2.3](https://github.com/algolia/algoliasearch-client-php/compare/2.2.2...2.2.3)

### Fixed
- Reverts `Adds missing optional certificate` ([198f111](https://github.com/algolia/algoliasearch-client-php/commit/198f111ab5d1cabba08ea20e70632b75bc9e6f16))

## [v2.2.2](https://github.com/algolia/algoliasearch-client-php/compare/2.2.1...2.2.2)

### Fixed
- Adds missing optional certificate ([#521](https://github.com/algolia/algoliasearch-client-php/pull/521))

## [v2.2.1](https://github.com/algolia/algoliasearch-client-php/compare/2.2.0...2.2.1)

### Fixed
- Missing iterator key increment ([#515](https://github.com/algolia/algoliasearch-client-php/pull/515))

## [v2.2.0](https://github.com/algolia/algoliasearch-client-php/compare/2.1.3...2.2.0)

### Added
- `SearchClient::restoreApiKey` method ([#502](https://github.com/algolia/algoliasearch-client-php/pull/502))

### Fixed
- Autoload when not using composer ([#506](https://github.com/algolia/algoliasearch-client-php/pull/506))

## [v2.1.3](https://github.com/algolia/algoliasearch-client-php/compare/2.1.2...2.1.3)

### Fixed
- Default write timeout - changed from `5s` to `30s` ([#505](https://github.com/algolia/algoliasearch-client-php/pull/505))

## [v2.1.2](https://github.com/algolia/algoliasearch-client-php/compare/2.1.1...2.1.2)

### Fixed
- Adds `null` resilience to search `$query` ([#499](https://github.com/algolia/algoliasearch-client-php/pull/499))

## [v2.1.1](https://github.com/algolia/algoliasearch-client-php/compare/2.1.0...2.1.1)

### Fixed
- `multipleBatch` should not check for objectIDs in the list of operations ([#488](https://github.com/algolia/algoliasearch-client-php/pull/488))

## [v2.1.0](https://github.com/algolia/algoliasearch-client-php/compare/2.0.1...2.1.0)

### Added
- Personalization related methods ([#493](https://github.com/algolia/algoliasearch-client-php/pull/493))
- Insights Client related methods ([#487](https://github.com/algolia/algoliasearch-client-php/pull/487))

## [v2.0.1](https://github.com/algolia/algoliasearch-client-php/compare/2.0.0...2.0.1)

### Fixed
- Usage of `wait` method on empty batches ([#491](https://github.com/algolia/algoliasearch-client-php/pull/491))

## [v2.0.0](https://github.com/algolia/algoliasearch-client-php/compare/1.28.0...2.0.0)

### Changed
- Major version - [Upgrade Guide](https://github.com/algolia/algoliasearch-client-php/blob/2.0.0/docs/UPGRADE-from-v1-to-v2.md)
