# PHP ddos-protected-server

php library limit requests by (basic, ipaddress, mac).

## Getting Started

php library protect ddos service. Limit requests by minutes form guest or ipaddress.
this script help protect your website from ddos-tools.

### Prerequisites

```
PHP > 7.x
```

### Installing

```
composer require drhuy/ddos-protected-server
```
with laravel - octobercms

```
php artisan vendor:publish --provider="Drhuy\DdosProtected\Server"
```
## Running the tests

Basic
```
use Drhuy\DdosProtected\Server;

$t = new Server;
$t-> run();
```

Advances
```
use Drhuy\DdosProtected\Server;

$t = new Server([
    'fix_max_request'   => 15,  // if run() without max_request then get it
    'max_request'       => 10,
    'minute_reset'      => 1,   // reset count request after :minute_reset minutes
    'n_logs_keep'       => 3,   // keep :n_logs_keep lasted logs
    'auto_remove_log'   => true,
    'block_type'        => '',  // ['', 'IP', 'MAC']
    'site_name'         => '',  // Group requests of other used for multi site or pages
    // callback functions
    'onSuspend'         => function($client){
    },
    'onAccept'          => function(){
    }
]);
$t-> run(['block_type'=> 'IP', 'max_request'=> 5]);
$t-> run();
```
## Notic
* run($arguments): $arguments like _construct $arguments
* You can use multi thread. this ex used 2 thread
** thread 1: per IP will be requested 5 times per :minute_reset minutes
** thread 2: all requests per :minute_reset < :fix_max_request
** block_type = 'MAC' no required it make your site too slow.

## Built With

* [PHP](https://www.php.net/) - PHP is a popular general-purpose scripting language that is especially suited to web development.

## Authors

**Đinh Thanh Huy** [DrHuy](https://github.com/huydt03)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Thanks for used
* It help for you: donate me $1: https://paypal.me/huy03