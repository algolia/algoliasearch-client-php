# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

# Release Notes

## [Unreleased](https://github.com/algolia/algoliasearch-client-php/compare/3.3.2...master)

### Added

- Include input string in message about invalid json ([#711](https://github.com/algolia/algoliasearch-client-php/pull/711))

## [v3.3.2](https://github.com/algolia/algoliasearch-client-php/compare/3.3.1...3.3.2)

### Fixed
- Add phpdoc regarding return types ([#710](https://github.com/algolia/algoliasearch-client-php/pull/710))

## [v3.3.1](https://github.com/algolia/algoliasearch-client-php/compare/3.3.0...3.3.1)

### Fixed
- Fix class not found error ([#708](https://github.com/algolia/algoliasearch-client-php/pull/708))

## [v3.3.0](https://github.com/algolia/algoliasearch-client-php/compare/3.2.0...3.3.0)

### Changed
- Use interface for constructor arguments ([#704](https://github.com/algolia/algoliasearch-client-php/pull/704))

### Fixed
- Corrected saveObjects call example ([#702](https://github.com/algolia/algoliasearch-client-php/pull/702))
- Fixes deprecation alert when using symfony/error-handler ([#703](https://github.com/algolia/algoliasearch-client-php/pull/703))

## [v3.2.0](https://github.com/algolia/algoliasearch-client-php/compare/3.1.0...3.2.0)

### Added
- Add #[\ReturnTypeWillChange] when needed for PHP 8.1 compatibility ([#697](https://github.com/algolia/algoliasearch-client-php/pull/697))

### Changed
- chore: move to newer CircleCI image ([#688](https://github.com/algolia/algoliasearch-client-php/pull/688))
- chore: add PHP 8.1 image check in the CircleCI workflow ([#699](https://github.com/algolia/algoliasearch-client-php/pull/699))

### Fixed
- Fix Psr log ([#696](https://github.com/algolia/algoliasearch-client-php/pull/696))
- Allow newer version of psr/simple-cache ([#698](https://github.com/algolia/algoliasearch-client-php/pull/698))

## [v3.1.0](https://github.com/algolia/algoliasearch-client-php/compare/3.0.2...3.1.0)

### Added
- Add search alias for multiple queries ([#684](https://github.com/algolia/algoliasearch-client-php/pull/684))
- Add RecommendClient class ([#686](https://github.com/algolia/algoliasearch-client-php/pull/686))

### Changed
- Rename recommendation client ([#682](https://github.com/algolia/algoliasearch-client-php/pull/682))

### Fixed
-  Pin log dependency ([#685](https://github.com/algolia/algoliasearch-client-php/pull/685))

## [v3.0.2](https://github.com/algolia/algoliasearch-client-php/compare/3.0.1...3.0.2)

### Fixed
- Use ::class instead of string for class name ([#667](https://github.com/algolia/algoliasearch-client-php/pull/667))

## [v3.0.1](https://github.com/algolia/algoliasearch-client-php/compare/3.0.0...3.0.1)

### Fixed
- Remove invalid method call ([#675](https://github.com/algolia/algoliasearch-client-php/pull/675))

## [v3.0.0](https://github.com/algolia/algoliasearch-client-php/compare/2.8.0...3.0.0)

### Changed
- Major version - Drops support for PHP < 7.2

## [v2.8.0](https://github.com/algolia/algoliasearch-client-php/compare/2.7.3...2.8.0)

### Fix
- Add missing part of the url for stopABTest() method ([#666](https://github.com/algolia/algoliasearch-client-php/pull/666))
- Handling of params array in the $queries array for multipleQueries method ([#663](https://github.com/algolia/algoliasearch-client-php/pull/663))

### Added
- Custom Dictionaries feature ([#662](https://github.com/algolia/algoliasearch-client-php/pull/662))

## [v2.7.3](https://github.com/algolia/algoliasearch-client-php/compare/2.7.2...2.7.3)

### Chore
- Support PHP 8
- Use correct en variables for forks

## [v2.7.2](https://github.com/algolia/algoliasearch-client-php/compare/2.7.1...2.7.2)

### Chore
- Containerize the repo

## [v2.7.1](https://github.com/algolia/algoliasearch-client-php/compare/2.7.0...2.7.1)

### Fix
- Enable `JSON_UNESCAPED_UNICODE` option for requests bodies JSON encoding. 

## [v2.7.0](https://github.com/algolia/algoliasearch-client-php/compare/2.6.2...2.7.0)

### Added
- Support of Guzzle 7 ([#627](https://github.com/algolia/algoliasearch-client-php/pull/627))

## [v2.6.2](https://github.com/algolia/algoliasearch-client-php/compare/2.6.1...2.6.2)

### Changed
- Updated tests to accommodate engine response ([#626](https://github.com/algolia/algoliasearch-client-php/pull/626))

## [v2.6.1](https://github.com/algolia/algoliasearch-client-php/compare/2.6.0...2.6.1)

### Fixed
- wrong deserialization of 100 status code ([#620](https://github.com/algolia/algoliasearch-client-php/pull/620))
- name of `getTopUserIds` method ([#616](https://github.com/algolia/algoliasearch-client-php/pull/616))

## [v2.6.0](https://github.com/algolia/algoliasearch-client-php/compare/2.5.1...2.6.0)

### Added
- Method `RecommendationClient.setPersonalizationStrategy` and method `RecommendationClient.getPersonalizationStrategy` ([#600](https://github.com/algolia/algoliasearch-client-php/pull/600))
- Method `SearchClient.hasPendingMappings` ([#599](https://github.com/algolia/algoliasearch-client-php/pull/599))
- Method `SearchClient.assignUserIds` ([#610](https://github.com/algolia/algoliasearch-client-php/pull/610))

### Changed
- Deprecates method `SearchClient.setPersonalizationStrategy` and method `SearchClient.getPersonalizationStrategy` ([#600](https://github.com/algolia/algoliasearch-client-python/pull/600))

## [v2.5.1](https://github.com/algolia/algoliasearch-client-php/compare/2.5.0...2.5.1)

### Fixed
- Serialization issue on `saveRule` and `saveRules` when rule contains an empty list of consequence params ([#606](https://github.com/algolia/algoliasearch-client-php/pull/606))

## [v2.5.0](https://github.com/algolia/algoliasearch-client-php/compare/2.4.0...2.5.0)

### Added
- Make Logger injectacle in ApiWrapper ([#593](https://github.com/algolia/algoliasearch-client-php/pull/593))

### Fixed
- Type information in `HttpClientInterface:sendRequest` ([#594](https://github.com/algolia/algoliasearch-client-php/pull/594))

### Chore
- Composer update and CS fix ([#595](https://github.com/algolia/algoliasearch-client-php/pull/595))

## [v2.4.0](https://github.com/algolia/algoliasearch-client-php/compare/2.3.0...2.4.0)

### Added
- `SearchClient::getSecuredApiKeyRemainingValidity` method ([#581](https://github.com/algolia/algoliasearch-client-php/pull/581))
- `SearchIndex::findObject` and `SearchIndex::getObjectPosition` methods ([#579](https://github.com/algolia/algoliasearch-client-php/pull/579))

### Fixed
- Adds missing `requestOptions` to `SearchIndex::exists` ([#582](https://github.com/algolia/algoliasearch-client-php/pull/582))

## [v2.3.0](https://github.com/algolia/algoliasearch-client-php/compare/2.2.6...2.3.0)

### Added
- `SearchClient::exists` method ([#565](https://github.com/algolia/algoliasearch-client-php/pull/565))

### Fixed
- Retry strategy bug while using `Guzzle` requester: `cURL error XX: Failed to connect` ([#572](https://github.com/algolia/algoliasearch-client-php/pull/572))

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
