# WooCommerce NFT Downloads

![hero](./docs/images/hero.png)

WordPress plugin that makes it possible to give customers access to product downloads by checking that customers own a certain NFT. Limited user documentation available in docs/.

## Quick setup for testing and development

This is a description for how to setup a local server and other tools needed to try out or develop this plugin.

Requirements

- Docker and Docker Compose
- Node.js v16 (if you have `nvm`, run `nvm use`)
- Composer

After cloning the repository, run the following command to install npm and composer dependencies

```
node ./scripts/setup.js
```

Start the server

```
npm run start
```

You should be able to access the site on `localhost:8888` and the plugin will be installed. See user documentation in `docs/` for usage instructions.

## Automated testing

- `phpunit/` - Unit tests
- `specs/` - E2E tests

Commands for running tests:

- `npm run test:php` PHP unit tests
- `npm run test:e2e` E2E tests
- `npm run test:php:api` PHP unit tests that do remote API requests instead of using stubs. Listing an Ethereum node URL is required. See [Remote API testing](#remote-api-testing).

### Remote API testing

In order for Remote API tests to work, you need to be able to communicate to an Ethereum node and have its URL. You could use a service like [Alchemy](https://www.alchemy.com/) to set this up.

Then, rename `secrets.php.example` to `secrets.php` and fill in the `eth_api_url` value. Now it should be possible to run remote API tests.

## Other commands

Make sure you install dependencies before running any of these commands:

```
node ./scripts/setup.js
```

### Generate zip

```
./scripts/helper.js zip
```
