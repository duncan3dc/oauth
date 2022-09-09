oauth
=====

Handles non-web based OAuth/OAuth2 authentication along with providers for common services

[![Latest Stable Version](https://poser.pugx.org/duncan3dc/oauth/version.svg)](https://packagist.org/packages/duncan3dc/oauth)
[![build](https://github.com/duncan3dc/oauth/workflows/.github/workflows/buildcheck.yml/badge.svg?branch=main)](https://github.com/duncan3dc/oauth/actions/workflows/buildcheck.yml)
[![Coverage Status](https://coveralls.io/repos/github/duncan3dc/oauth/badge.svg?branch=master)](https://coveralls.io/github/duncan3dc/oauth)

__I really don't think you will find these classes useful, if you proceed then don't say I didn't warn you__  
You are likely looking for [thephpleague/oauth1-client](https://github.com/thephpleague/oauth1-client) or [thephpleague/oauth2-client](https://github.com/thephpleague/oauth2-client)


Requirements
------------

* These classes require use of the [sql-class](https://github.com/duncan3dc/sql-class) project (for storing authorised credentials)
* The active database must contain a table called oauth (or know about it via the definitions within the Sql class)
* The table must have the following schema
```
`type`      varchar(20),
`username`  varchar(100),
`state`     int(11),
`token`     varchar(100),
`secret`    varchar(100),
UNIQUE KEY `type, username` (`type`,`username`)
```


OAuth2
------
The OAuth2 classes require manual intervention to get authorised initially, but once your token/secret are in the database they work like the OAuth classes.


Examples
--------

Basic twitter example
```php
use duncan3dc\OAuth\Twitter;
use duncan3dc\SqlClass\Sql;

Sql::addServer("twitter", [
    "hostname"  =>  "example.com",
    "username"  =>  "my_twitter_app",
    "password"  =>  "secret_password",
    "database"  =>  "twitter",
]);

$twitter = new Twitter([
    "username"  =>  "my_handle",
    "authkey"   =>  "XfHrRTY25FgkyqxDfbpe",
    "secret"    =>  "gwj8c29GHDWdphmQhGtHPx4GybwRfhXplT3CD0VG1n",
]);

# The authorise method returns null if we are already authorised. Otherwise it returns a url to grant at
if ($url = $twitter->authorise()) {
    throw new \Exception("Authorsation failed, grant permission here: " . $url);
}

$userData = $twitter->user("my_handle");
print_($userData);
```


If you have extended the Sql class (or are using an interface compatible project) you can have the OAuth classes use that class as so
```php
use duncan3dc\OAuth\Twitter;
use MrCoder\MyCustom\Sql;

\duncan3dc\OAuth\Sql::useClass(Sql::class);

$twitter = new Twitter([
```


If your oauth table is not in the active database, then you can let the Sql class know where it is using the following
```php
use duncan3dc\OAuth\Twitter;
use duncan3dc\SqlClass\Sql;

Sql::addServer("twitter", [
    "hostname"      =>  "example.com",
    "username"      =>  "my_twitter_app",
    "password"      =>  "secret_password",
    "database"      =>  "twitter",
    "definitions"   =>  ["oauth" => "my_other_database"],
]);

$twitter = new Twitter([
```
