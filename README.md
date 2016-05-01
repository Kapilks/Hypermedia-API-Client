# Hypermedia-API-Client
> Hypermedia API Client written in PHP

## Installation

* Install Composer
```
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

* Create `composer.json` file with following content
```json
{
	"require": {
		"kapilks/hyper-media-client": "dev-master"
	}
}
```

* Install API Client
```
composer install
```

* After installing require composer's autoloader in your `php` script
```php
require_once __DIR__. '/vendor/autoload.php';
```