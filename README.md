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

The best way to get started is to test the find/findall utility under examples/index.php. 
To use, create a copy of examples/config.php.dist and save it as examples/config.php. Next 
update the params array with your info so it looks like:

```php
$params = [
    'url' => 'https://sandbox.badgeville.com/cairo',
    'apiVersion' => 'v1',
    'apiKey' => '234lkj23l4kj23l4l2j34lk23j4lk23l4', // get this from your badgeville dashboard
    'siteId' => '23k4lj23kl4j23lkj4', // get this from your badgeville dashboard
];
```

Now you can direct your brower to the examples folder and use the utility. 

To start using the library you need create a site instance passing it the site id 
and setting the client to a GuzzleHttp\Client instance.

```php
use Badgeville\Cairo\Sites;
$site = new Sites($siteId);
$site->setClient(new GuzzleHttp\Client($params));
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

$activity = $site->players('234lkj234lkj234lkj')->activities()->create([
    'verb' => 'logged'
]);
```

###Todos
* collections
* pagination
* unittesting
* metadata mapping?
* pass in guzzle adapter?
* monolog
* decide on exceptions
* need error handling, resources should indicate what they can do ex: create vs find

considering the following changes 
* so all classes are singular named