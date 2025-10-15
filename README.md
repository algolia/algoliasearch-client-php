<p align="center">
  <a href="https://www.algolia.com">
    <img alt="Algolia for PHP" src="https://raw.githubusercontent.com/algolia/algoliasearch-client-common/master/banners/php.png" >
  </a>

  <h4 align="center">The perfect starting point to integrate <a href="https://algolia.com" target="_blank">Algolia</a> within your PHP project</h4>

  <p align="center">
    <a href="https://circleci.com/gh/algolia/algoliasearch-client-php"><img src="https://circleci.com/gh/algolia/algoliasearch-client-php.svg?style=shield" alt="CircleCI" /></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/v/stable.svg" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/license.svg" alt="License"></a>
  </p>
</p>

<p align="center">
  <a href="https://www.algolia.com/doc/libraries/sdk/install#php" target="_blank">Documentation</a>  •
  <a href="https://github.com/algolia/scout-extended" target="_blank">Laravel</a>  •
  <a href="https://github.com/algolia/search-bundle" target="_blank">Symfony</a>  •
  <a href="https://discourse.algolia.com" target="_blank">Community Forum</a>  •
  <a href="http://stackoverflow.com/questions/tagged/algolia" target="_blank">Stack Overflow</a>  •
  <a href="https://github.com/algolia/algoliasearch-client-php/issues" target="_blank">Report a bug</a>  •
  <a href="https://www.algolia.com/support" target="_blank">Support</a>
</p>

## ✨ Features

- Thin & minimal low-level HTTP client to interact with Algolia's API
- Supports php `^8.0`.

## 💡 Getting Started

First, install Algolia PHP API Client via the [composer](https://getcomposer.org/) package manager:

```bash
composer require algolia/algoliasearch-client-php "^4.0"
```

You can now import the Algolia API client in your project and play with it.

```php
use Algolia\AlgoliaSearch\Api\SearchClient;

$client = SearchClient::create('<YOUR_APP_ID>', '<YOUR_API_KEY>');

// Add a new record to your Algolia index
$response = $client->saveObject(
    '<YOUR_INDEX_NAME>',
    ['objectID' => 'id',
        'test' => 'val',
    ],
);

// play with the response
var_dump($response);

// Poll the task status to know when it has been indexed
$client->waitForTask('<YOUR_INDEX_NAME>', $response['taskID']);

// Fetch search results, with typo tolerance
$response = $client->search(
    ['requests' => [
        ['indexName' => '<YOUR_INDEX_NAME>',
            'query' => '<YOUR_QUERY>',
            'hitsPerPage' => 50,
        ],
    ],
    ],
);

// play with the response
var_dump($response);
```

For full documentation, visit the **[Algolia PHP API Client](https://www.algolia.com/doc/libraries/sdk/install#php)**.

## ❓ Troubleshooting

Encountering an issue? Before reaching out to support, we recommend heading to our [FAQ](https://support.algolia.com/hc/sections/15061037630609-API-Client-FAQs) where you will find answers for the most common issues and gotchas with the client. You can also open [a GitHub issue](https://github.com/algolia/api-clients-automation/issues/new?assignees=&labels=&projects=&template=Bug_report.md)

## Contributing

This repository hosts the code of the generated Algolia API client for PHP, if you'd like to contribute, head over to the [main repository](https://github.com/algolia/api-clients-automation). You can also find contributing guides on [our documentation website](https://api-clients-automation.netlify.app/docs/introduction).

## 📄 License

The Algolia PHP API Client is an open-sourced software licensed under the [MIT license](LICENSE).
