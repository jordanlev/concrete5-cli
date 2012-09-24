#!/usr/bin/php
<?php

##NOTE: IF YOU ARE USING MAMP AND GET 'ERROR: Unable to connect to database.', SEE https://github.com/concrete5/concrete5-cli/issues/2 FOR A FIX!

# DEFAULTS & SETTINGS #########################################################

define('CLI_PARAM_DBSERVER', 'localhost');
define('CLI_PARAM_DBUSERNAME', 'root');
define('CLI_PARAM_DBPASSWORD', 'root');
define('CLI_PARAM_ADMINPASSWORD', 'admin'); //DON'T USE A REAL PASSWORD -- THIS GETS SENT IN QUERYSTRING TO THE TEMP LOGIN PAGE UPON INSTALLATION (WHICH MIGHT STICK AROUND IN BROWSER HISTORY ETC.)!
define('CLI_PARAM_ADMINEMAIL', 'info@jordanlev.com');

define('HTDOCS_DIR', '/Users/jordanlev/Sites/'); #YES preceeding AND trailing slash!
define('LOCALHOST_BASE_URL', 'http://localhost:8888/'); #YES trailing slash!

define('STARTING_POINT_NAME_SAMPLE_CONTENT', 'standard');
define('STARTING_POINT_NAME_EMPTY_CONTENT', 'blank');
define('MYSQL_BIN', '/Applications/MAMP/Library/bin/mysql');
define('MYSQL_USERNAME', CLI_PARAM_DBUSERNAME);
define('MYSQL_PASSWORD', CLI_PARAM_DBPASSWORD);
#The following files must exist in the same directory as this-here local_mamp.php script:
define('FILENAME_INSTALL_C5_CLI', 'install-concrete5.php'); //required
define('FILENAME_LOGIN_TASKS', 'temp_cli_login.html'); //optional (use empty string if none)
define('FILENAME_ADD_TO_CONFIG_PHP', 'add_to_config_php.txt'); //optional (use empty string if none)

//Available C5 versions for installation (note that 5.5.1 was the first version to allow CLI installation).
//First one in list becomes default option.
//"unzips_to" is the name of the folder that you wind up with after unzipping the download
// (so far this has always been the word "concrete" followed by the version number,
//  but theoretically this could change in the future?)
$c5_versions = array(
	array(
		'name' => '5.6.0.2',
		'url' => 'http://www.concrete5.org/download_file/-/view/44326/8497/',
		'unzips_to' => 'concrete5.6.0.2',
	),
	array(
		'name' => '5.5.2.1',
		'url' => 'http://www.concrete5.org/download_file/-/view/37862/8497/',
		'unzips_to' => 'concrete5.5.2.1',
	),
	array(
		'name' => '5.5.1',
		'url' => 'http://www.concrete5.org/download_file/-/view/33453/8497/',
		'unzips_to' => 'concrete5.5.1',
	),
);


# ASK USER FOR PARAMS #########################################################
system('clear');
echo "Concrete5 MAMP Setup Script\n";
echo "===========================\n\n";

# Choose version
echo "Available Concrete5 versions...\n";
for ($i = 0, $len = count($c5_versions); $i < $len; $i++) {
	echo ($i + 1) . ') ' . $c5_versions[$i]['name'];
	echo ($i == 0) ? ' (DEFAULT)' : '';
	echo "\n";
}
echo "\n";
$version_index = stdin("Enter number for the version to install (or leave blank for default):", false);
$version_index = empty($version_index) ? 0 : intval($version_index);
$version_index = ($version_index < 0 || $version_index >= count($c5_versions)) ? 0 : $version_index;
$version = $c5_versions[$version_index];

# Get target directory
$target = stdin("Enter target directory (trailing slash will be stripped):\n" . HTDOCS_DIR);
$target_url = rtrim($target, '/');
$target_dir = HTDOCS_DIR . $target_url;
if (is_dir($target_dir)) {
	echo "ABORTING INSTALLATION: TARGET DIRECTORY ({$target_dir}) ALREADY EXISTS!\n";
	exit;
}

# Get DB name
$database = stdin("Enter database name (must not already exist):\nDatabase Name: ");
$database = strip_unsafe_cli_chars($database);

# Get site name
$site = stdin("Enter Site Name (quotes/exclamations will be stripped)\nSite Name: ");
$site = strip_unsafe_cli_chars($site);

# Ask for sample content or blank
$do_install_sample_content = stdin("Install C5 sample content (Y/n)?\ny/n (default y): ", false);
if (strtolower($do_install_sample_content) == "n") {
	$starting_point = STARTING_POINT_NAME_EMPTY_CONTENT;
} else {
	$starting_point = STARTING_POINT_NAME_SAMPLE_CONTENT;
}


# CHECK DATABASE (ALSO SERVES AS A CHECK TO ENSURE MAMP IS RUNNING) ###########
$sql = "SHOW DATABASES LIKE '{$database}'";
$db_check = mysql_exec($sql, true);
if (strpos($db_check, 'ERROR') === 0) {
	echo "ABORTING INSTALLATION: THE FOLLOWING DATABASE ERROR OCCURRED: {$db_check}\n";
	exit;
} else if (strlen($db_check) > 0) {
	echo "ABORTING INSTALLATION: DATABASE ({$database}) ALREADY EXISTS!\n";
	exit;
}


# VALIDATE / SETUP DIRECTORIES AND FILES ######################################
$c5_cli_script_path = dirname(__FILE__) . '/' . FILENAME_INSTALL_C5_CLI;
$parent_dir = dirname($target_dir);
$c5_download_zippath = $parent_dir . '/temp_concrete5_cli_download.zip';
$c5_download_unzipped_dir = $parent_dir . '/' . $version['unzips_to'];
if (!is_file($c5_cli_script_path)) {
	echo "ABORTING INSTALLATION: CANNOT LOCATE C5 CLI INSTALL SCRIPT ({$c5_cli_script_path})!\n";
	exit;
}
if (is_file($c5_download_zippath)) {
	echo "ABORTING INSTALLATION: DOWNLOAD FILE NAME ({$c5_download_zippath}) ALREADY EXISTS!\n";
	exit;
}
if (is_dir($c5_download_unzipped_dir)) {
	echo "ABORTING INSTALLATION: DOWNLOAD UNZIPS-TO DIRECTORY NAME ({$c5_download_unzipped_dir}) ALREADY EXISTS!\n";
	exit;
}
system('mkdir -p ' . $parent_dir); //Ensure the target directory's parent directory exists (the -p option does 2 things for us: creates all necessary hierarchies, *AND* silcences error output if directory already exists)


# DOWNLOAD/INSTALL C5 FILES ###################################################
echo "Downloading Concrete5 version {$version['name']}...\n";
system("curl -o {$c5_download_zippath} {$version['url']}");

echo "Unzipping {$c5_download_zippath}...\n";
system("unzip {$c5_download_zippath} -d " . $parent_dir);

echo "Removing downloaded file...\n";
system("rm {$c5_download_zippath}");

echo "Move Concrete5 files to target directory...\n";
if (!is_dir($c5_download_unzipped_dir)) {
	echo "ERROR: CANNOT LOCATE UNZIPPED CONCRETE5 DIRECTORY -- EXPECTED IT IN: {$c5_download_unzipped_dir}\n";
	exit;
}
system("mv {$c5_download_unzipped_dir} {$target_dir}");


# INSTALL DATABASE AND C5 #####################################################
echo "Creating database $database...\n";
$sql = "CREATE DATABASE {$database} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"; //don't use backticks -- they mess up command line!
mysql_exec($sql);

echo "Installing Concrete5...\n";
$c5_install_command = $c5_cli_script_path
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


# CUSTOMIZE CONFIG FILE #######################################################
if (FILENAME_ADD_TO_CONFIG_PHP !== '') {
	$add_to_config_php_path = dirname(__FILE__) . '/' . FILENAME_ADD_TO_CONFIG_PHP;
	if (is_file($add_to_config_php_path)) {
		#add a newline to end of file (to separate our items from existing items)
		system("echo >> {$target_dir}/config/site.php");
	} else {
		#create file, and put opening php tag at top
		system('echo -e "<?php\n" >> ' . $target_dir . '/config/site.php');
	}
	system('cat ' . dirname(__FILE__) . '/' . FILENAME_ADD_TO_CONFIG_PHP . " >> {$target_dir}/config/site.php");
}


# LOGIN #######################################################################
if (FILENAME_LOGIN_TASKS !== '') {
	echo "Logging in...\n";
	
	$login_file_source_path = dirname(__FILE__) . '/' . FILENAME_LOGIN_TASKS;
	$login_file_dest_path = $target_dir . '/config/' . FILENAME_LOGIN_TASKS;
	$login_file_url = LOCALHOST_BASE_URL . $target_url . '/config/' . FILENAME_LOGIN_TASKS . '#' . CLI_PARAM_ADMINPASSWORD;
	
	system("cp {$login_file_source_path} {$login_file_dest_path}");
	system("open {$login_file_url}");
	echo "Deleting temporary login file ({$login_file_dest_path}) in 5 seconds...";
	shell_exec('sleep 5');
	system("rm {$login_file_dest_path}");
	echo "\nTemporary login file ({$login_file_dest_path}) has been deleted.";
}

# DONE! #######################################################################
system("open {$target_dir}");
echo "\n\n=====\nDONE!\n=====\n\n";


# Utility Functions ###########################################################
function stdin($prompt, $required = true) {
	echo $prompt;
	
	$handle = fopen('php://stdin', 'r');
	$response = fgets($handle);
	$response = trim($response); //response always comes back with a newline at the end!
	
	if ($required && empty($response)) {
		return stdin($prompt, $required);
	} else {
		echo "\n\n";
		return $response;
	}
}

//NOTE: $sql MUST NOT HAVE ANY QUOTES IN IT!!! (Apostrophes are okay though.)
function mysql_exec($sql, $return_output = false) {
	$command = MYSQL_BIN
             . ' -u' . MYSQL_USERNAME
             . ((MYSQL_PASSWORD == '') ? '' : ' -p' . MYSQL_PASSWORD)
             . ' -e "' . $sql . '"';
	if ($return_output) {
		return shell_exec($command);
	} else {
		system($command);
	}
}

function strip_unsafe_cli_chars($str) {
	$bad_chars = array('"', "'", '`', '!'); //exclamations and backticks mess up command lines!
	return str_replace($bad_chars, '', $str);
}