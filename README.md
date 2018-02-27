# Algolia Search API Client for PHP

[Algolia Search](https://www.algolia.com) is a hosted full-text, numerical,
and faceted search engine capable of delivering realtime results from the first keystroke.

The **Algolia Search API Client for PHP** lets
you easily use the [Algolia Search REST API](https://www.algolia.com/doc/rest-api/search) from
your PHP code.

[![Build Status](https://travis-ci.org/algolia/algoliasearch-client-php.svg?branch=master)](https://travis-ci.org/algolia/algoliasearch-client-php) [![Latest Stable Version](https://poser.pugx.org/algolia/algoliasearch-client-php/v/stable.svg)](https://packagist.org/packages/algolia/algoliasearch-client-php) [![Coverage Status](https://coveralls.io/repos/algolia/algoliasearch-client-php/badge.svg)](https://coveralls.io/r/algolia/algoliasearch-client-php)


If you're a Symfony or Laravel user, you're probably looking for the following integrations:

- **Laravel**: [Laravel Scout](/doc/api-client/laravel/algolia-and-scout/)
- **Symfony**: [algolia/AlgoliaSearchBundle](https://github.com/algolia/AlgoliaSearchBundle)




## API Documentation

You can find the full reference on [Algolia's website](https://www.algolia.com/doc/api-client/php/).


## Table of Contents



1. **[Install](#install)**


1. **[Quick Start](#quick-start)**


1. **[Push data](#push-data)**


1. **[Configure](#configure)**


1. **[Search](#search)**


1. **[Search UI](#search-ui)**


1. **[List of available methods](#list-of-available-methods)**


# Getting Started



## Install

### With composer (Recommended)

Install the package via [Composer](https://getcomposer.org/doc/00-intro.md):

```bash
composer require algolia/algoliasearch-client-php
```

### Without composer

If you don't use Composer, you can download the [package](https://github.com/algolia/algoliasearch-client-php/archive/master.zip) and include it in your code.

```php
require_once('algoliasearch-client-php-master/algoliasearch.php');
```

### Framework Integrations

We officially provide support for the **Laravel** and **Symfony** frameworks:

If you are using one of those two frameworks have a look at our
[Laravel documentation](https://www.algolia.com/doc/api-client/laravel/algolia-and-scout/) or [Symfony documentation](https://www.algolia.com/doc/api-client/symfony/getting-started/)

## Quick Start

In 30 seconds, this quick start tutorial will show you how to index and search objects.

### Initialize the client

To begin, you will need to initialize the client. In order to do this you will need your **Application ID** and **API Key**.
You can find both on [your Algolia account](https://www.algolia.com/api-keys).

```php
// composer autoload
require __DIR__ . '/vendor/autoload.php';

// if you are not using composer
// require_once 'path/to/algoliasearch.php';

$client = new \AlgoliaSearch\Client('YourApplicationID', 'YourAdminAPIKey');

$index = $client->initIndex('your_index_name');
```

## Push data

Without any prior configuration, you can start indexing [500 contacts](https://github.com/algolia/datasets/blob/master/contacts/contacts.json) in the ```contacts``` index using the following code:

```php
$index = $client->initIndex('contacts');
$batch = json_decode(file_get_contents('contacts.json'), true);
$index->addObjects($batch);
```

## Configure

Settings can be customized to fine tune the search behavior. For example, you can add a custom sort by number of followers to further enhance the built-in relevance:

```php
$index->setSettings(['customRanking' => ['desc(followers)']]);
```

You can also configure the list of attributes you want to index by order of importance (most important first).

**Note:** The Algolia engine is designed to suggest results as you type, which means you'll generally search by prefix.
In this case, the order of attributes is very important to decide which hit is the best:

```php
$index->setSettings(
    [
        'searchableAttributes' => [
            'lastname',
            'firstname',
            'company',
            'email',
            'city',
            'address'
        ]
    ]
);
```

## Search

You can now search for contacts using `firstname`, `lastname`, `company`, etc. (even with typos):

```php
// search by firstname
var_dump($index->search('jimmie'));

// search a firstname with typo
var_dump($index->search('jimie'));

// search for a company
var_dump($index->search('california paint'));

// search for a firstname & company
var_dump($index->search('jimmie paint'));
```

## Search UI

**Warning:** If you are building a web application, you may be more interested in using one of our
[frontend search UI libraries](https://www.algolia.com/doc/guides/search-ui/search-libraries/)

The following example shows how to build a front-end search quickly using
[InstantSearch.js](https://community.algolia.com/instantsearch.js/)

### index.html

```html
<!doctype html>
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css">
  <!-- Always use `2.x` versions in production rather than `2` to mitigate any side effects on your website,
  Find the latest version on InstantSearch.js website: https://community.algolia.com/instantsearch.js/v2/guides/usage.html -->
</head>
<body>
  <header>
    <div>
       <input id="search-input" placeholder="Search for products">
       <!-- We use a specific placeholder in the input to guides users in their search. -->
    
  </header>
  <main>
      
      
  </main>

  <script type="text/html" id="hit-template">
    
      <p class="hit-name">{{{_highlightResult.firstname.value}}} {{{_highlightResult.lastname.value}}}</p>
    
  </script>

  <script src="https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js"></script>
  <script src="app.js"></script>
</body>
```

### app.js

```js
var search = instantsearch({
  // Replace with your own values
  appId: 'YourApplicationID',
  apiKey: 'YourSearchOnlyAPIKey', // search only API key, no ADMIN key
  indexName: 'contacts',
  urlSync: true,
  searchParameters: {
    hitsPerPage: 10
  }
});

search.addWidget(
  instantsearch.widgets.searchBox({
    container: '#search-input'
  })
);

search.addWidget(
  instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: document.getElementById('hit-template').innerHTML,
      empty: "We didn't find any results for the search <em>\"{{query}}\"</em>"
    }
  })
);

search.start();
```




## List of available methods







### Search

- [Search an index](https://algolia.com/doc/api-reference/api-methods/search/?language=php)
- [Search for facet values](https://algolia.com/doc/api-reference/api-methods/search-for-facet-values/?language=php)
- [Search multiple indexes](https://algolia.com/doc/api-reference/api-methods/multiple-queries/?language=php)
- [Browse an index](https://algolia.com/doc/api-reference/api-methods/browse/?language=php)





### Indexing

- [Add objects](https://algolia.com/doc/api-reference/api-methods/add-objects/?language=php)
- [Update objects](https://algolia.com/doc/api-reference/api-methods/update-objects/?language=php)
- [Partial update objects](https://algolia.com/doc/api-reference/api-methods/partial-update-objects/?language=php)
- [Delete objects](https://algolia.com/doc/api-reference/api-methods/delete-objects/?language=php)
- [Delete by query](https://algolia.com/doc/api-reference/api-methods/delete-by-query/?language=php)
- [Get objects](https://algolia.com/doc/api-reference/api-methods/get-objects/?language=php)
- [Custom batch](https://algolia.com/doc/api-reference/api-methods/batch/?language=php)
- [Wait for operations](https://algolia.com/doc/api-reference/api-methods/wait-task/?language=php)





### Settings

- [Get settings](https://algolia.com/doc/api-reference/api-methods/get-settings/?language=php)
- [Set settings](https://algolia.com/doc/api-reference/api-methods/set-settings/?language=php)





### Manage indices

- [List indexes](https://algolia.com/doc/api-reference/api-methods/list-indices/?language=php)
- [Delete index](https://algolia.com/doc/api-reference/api-methods/delete-index/?language=php)
- [Copy index](https://algolia.com/doc/api-reference/api-methods/copy-index/?language=php)
- [Move index](https://algolia.com/doc/api-reference/api-methods/move-index/?language=php)
- [Clear index](https://algolia.com/doc/api-reference/api-methods/clear-index/?language=php)





### API Keys

- [Create secured API Key](https://algolia.com/doc/api-reference/api-methods/generate-secured-api-key/?language=php)
- [Add API Key](https://algolia.com/doc/api-reference/api-methods/add-api-key/?language=php)
- [Update API Key](https://algolia.com/doc/api-reference/api-methods/update-api-key/?language=php)
- [Delete API Key](https://algolia.com/doc/api-reference/api-methods/delete-api-key/?language=php)
- [Get API Key permissions](https://algolia.com/doc/api-reference/api-methods/get-api-key/?language=php)
- [List API Keys](https://algolia.com/doc/api-reference/api-methods/list-api-keys/?language=php)





### Synonyms

- [Save synonym](https://algolia.com/doc/api-reference/api-methods/save-synonym/?language=php)
- [Batch synonyms](https://algolia.com/doc/api-reference/api-methods/batch-synonyms/?language=php)
- [Delete synonym](https://algolia.com/doc/api-reference/api-methods/delete-synonym/?language=php)
- [Clear all synonyms](https://algolia.com/doc/api-reference/api-methods/clear-synonyms/?language=php)
- [Get synonym](https://algolia.com/doc/api-reference/api-methods/get-synonym/?language=php)
- [Search synonyms](https://algolia.com/doc/api-reference/api-methods/search-synonyms/?language=php)
- [Export Synonyms](https://algolia.com/doc/api-reference/api-methods/export-synonyms/?language=php)





### Query rules

- [Save a single rule](https://algolia.com/doc/api-reference/api-methods/rules-save/?language=php)
- [Batch save multiple rules](https://algolia.com/doc/api-reference/api-methods/rules-save-batch/?language=php)
- [Read a rule](https://algolia.com/doc/api-reference/api-methods/rules-read/?language=php)
- [Delete a single rule](https://algolia.com/doc/api-reference/api-methods/rules-delete/?language=php)
- [Clear all rules](https://algolia.com/doc/api-reference/api-methods/rules-clear/?language=php)
- [Search for rules](https://algolia.com/doc/api-reference/api-methods/rules-search/?language=php)
- [Export rules](https://algolia.com/doc/api-reference/api-methods/rules-export/?language=php)





### MultiClusters

- [Assign or Move userID](https://algolia.com/doc/api-reference/api-methods/assign-user-id/?language=php)
- [Get top userID](https://algolia.com/doc/api-reference/api-methods/get-top-user-id/?language=php)
- [Get userID](https://algolia.com/doc/api-reference/api-methods/get-user-id/?language=php)
- [List clusters](https://algolia.com/doc/api-reference/api-methods/list-clusters/?language=php)
- [List userID](https://algolia.com/doc/api-reference/api-methods/list-user-id/?language=php)
- [Remove userID](https://algolia.com/doc/api-reference/api-methods/remove-user-id/?language=php)
- [Search userID](https://algolia.com/doc/api-reference/api-methods/search-user-id/?language=php)





### Advanced

- [Get latest logs](https://algolia.com/doc/api-reference/api-methods/get-logs/?language=php)
- [Set extra header](https://algolia.com/doc/api-reference/api-methods/set-extra-header/?language=php)





## Getting Help

- **Need help**? Ask a question to the [Algolia Community](https://discourse.algolia.com/) or on [Stack Overflow](http://stackoverflow.com/questions/tagged/algolia).
- **Found a bug?** You can open a [GitHub issue](https://github.com/algolia/algoliasearch-client-php/issues).

