#Badgeville Cairo SDK for PHP
==============

version 0.1

This ia a work in progress and lots of things will be changing still. Hopefully it 
won't take too long to get a beta ready. The idea is to provide a library that is 
easy to use to interact with the Cairo API. All that's needed to get started is 

##Usage 
keep in mind functionality will probably change until beta

To install via composer:
```json
{
    "require": {
        "joeyrivera/badgeville-php": "dev-master"
    }
}
```

First need to create a site instance. You can get an example config file in examples/config.php.dist:

```php
return [
    'url' => 'https://sandbox.badgeville.com/cairo',
    'apiVersion' => 'v1',
    'apiKey' => '234lkj23l4kj23l4l2j34lk23j4lk23l4', // get this from your badgeville dashboard
    'siteId' => '23k4lj23kl4j23lkj4', // get this from your badgeville dashboard
];
```

```php
use Badgeville\Site;
$site = new Site(require_once 'config.php');
```

Now you can make all site specific calls like:

```php
$player = $site->players()->find('joey@rivera.com');

$player = $site->players()->find('234lkj234lkj234lkj', [
    'includes' => 'rewards,positions,activities'
]);

$players = $site->players()->findAll();

$player = $site->players()->create([
    'name' => 'Joey Tester2',
    'email' => 'joeyrivera@air-watch.com2'
]);

$player->display_name = 'testing2';
$player->save();
```

@todos
collections
pagination