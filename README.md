<p align="center">
  <a href="https://www.algolia.com">
    <img alt="Algolia for PHP" src="https://raw.githubusercontent.com/algolia/algoliasearch-client-common/master/readme-banner.png" >
  </a>

  <h4 align="center">The perfect starting point to integrate <a href="https://algolia.com" target="_blank">Algolia</a> within your PHP project</h4>

  <p align="center">
    <a href="https://travis-ci.org/algolia/algoliasearch-client-php"><img src="https://img.shields.io/travis/algolia/algoliasearch-client-php/master.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/v/stable.svg" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/algolia/algoliasearch-client-php"><img src="https://poser.pugx.org/algolia/algoliasearch-client-php/license.svg" alt="License"></a>
  </p>
</p>

<p align="center">
  <a href="https://www.algolia.com/doc/api-client/getting-started/install/php/" target="_blank">Documentation</a>  •
  <a href="https://github.com/algolia/scout-extended" target="_blank">Laravel</a>  •
  <a href="https://github.com/algolia/search-bundle" target="_blank">Symfony</a>  •
  <a href="https://discourse.algolia.com" target="_blank">Community Forum</a>  •
  <a href="http://stackoverflow.com/questions/tagged/algolia" target="_blank">Stack Overflow</a>  •
  <a href="https://github.com/algolia/algoliasearch-client-php/issues" target="_blank">Report a bug</a>  •
  <a href="https://www.algolia.com/support" target="_blank">Support</a>
</p>

## ✨ Features

- Thin & minimal low-level HTTP client to interact with Algolia's API
- Supports php `^5.3`.

## 💡 Getting Started

First, install Algolia PHP API Client via the [composer](https://getcomposer.org/) package manager:
```bash
composer require algolia/algoliasearch-client-php
```

Then, create objects on your index:
```php
$client = Algolia\AlgoliaSearch\SearchClient::create(
  'YourApplicationID',
  'YourAdminAPIKey'
);

$index = $client->initIndex('your_index_name');

$index->saveObjects(['objectID' => 1, 'name' => 'Foo']);
```

Finally, you may begin searching a object using the `search` method:
```py
objects = $index->search('Fo');
```

For full documentation, visit the **[Algolia PHP API Client](https://www.algolia.com/doc/api-client/getting-started/install/php/)**.

## 📄 License

Algolia PHP API Client is an open-sourced software licensed under the [MIT license](LICENSE.md).
