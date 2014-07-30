#Badgeville Cairo SDK for PHP
==============

version 0.1

This ia a work in progress and lots of things will be changing still. Hopefully it 
won't take too long to get a beta ready. The idea is to provide a library that is 
easy to use to interact with the Cairo API. 

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

The best way to get started is to test the find/findall utility under examples/index.php 
To use, create a copy of examples/config.php.dist and save it as examples/config.php. Next 
fill out the array so it looks similar to:

```php
return [
    'url' => 'https://sandbox.badgeville.com/cairo',
    'apiVersion' => 'v1',
    'apiKey' => '234lkj23l4kj23l4l2j34lk23j4lk23l4', // get this from your badgeville dashboard
    'siteId' => '23k4lj23kl4j23lkj4', // get this from your badgeville dashboard
];
```

Now you can direct your brower to the examples folder and use the utility. To start 
using the library you need the configure array in your project and then you can 
create a site instance which is needed to make any other calls: 

```php
use Badgeville\Site;
$site = new Site(require_once 'config.php');
```

Example of site specific calls:

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

###Todos
* collections
* pagination
* unittesting