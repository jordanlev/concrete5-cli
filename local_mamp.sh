#! /bin/bash

c5_download_version='5.5.2.1'
c5_download_url='http://www.concrete5.org/download_file/-/view/37862/8497/'

##NOTE: IF YOU ARE USING MAMP AND GET 'ERROR: Unable to connect to database.', SEE https://github.com/concrete5/concrete5-cli/issues/2 FOR A FIX!

# DEFAULTS & SETTINGS #########################################################

cli_param_dbserver='localhost'
cli_param_dbusername='root'
cli_param_dbpassword='root'
cli_param_adminpassword='admin'
cli_param_adminemail='info@jordanlev.com'

sites_dir='/Users/jordanlev/Sites/' #YES preceeding AND trailing slash!
starting_point_name_sample_content='standard'
starting_point_name_empty_content='blank'
mysql_bin='/Applications/MAMP/Library/bin/mysql'
mysql_username="$cli_param_dbusername"
mysql_password="$cli_param_dbpassword" # NOTE: I don't think this script will work with a blank password (you would need to modify the mysql command below so it does *NOT* specify the -p option)
c5_install_cli_bin='./install-concrete5.php' #RELATIVE TO THIS SCRIPT'S PATH!!!!
##TODO: How can we make it so this doesn't have to be relative to script path? (Do we even want to do that?)


# ASK USER FOR PARAMS #########################################################
clear
echo "Concrete5 $c5_download_version MAMP Setup Script"
echo -e "===================================\n"

# Get target directory
while true
do 
	echo "Enter target directory (NO trailing slash):"
	read -p "$sites_dir" target

	if [ "$target" = "" ]; then 
		continue
	else
		echo -e "OK..\n"
		break
	fi
done

# Get DB name
while true
do 
	echo "Enter database name (does not exist yet):"
	read -p "Database Name: " database

	if [ "$database" = "" ]; then 
		continue
	else
		echo -e "OK..\n"
		break
	fi
done

# Get site name
while true
do 
       echo "Enter Site Name (NO QUOTES, NO EXCLAMATION POINTS)"
       read -p "Site Name: " site

       if [ "$site" = "" ]; then
           continue
       else
           echo -e "OK..\n"
           break
       fi
done

##TODO: CHECK FOR QUOTES AND EXCLAMATION POINTS ETC.!!!

# Ask for sample content or blank
while true
do 
       echo "Install C5 sample content (Y/n)?"
       read -p "y/n (default y): " do_install_sample_content

       if [ "$do_install_sample_content" = "n" ]; then
           starting_point=$starting_point_name_empty_content
       else
           starting_point=$starting_point_name_sample_content
       fi
       echo -e "OK..\n"
       break
done

# SET UP VARIABLES ############################################################
c5_download_zipfilename="concrete$c5_download_version.zip"
c5_download_zippath="$sites_dir$c5_download_zipfilename"
c5_download_unzipped_dir="${sites_dir}concrete$c5_download_version" # NOTE: We're making an assumption about the unzipped contents here!
target_dir="$sites_dir$target"

##TODO: MAKE SURE SITE_DIR EXISTS BEFORE RUNNING ANYTHING!

##TODO: MAKE SURE MAMP IS RUNNING!

# DOWNLOAD/INSTALL C5 FILES ###################################################
echo "Downloading Concrete5 version $c5_download_version..."
curl -o $c5_download_zippath $c5_download_url

echo "Unzipping $c5_download_zippath..."
##TODO: CHECK THAT $c5_download_unzipped_dir DOES NOT EXIST
##      (OR BETTER YET, MAKE A NEW PHONY DIRECTORTY TO UNZIP IT INTO, ETC... THEN WE DON'T CARE WHAT IT'S CALLED ETC.)
##  (OR EVEN BETTER(??): CREATE TARGET DIR FIRST, UNZIP TO THERE, RENAME TARGET DIR WITH TEMP EXTENSION, RENAME UNZIPPED DIR TO TARGTET DIR, MOVE IT UP ONE LEVEL, THEN RMDIR THE FIRST TARGETDIR (that now has temp extension)???
unzip $c5_download_zippath -d $sites_dir

echo "Removing downloaded file..."
rm $c5_download_zippath

echo "Move Concrete5 files to target directory..."
##TODO: CHECK THAT $c5_download_unzipped_dir ACTUALLY EXISTS
##TODO: CHECK THAT $target_dir DOES **NOT** ALREADY EXIST
mkdir -p $(dirname ${target_dir})
mv $c5_download_unzipped_dir $target_dir

echo "Creating database $database...";
##TODO: CHECK IF DATABASE EXISTS ALREADY!
$mysql_bin -u$mysql_username -p$mysql_password -e "CREATE DATABASE $database DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";

echo "Installing Concrete5...";
## TODO: Check if installer script exists!
$c5_install_cli_bin --db-server=$cli_param_dbserver --db-username=$cli_param_dbusername --db-password=$cli_param_dbpassword --db-database=$database --admin-password=$cli_param_adminpassword --admin-email=$cli_param_adminemail --starting-point=$starting_point --target=$target_dir --site="$site"

# TODO: Run the "delete lang files" thing? (maybe make it optional, because we only care if it's a client project going up to svn)

echo >> $target_dir/config/site.php #add a newline to end of file, just in case
cat ./add_to_config_php.txt >> $target_dir/config/site.php
##TODO: MAKE SURE THIS FILE EXISTS!!!

echo -e "\nINSTALLATION COMPLETE!"
echo -e "\nIf this is an addon test site, go here:"
echo "http://localhost:8888/$target/index.php/login?rcID=41&uName=admin&uMaintainLogin=1"
echo -e "\nIf this is a client site, remember to do the following:"
echo "1) Go to http://localhost:8888/$target/index.php/login?rcID=55&uName=admin&uMaintainLogin=1"
echo "2) Login (password: $cli_param_adminpassword)"
echo "3) Enable Pretty URLs"
echo "4) Edit config/site.php and uncomment: define('URL_REWRITING_ALL', true);"
echo "5) Add gzip/www stuff to htaccess (see add_to_htaccess.txt file in this script dir)"
echo "6) Disable Cache"
echo "7) Set custom text editor controls"
echo "8) Add Full Sitemap to blue bar"
echo ""

##TODO: Create .htaccess and append the add_to_htaccess.txt file to it. Not sure what to do about the rewrite rule (eventually we want our own starting point with config vars, but it's not working in 5.5.2.1)
