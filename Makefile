SHELL := /bin/bash

default:
	@echo "Welcome to codeday-team!";\
	echo -n "Please enter the MySQL host (default: localhost):";\
	read host;\
	host=${host:-localhost};\
	echo -n "Please enter the MySQL username:";\
	read username;\
	echo $password;\
	echo -n "Please enter the MySQL password:";\
	read -s password;\
	mv includes/config.php.example includes/config.php 2>/dev/null;true;\
	sed 's/"USER", ""/"USER", "$(username)"/g' includes/config.php > includes/config.php;\
	sed 's/"PASSWORD", ""/"PASSWORD", "$(password)"/g' includes/config.php > includes/config.php;\
	echo $username;\
	echo $password;\
	mysql -u "$username" -p"$password" codeday-team < ./codeday-team.sql;\
	echo "Configuration complete. For further configuration options, check the config file includes/config.php";\
	exit 0;
