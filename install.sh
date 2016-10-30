#!/bin/bash

mv includes/config.php.example includes/config.php
read -p "Enter your MySQL Username:" username;
read -p "Enter your MySQL Password:" password;
mysql -u $username -p$(echo $password) codeday-team < codeday-team.sql
unset username; # null it!
unset password; # do the same!
