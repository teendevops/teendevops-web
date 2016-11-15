# Status
[![Build Status](https://travis-ci.com/Arinerron/codeday-team.svg?token=xRJQhWcuhJai95gtzHzi&branch=master)](https://travis-ci.com/Arinerron/codeday-team)

# About
The reason why this is called "codeday-team" is because I don't have a name for it yet, and I was developing it for/at CodeDay.  I'll rename when we find a name for it.

# Installation (for master branch)
## Automatically
1. Clone or download this repository
2. Simply execute the script `install.sh`
3. And double check `includes/config.php` just in case

## Manually
1. Clone or download this repository
2. Import the schema called `codeday-team.sql` into your SQL server
3. Rename `config.php.example` in the `includes/` folder to `config.php`
4. Configure your site

# Checking syntax
1. Execute the script `syntax.sh`
2. If there is no syntax error, it will return nothing. Otherwise, it will print the error.

# Development Branch
The development branch contains breaking changes. It may be updated frequently and without warning and is thus inherently unstable. Caution is advised... 

## Installation for development branch
Ensure that you have installed: 
1. Apache2
2. At least PHP5.6
3. PHP-dev

Also ensure that mod_rewrite is enabled: `sudo a2enmod rewrite`. 

Copy the repository into `/var/www/html` therefore giving you `/var/www/html/codeday-team`. (If you change this you'll need to update 000-default.conf)

Copy 000-default.conf in `/var/www/html/codeday-team` into `/etc/apache2/sites-enabled` then restart Apache `sudo service apache2 restart`. 

If no errors are returned you should be able to load the index page. 
