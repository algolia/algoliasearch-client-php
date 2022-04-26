# api-clients-automation

**Make sure to have Docker installed so you don't have to install the tooling for every API clients.**

## Setup repository tooling

```bash
nvm use && yarn
```

## Setup dev environment

```bash
yarn docker:setup
```

[Read more on our documentation](https://api-clients-automation.netlify.app/docs/automation/setup-repository)

## Contributing

You can make changes locally and run commands through the docker container.

[Specs CLI commands](https://api-clients-automation.netlify.app/docs/automation/CLI/specs-commands) • [Clients CLI commands](https://api-clients-automation.netlify.app/docs/automation/CLI/clients-commands) • [CTS CLI commands](https://api-clients-automation.netlify.app/docs/automation/CLI/cts-commands)

### Build and validate specs

#### Usage

```bash
yarn docker build specs <client | all>
```

[Read more on our documentation](https://api-clients-automation.netlify.app/docs/automation/add-new-api-client)

### Generate clients based on the [`specs`](./specs/)

#### Usage

```bash
yarn docker generate <language | all> <client | all>
```

[Read more on our documentation](https://api-clients-automation.netlify.app/docs/automation/add-new-language)

## Testing clients

You can test our generated clients by running:

- The playground [`playground`](./playground) ([Playground](https://api-clients-automation.netlify.app/docs/automation/testing/playground.md))
- Tests with our [`Common Test Suite`](./tests/) ([Common Test Suite](https://api-clients-automation.netlify.app/docs/automation/testing/common-test-suite.md)).
