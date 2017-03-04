#!/bin/bash
# f1ag is 1nst@all_it

if [ ! "$BASH_VERSION" ] ; then
    bash install.sh;
    exit 0;
fi;

echo "Welcome to codeday-team!";
echo -n "Please enter the MySQL host (default: localhost):";
read host;
host=${host:-localhost};
echo -n "Please enter the MySQL username:";
read username;
echo -n "Please enter the MySQL password:";
read -s password;
echo;
rm includes/config.php 2>/dev/null || true;
cp includes/config.php.example includes/config.php 2>/dev/null || true;
chmod 740 includes/config.php;
sed "s/\"USER\", \"\"/\"USER\", \"$username\"/g" includes/config.php > includes/config_change.php;
mv includes/config_change.php includes/config.php;
sed "s/\"PASSWORD\", \"\"/\"PASSWORD\", \"$password\"/g" includes/config.php > includes/config_change.php;
mv includes/config_change.php includes/config.php;
sed "s/\"HOST\", \"localhost\"/\"HOST\", \"$host\"/g" includes/config.php > includes/config_change.php;
mv includes/config_change.php includes/config.php;
mysql -u "$(echo $username)" -p"$(echo $password)" < ./codeday-team.sql;
echo "Configuration complete. For further configuration options, check the config file includes/config.php";
unset username;
unset password;
unset host;
exit 0;
