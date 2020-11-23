# 1&1 Transaction Mail Extender
Extends Shopware 6 transaction mails with a Schema.org conform HTML content

## Requirements
* Shopware 6.2 / 6.3
* PHP 7.3 / 7.4

## Installation
Require the plugin via composer
`composer require flagbit/shopware6-transaction-mail-extender`

Refresh the plugin list to load and see if the plugin is now available for your Shopware installation.
`bin/console plugin:refresh`

If the plugin is known, install and activate the plugin
`bin/console plugin:install --activate --clearCache TransactionMailExtender`
