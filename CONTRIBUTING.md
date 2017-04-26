## Contributing

Hi there! We're thrilled that you'd like to contribute to this project.
Your help is essential for keeping it great.

## Submitting a pull request

0. [Fork](https://github.com/algolia/algoliasearch-client-php/fork) and clone the repository
0. Configure and install the dependencies: `composer install`
0. Make sure the tests pass on your machine via the section [Running the tests](#running-the-tests)
0. Create a new branch: `git checkout -b my-branch-name`
0. Make your change, add tests, and make sure the tests still pass
0. Push to your fork and [submit a pull request](https://github.com/algolia/algoliasearch-client-php/compare)
0. Pat your self on the back and wait for your pull request to be reviewed and merged.

__Do not worry about the tests failing on PR, it is expected given we have no way of sharing private env variable from Travis.__

Here are a few things you can do that will increase the likelihood of your pull request being accepted:

- Write tests.
- Keep your change as focused as possible. If there are multiple changes you would like to make that are not dependent upon each other, consider submitting them as separate pull requests.
- Write a [good commit message](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).

## Running the tests

Add theses two environments variables from [API Keys](https://www.algolia.com/api-keys):

* ALGOLIA_APPLICATION_ID=`Application ID`
* ALGOLIA_API_KEY=`Admin API Key`

Then run:

```bash
php vendor/bin/phpunit
```

Install [Xdebug](https://xdebug.org/), if you have a message like this:

```
Error:         No code coverage driver is available
```

## Resources

- [Contributing to Open Source on GitHub](https://guides.github.com/activities/contributing-to-open-source/)
- [Using Pull Requests](https://help.github.com/articles/using-pull-requests/)
- [GitHub Help](https://help.github.com)
