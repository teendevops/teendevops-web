# Status
[![Build Status](https://travis-ci.com/Arinerron/codeday-team.svg?token=xRJQhWcuhJai95gtzHzi&branch=master)](https://travis-ci.com/Arinerron/codeday-team)

# About
The reason why this is called "codeday-team" is because I don't have a name for it yet, and I was developing it for/at CodeDay.  I'll rename when we find a name for it.

# Live Version
Thanks to @virtualdxs, we now have a live version here: http://box.k7dxs.xyz/

Please don't reuse a password on the site though, because it is for testing and may not be secure. Also, the page is sent via HTTP and is not encrypted.

# Installation
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
2. If there is no syntax error, it will return a success message. Otherwise, it will print the error.
