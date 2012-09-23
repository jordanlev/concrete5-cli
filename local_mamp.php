#!/usr/bin/php
<?php

#TODO: CAN WE MODIFY THIS SO IT CAN BE RUN FROM THE TARGET DIR (BUT THE RELATIVE PATHS TO THE INSTALL SCRIPT WOULD STILL WORK?)

#TODO: MAKE AN ARRAY OF VARIABLES SO YOU CAN CHOOSE OPTIONS! 5.5.2.1, 5.6.0.2, mebbe 5.5.1 (NOTE THAT 5.5.1 WAS THE FIRST THAT ALLOWED CLI INSTALL, SO CAN'T GO LOWER THAN THAT!)
$c5_download_version = '5.5.2.1';
$c5_download_url = 'http://www.concrete5.org/download_file/-/view/37862/8497/';

##NOTE: IF YOU ARE USING MAMP AND GET 'ERROR: Unable to connect to database.', SEE https://github.com/concrete5/concrete5-cli/issues/2 FOR A FIX!

# DEFAULTS & SETTINGS #########################################################

define('CLI_PARAM_DBSERVER', 'localhost');
define('CLI_PARAM_DBUSERNAME', 'root');
define('CLI_PARAM_DBPASSWORD', 'root');
define('CLI_PARAM_ADMINPASSWORD', 'admin');
define('CLI_PARAM_ADMINEMAIL', 'info@jordanlev.com');

define('SITES_DIR', '/Users/jordanlev/Sites/'); #YES preceeding AND trailing slash!
define('STARTING_POINT_NAME_SAMPLE_CONTENT', 'standard');
define('STARTING_POINT_NAME_EMPTY_CONTENT', 'blank');
define('MYSQL_BIN', '/Applications/MAMP/Library/bin/mysql');
define('MYSQL_USERNAME', CLI_PARAM_DBUSERNAME);
define('MYSQL_PASSWORD', CLI_PARAM_DBPASSWORD);
define('FILENAME_INSTALL_C5_CLI', 'install-concrete5.php'); # file must be in same directory as this local_mamp.php script!
define('FILENAME_ADD_TO_CONFIG_PHP', 'add_to_config_php.txt'); # file must be in same directory as this local_mamp.php script!

# TODO: CHECK IF THE ABOVE TWO FILES EXIST IN THIS DIR. IT'S OKAY IF ADD_TO_CONFIG DOESN'T EXIST, but not the other one!


# ASK USER FOR PARAMS #########################################################
system('clear');
echo "Concrete5 MAMP Setup Script";
echo "===========================\n";

# Get target directory
$target = stdin("Enter target directory (no trailing slash):\n" . SITES_DIR);
$target = rtrim($target, '/');

# Get DB name
$database = stdin("Enter database name (does not exist yet):\nDatabase Name: ");

# Get site name
$site = stdin("Enter Site Name (no quotes/exclamations)\nSite Name: ");
$site = str_replace(array("'", '"', '!'), '', $site); //exclamation points mess up shell commands

# Ask for sample content or blank
$do_install_sample_content = stdin("Install C5 sample content (Y/n)?\ny/n (default y): ", false);
if (strtolower($do_install_sample_content) == "n") {
	$starting_point = STARTING_POINT_NAME_EMPTY_CONTENT;
} else {
	$starting_point = STARTING_POINT_NAME_SAMPLE_CONTENT;
}

# SET UP VARIABLES ############################################################
$c5_download_zipfilename = "concrete{$c5_download_version}.zip";
$c5_download_zippath = SITES_DIR . $c5_download_zipfilename;
# TODO: WHAT IS THE ASSUMPTION WE'RE MAKING HERE? CAN WE UNMAKE IT??
$c5_download_unzipped_dir = SITES_DIR . "concrete{$c5_download_version}"; # NOTE: We're making an assumption about the unzipped contents here!
$target_dir = SITES_DIR . $target;

##TODO: MAKE SURE SITE_DIR EXISTS BEFORE RUNNING ANYTHING!

##TODO: MAKE SURE MAMP IS RUNNING!

# DOWNLOAD/INSTALL C5 FILES ###################################################
echo "Downloading Concrete5 version {$c5_download_version}...\n";
system("curl -o {$c5_download_zippath} {$c5_download_url}");

echo "Unzipping {$c5_download_zippath}...\n";
##TODO: CHECK THAT $c5_download_unzipped_dir DOES NOT EXIST
##      (OR BETTER YET, MAKE A NEW PHONY DIRECTORTY TO UNZIP IT INTO, ETC... THEN WE DON'T CARE WHAT IT'S CALLED ETC.)
##  (OR EVEN BETTER(??): CREATE TARGET DIR FIRST, UNZIP TO THERE, RENAME TARGET DIR WITH TEMP EXTENSION, RENAME UNZIPPED DIR TO TARGTET DIR, MOVE IT UP ONE LEVEL, THEN RMDIR THE FIRST TARGETDIR (that now has temp extension)???
system("unzip {$c5_download_zippath} -d " . SITES_DIR);

echo "Removing downloaded file...\n";
system("rm {$c5_download_zippath}");

echo "Move Concrete5 files to target directory...\n";
##TODO: CHECK THAT $c5_download_unzipped_dir ACTUALLY EXISTS
##TODO: CHECK THAT $target_dir DOES **NOT** ALREADY EXIST
system('mkdir -p ' . dirname($target_dir)); //Ensure the target directory's parent directory exists (the -p option does 2 things for us: creates all necessary hierarchies, *AND* silcences error output if directory already exists)
system("mv {$c5_download_unzipped_dir} {$target_dir}");

echo "Creating database $database...\n";
##TODO: CHECK IF DATABASE EXISTS ALREADY!

$mysql_command = MYSQL_BIN
               . ' -u' . MYSQL_USERNAME
               . ((MYSQL_PASSWORD == '') ? '' : ' -p' . MYSQL_PASSWORD)
               . ' -e "CREATE DATABASE ' . $database . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"';
system($mysql_command);

echo "Installing Concrete5...\n";
## TODO: Check if installer script exists!
$c5_install_command = dirname(__FILE__) . '/' . FILENAME_INSTALL_C5_CLI
                    . ' --db-server=' . CLI_PARAM_DBSERVER
                    . ' --db-username=' . CLI_PARAM_DBUSERNAME
                    . ' --db-password=' . CLI_PARAM_DBPASSWORD
                    . ' --db-database=' . $database
                    . ' --admin-password=' . CLI_PARAM_ADMINPASSWORD
                    . ' --admin-email=' . CLI_PARAM_ADMINEMAIL
                    . ' --starting-point=' . $starting_point
                    . ' --target=' . $target_dir
                    . ' --site="' . $site . '"';
system($c5_install_command);

# TODO: Run the "delete lang files" thing? (maybe make it optional, because we only care if it's a client project going up to svn)

system("echo >> {$target_dir}/config/site.php"); #add a newline to end of file, just in case
system('cat ' . dirname(__FILE__) . '/' . FILENAME_ADD_TO_CONFIG_PHP . " >> {$target_dir}/config/site.php");
##TODO: MAKE SURE THIS FILE EXISTS!!! (AND IF NOT, YOU'LL NEED TO ADD <?php TO THE TOP!!!)

echo "\nINSTALLATION COMPLETE!\n";
echo "\nIf this is an addon test site, go here:\n";
echo "http://localhost:8888/{$target}/index.php/login?rcID=41&uName=admin&uMaintainLogin=1\n";
echo "\nIf this is a client site, remember to do the following:\n";
echo "1) Go to http://localhost:8888/{$target}/index.php/login?rcID=55&uName=admin&uMaintainLogin=1\n";
echo "2) Login (password: " . CLI_PARAM_ADMINPASSWORD . ")\n";
echo "3) Enable Pretty URLs\n";
echo "4) Edit config/site.php and uncomment: define('URL_REWRITING_ALL', true);\n";
echo "5) Add gzip/www stuff to htaccess (see add_to_htaccess.txt file in this script dir)\n";
echo "6) Disable Cache\n";
echo "7) Set custom text editor controls\n";
echo "8) Add Full Sitemap to blue bar\n";
echo "\n";

##TODO: Create .htaccess and append the add_to_htaccess.txt file to it. Not sure what to do about the rewrite rule (eventually we want our own starting point with config vars, but it's not working in 5.5.2.1)

function stdin($prompt, $required = true, $echo_ok_on_success = true) {
	echo $prompt;
	
	$handle = fopen('php://stdin', 'r');
	$response = fgets($handle);
	$response = trim($response); //response always comes back with a newline at the end!
	
	if ($required && empty($response)) {
		return stdin($prompt, $required);
	} else {
		echo "OK..\n";
		return $response;
	}
}
