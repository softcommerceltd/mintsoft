# Magento - MintSoft Connector
Magento :: MintSoft :: Synchronisation

A plugin that integrates and synchronises data between Magento2 and MintSoft https://www.mintsoft.co.uk/

## Available integrations:
- Product export from Magento to MintSoft.

## Requirements
* [Magento 2.4](https://magento.com/tech-resources/download).
* [MintSoft account](https://www.mintsoft.co.uk/)
* PHP 7.4.0 or later

## Installation

### Install via FTP
* Download compressed file and unzip it.
* Login to your magento server and move to magento's app/code directory
`cd app/code && mkdir SoftCommerce && cd SoftCommerce && mkdir MintSoft` to create new directory app/code/SoftCommerce/MintSoft
* Upload contents to app/code/SoftCommerce/MintSoft directory.
* Move back to your magento root directory and execute "Post Installation" commands described bellow.

### Install via composer

Run the following command from Magento root directory:

```
composer config repositories.softcommerce-mintsoft vcs https://github.com/softcommerceltd/mintsoft.git
composer require softcommerce/module-mintsoft
```
If you receive an error regarding php incompatibility, but you are sure your php version is compatible, then use --ignore-platform-reqs
```
composer require softcommerce/module-mintsoft --ignore-platform-reqs
```

### Post Installation

In production mode:
```
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento deploy:mode:set production
php bin/magento maintenance:disable
```

In development mode:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

## License
Each source file included in this package is licensed under OSL 3.0.

[Open Software License (OSL 3.0)](https://opensource.org/licenses/osl-3.0.php).
Please see `LICENSE.txt` for full details of the OSL 3.0 license.

## Thanks for dropping by

<p align="center">
    <a href="https://magento.com">
        <img src="https://softcommerce.co.uk/pub/media/banner/logo.svg" width="200" alt="Soft Commerce Ltd" />
    </a>
    <br />
    <a href="https://softcommerce.co.uk/">
        https://softcommerce.co.uk/
    </a>
</p>




