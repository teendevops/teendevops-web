# Status
[![Build Status](https://travis-ci.com/Arinerron/codeday-team.svg?token=xRJQhWcuhJai95gtzHzi&branch=master)](https://travis-ci.com/Arinerron/codeday-team)

# About
teendevops aims to create an scalable and open-source platform on which teenagers who share similar interests in information technology can connect, communicate, and collaborate with each other.

You can check out the project requirements [here](https://github.com/Arinerron/teendevops/blob/master/REQUIREMENTS.md).

# Documentation
- You can access the REST API documentation in the file [/api/v1/README.md](https://github.com/Arinerron/teendevops/blob/master/api/v1/README.md).
- The code is documented with comments

# Live Version
Please don't reuse a password on either of these sites yet. Neither of them use HTTPS, so neither of them are considered fully secure.
## Production
- Thanks to @Gwiddle Web Hosting, we now have a prod version here: http://teendevops.net
## Development
- Thanks to @virtualdxs, we now have a dev version here: http://box.k7dxs.xyz/
- You can try your commit by loading http://box.k7dxs.xyz/pull.php

# Installation
## Automatically
1. Clone or download this repository
2. Simply execute the script `install.sh`
3. And double check `includes/config.php` just in case

## Manually
1. Clone or download this repository
2. Import the schema called `teendevops.sql` into your SQL server
3. Rename `config.php.example` in the `includes/` folder to `config.php`
4. Configure your site

# Checking syntax
1. Execute the script `syntax.sh`
2. If there is no syntax error, it will return a success message. Otherwise, it will print the error.

# Thanks to...
- @eviltak - Came up with the name `teendevops` and helped a lot with planning
- @DudmasterUltra - Wrote a Java API for teendevops and helped brainstorming
- @virtualdxs - Web hosting for development
- @Gwiddle - Web hosting for production
- [All of the developers and users that were not mentioned](https://github.com/Arinerron/teendevops/graphs/contributors)...

[flag](ur-a_r3adme_reader)
