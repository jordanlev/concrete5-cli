#!/opt/local/bin/php
<?php

define('FILE_PERMISSIONS_MODE', 0777);
define('DIRECTORY_PERMISSIONS_MODE', 0777);
define('APP_VERSION_CLI_MINIMUM', '5.5.1');

error_reporting(0);
ini_set('display_errors', 0);
define('C5_EXECUTE', true);

foreach($argv as $val) {
	$val = explode('=', $val);
	if (is_array($val) && count($val) > 1) {
		switch($val[0]) {
			case '--db-server':
				$DB_SERVER = $val[1];
				break;
			case '--db-username':
				$DB_USERNAME = $val[1];
				break;
			case '--db-password':
				$DB_PASSWORD = $val[1];
				break;
			case '--db-database':
				$DB_DATABASE = $val[1];
				break;
			case '--admin-password':
				$INSTALL_ADMIN_PASSWORD = $val[1];
				break;
			case '--admin-email':
				$INSTALL_ADMIN_EMAIL = $val[1];
				break;
			case '--starting-point':
				$INSTALL_STARTING_POINT = $val[1];
				break;
			case '--target':
				$target = $val[1];
				break;
			case '--site':
				$site = trim($val[1], '\'"'); //remove surrounding quotes (because site name likely has spaces in it, which need to be quoted in command-line args)
				break;
			case '--core':
				$core = $val[1];
				break;
		}
	}
}

if (!$INSTALL_STARTING_POINT) {
	$INSTALL_STARTING_POINT = 'blank';
}

if (!empty($target)) {
	if (substr($target, 0, 1) == '/') {
		define('DIR_BASE', $target);
	} else { 
		define('DIR_BASE', dirname(__FILE__) . '/' . $target);
	}
} else {
	define('DIR_BASE', dirname(__FILE__));
}

if (!empty($core)) {
	if (substr($core, 0, 1) == '/') {
		$corePath = $core;	
	} else {
		$corePath = dirname(__FILE__) . '/' . $core;
	}
} else {
	$corePath = DIR_BASE . '/concrete';
}

if (!file_exists($corePath . '/config/version.php')) {
	die("ERROR: Invalid concrete5 core.\n");
} else {
	include($corePath . '/config/version.php');
}

if (file_exists(DIR_BASE . '/config/site.php')) {
	die("ERROR: concrete5 is already installed.\n");
}		

## Startup check ##	
require($corePath . '/config/base_pre.php');

## Load the base config file ##
require($corePath . '/config/base.php');

## Required Loading
require($corePath . '/startup/required.php');

## Setup timezone support
require($corePath . '/startup/timezone.php'); // must be included before any date related functions are called (php 5.3 +)

## First we ensure that dispatcher is not being called directly
require($corePath . '/startup/file_access_check.php');

require($corePath . '/startup/localization.php');

## Autoload core classes
spl_autoload_register(array('Loader', 'autoloadCore'), true);

## Load the database ##
Loader::database();

require($corePath . '/startup/autoload.php');

## Exception handler
require($corePath . '/startup/exceptions.php');

## Set default permissions for new files and directories ##
require($corePath . '/startup/file_permission_config.php');

## Startup check, install ##	
require($corePath . '/startup/magic_quotes_gpc_check.php');

## Default routes for various content items ##
require($corePath . '/config/theme_paths.php');

## Load session handlers
require($corePath . '/startup/session.php');

## Startup check ##	
require($corePath . '/startup/encoding_check.php');

$cnt = Loader::controller("/install");
$cnt->on_start();
$fileWriteErrors = clone $cnt->fileWriteErrors;
$e = Loader::helper('validation/error');

// handle required items
if (!$cnt->get('imageTest')) {
	$e->add(t('GD library must be enabled to install concrete5.'));
}
if (!$cnt->get('mysqlTest')) {
	$e->add($cnt->getDBErrorMsg());
}
if (!$cnt->get('xmlTest')) {
	$e->add(t('SimpleXML and DOM must be enabled to install concrete5.'));
}
if (!$cnt->get('phpVtest')) {
	$e->add(t('concrete5 requires PHP 5.2 or greater.'));
}

if (is_object($fileWriteErrors)) {
	$e->add($fileWriteErrors);
}

$_POST['SAMPLE_CONTENT'] = $INSTALL_STARTING_POINT;
$_POST['DB_SERVER'] = $DB_SERVER;
$_POST['DB_USERNAME'] = $DB_USERNAME;
$_POST['DB_PASSWORD'] = $DB_PASSWORD;
$_POST['DB_DATABASE'] = $DB_DATABASE;
if (!empty($site)) {
	$_POST['SITE'] = $site;
} else {
	$_POST['SITE'] = 'concrete5 Site';
}
$_POST['uPassword'] = $INSTALL_ADMIN_PASSWORD;
$_POST['uPasswordConfirm'] = $INSTALL_ADMIN_PASSWORD;
$_POST['uEmail'] = $INSTALL_ADMIN_EMAIL;

if (version_compare($APP_VERSION, APP_VERSION_CLI_MINIMUM, '<')) {
	$e->add('Your version of concrete5 must be at least ' . APP_VERSION_CLI_MINIMUM . ' to use this installer.');
}

if ($e->has()) {
	foreach($e->getList() as $ei) {
		print "ERROR: " . $ei . "\n";
	}	
	die;
}

$cnt->configure($e);

if ($e->has()) {
	foreach($e->getList() as $ei) {
		print "ERROR: " . $ei . "\n";
	}	
} else {
	$spl = Loader::startingPointPackage($INSTALL_STARTING_POINT);
	require(DIR_CONFIG_SITE . '/site_install.php');
	require(DIR_CONFIG_SITE . '/site_install_user.php');
	$routines = $spl->getInstallRoutines();
	try {
		foreach($routines as $r) {
			print $r->getProgress() . '%: ' . $r->getText() . "\n";
			call_user_func(array($spl, $r->getMethod()));
		}
	} catch(Exception $ex) {
		print "ERROR: " . $ex->getMessage() . "\n";		
		$cnt->reset();
	}
	
	if (!isset($ex)) {
		Config::save('SEEN_INTRODUCTION', 1);
		print "Installation Complete!\n";
	}
	
}