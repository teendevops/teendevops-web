# Status
[![Build Status](https://travis-ci.com/Arinerron/codeday-team.svg?token=xRJQhWcuhJai95gtzHzi&branch=master)](https://travis-ci.com/Arinerron/codeday-team)

# Important Notice
Do NOT use the Makefile. It is really bugged right now (see [issue #1](https://github.com/Arinerron/codeday-team/issues/1)). If it is too late for you, simply delete `includes/config.php` and `includes/config.php.example`, and redownload the original file `includes/config.php.example` from the repository. Make sure to drop the database `codeday-team` too.

# About
The reason why this is called "codeday-team" is because I don't have a name for it yet, and I was developing it for/at CodeDay.  I'll rename when we find a name for it.

# Installation
1. Clone or download this repository
2. Import the schema called `codeday-team.sql` into your SQL server
3. Rename `config.php.example` in the `includes/` folder to `config.php`
4. Configure your site
5. Start the webserver!
