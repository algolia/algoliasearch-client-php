In this page you will find our recommended way of installing Docker on your machine. 
This guide is made for OSX users.

## Install docker

First install Docker using [Homebrew](https://brew.sh/)
```
brew install docker
```

You can then install [Docker Desktop](https://docs.docker.com/get-docker/) if you wish, or use `docker-machine`. As we prefer the second option, we will only document this one.

## Setup your docker

Install `docker-machine`
```
brew install docker-machine
```

Then install [VirtualBox](https://www.virtualbox.org/) with [Homebrew Cask](https://github.com/Homebrew/homebrew-cask) to get a driver for your Docker machine
```
brew install virtualbox --cask
```

You may need to enter your password and authorize the application in your `System Settings` > `Security & Privacy`.

Create now a new machine, set it up as default and connect your shell to it (here we use zsh. The commands should anyway be displayed in each steps' output)

```
docker-machine create --driver virtualbox default
docker-machine env default
eval "$(docker-machine env default)"
```

Now you're all setup to use our provided Docker image!

## Build the image

```bash
docker build -t algolia-php .
```

## Run the image

You need to provide few environment variables at runtime to be able to run the [Common Test Suite](https://github.com/algolia/algoliasearch-client-specs/tree/master/common-test-suite).
You can set them up directly in the command:

```bash
docker run -it --rm --env ALGOLIA_APP_ID=XXXXXX [...] -v $PWD:/app -w /app algolia-php bash
```

However, we advise you to export them in your `.bashrc` or `.zshrc`. That way, you can use [Docker's shorten syntax](https://docs.docker.com/engine/reference/commandline/run/#set-environment-variables--e---env---env-file) to set your variables.

```bash
docker run -it --rm --env ALGOLIA_APP_ID \
                    --env ALGOLIA_API_KEY \
-v $PWD:/app -w /app algolia-php bash

### This is needed only to run the full test suite
docker run -it --rm --env ALGOLIA_APP_ID \
                    --env ALGOLIA_API_KEY \
                    --env ALGOLIA_APPLICATION_ID_MCM \
                    --env ALGOLIA_ADMIN_KEY_MCM \
-v $PWD:/app -w /app algolia-php bash
```

Once your container is running, any changes you make in your IDE are directly reflected in the container.

To launch the tests, you can use one of the following commands
```shell script
# run only the unit tests
./vendor/bin/phpunit

# run a single test
./vendor/bin/phpunit --filter=nameOfYourTests
```

You can find more commands in the `composer.json` file.

Feel free to contact us if you have any questions.

