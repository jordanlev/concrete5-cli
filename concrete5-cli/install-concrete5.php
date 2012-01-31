#!/opt/local/bin/php
<?php

define('FILE_PERMISSIONS_MODE', 0777);
define('APP_VERSION_CLI_MINIMUM', '5.5..1');

error_reporting(0);
ini_set('display_errors', 0);
define('C5_EXECUTE', true);

foreach($argv as $val) {
	$val = explode('=', $val);
	if (is_array($val) && count($val) > 1) {
		switch($val[0]) {
			case '-db-server':
				$DB_SERVER = $val[1];
				break;
			case '-db-username':
				$DB_USERNAME = $val[1];
				break;
			case '-db-password':
				$DB_PASSWORD = $val[1];
				break;
			case '-db-database':
				$DB_DATABASE = $val[1];
				break;
			case '-admin-password':
				$INSTALL_ADMIN_PASSWORD = $val[1];
				break;
			case '-admin-email':
				$INSTALL_ADMIN_EMAIL = $val[1];
				break;
			case '-starting-point':
				$INSTALL_STARTING_POINT = $val[1];
				break;
			case '-target':
				$target = $val[1];
				break;
			case '-site':
				$site = $val[1];
				break;
			case '-core':
				$core = $val[1];
				break;
		}
	}
}

if (!$INSTALL_STARTING_POINT) {
	$INSTALL_STARTING_POINT = 'blank';
}

if ($target) {
	if (substr($target, 0, 1) == '/') {
		define('DIR_BASE', $target);
	} else { 
		define('DIR_BASE', dirname(__FILE__) . '/' . $target);
	}
} else {
	define('DIR_BASE', dirname(__FILE__));
}

if ($core) {
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

if ((DIR_BASE . '/concrete') != $corePath) {
	symlink($corePath, DIR_BASE . '/concrete');
}

## Startup check ##	
require($corePath . '/config/base_pre.php');

## Load the base config file ##
require($corePath . '/config/base.php');


## Load the database ##
Loader::database();

## Load required libraries ##
Loader::library("cache");
Loader::library('object');
Loader::library('log');
Loader::library('localization');
Loader::library('request');
Loader::library('events');
Loader::library('model');
Loader::library('item_list');
Loader::library('view');
Loader::library('controller');
Loader::library('file/types');
Loader::library('block_view');
Loader::library('block_view_template');
Loader::library('block_controller');
Loader::library('attribute/view');
Loader::library('attribute/controller');

## Load required models ##
Loader::model('area');
Loader::model('global_area');
Loader::model('attribute/key');
Loader::model('attribute/value');
Loader::model('attribute/category');
Loader::model('attribute/set');
Loader::model('attribute/type');
Loader::model('block');
Loader::model('custom_style');
Loader::model('file');
Loader::model('file_version');
Loader::model('block_types');
Loader::model('collection');
Loader::model('collection_version');
Loader::model('collection_types');
Loader::model('config');
Loader::model('groups');
Loader::model('layout');  
Loader::model('package');
Loader::model('page');
Loader::model('page_theme');
Loader::model('composer_page');
Loader::model('permissions');
Loader::model('user');
Loader::model('userinfo');
Loader::model('task_permission');
Loader::model('stack/model');

## Setup timzone support
require($corePath . '/startup/timezone.php'); // must be included before any date related functions are called (php 5.3 +)

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
if ($site) {
	$_POST['SITE'] = $site;
} else {
	$_POST['SITE'] = 'concrete5 Site';
}
$_POST['uPassword'] = $INSTALL_ADMIN_PASSWORD;
$_POST['uPasswordConfirm'] = $INSTALL_ADMIN_PASSWORD;
$_POST['uEmail'] = $INSTALL_ADMIN_EMAIL;

$cnt->configure($e);

if (version_compare($APP_VERSION, APP_VERSION_CLI_MINIMUM, '<')) {
	$e->add('Your version of concrete5 must be at least ' . APP_VERSION_CLI_MINIMUM . ' to use this installer.');
}

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

/*
// Reset the database
$db = Loader::db();
$tables = $db->GetCol('show tables from `' . DEMO_DB_DATABASE . '`');
foreach($tables as $t) {
	$db->Execute('drop table ' . DEMO_DB_DATABASE . '.' . $t);
}

// Write the configuration files to config/
		$passwordHash = User::encryptPassword(DEMO_ADMIN_PASSWORD, DEMO_PASSWORD_SALT);
		$setup = get_defined_constants();
		$site_install = <<<EOF
<?php
define('DB_SERVER', '{$setup['DEMO_DB_SERVER']}');
define('DB_USERNAME', '{$setup['DEMO_DB_USERNAME']}');
define('DB_PASSWORD', '{$setup['DEMO_DB_PASSWORD']}');
define('DB_DATABASE', '{$setup['DEMO_DB_DATABASE']}');
define('PASSWORD_SALT', '{$setup['DEMO_PASSWORD_SALT']}');
EOF;

		$site_install_user = <<<EOF
<?php
define('INSTALL_USER_EMAIL', '{$setup['DEMO_ADMIN_EMAIL']}');
define('INSTALL_USER_PASSWORD_HASH', '{$passwordHash}');
define('SITE', '{$setup['DEMO_SETUP_SITE']}');		
EOF;

file_put_contents(DIR_CONFIG_SITE . '/site_install.php', $site_install);
file_put_contents(DIR_CONFIG_SITE . '/site_install_user.php', $site_install_user);

Loader::model('package/starting_point');
require(DIR_CONFIG_SITE . '/site_install.php');
require(DIR_CONFIG_SITE . '/site_install_user.php');

// Remove config/site.php
unlink(DIR_CONFIG_SITE . '/site.php');

// reinstall
$spl = Loader::startingPointPackage(DEMO_STARTING_POINT);
$routines = $spl->getInstallRoutines();
foreach($routines as $r) {
	call_user_func(array($spl, $r->getMethod()));
}

$u = new User();
$u->logout();


// create the demo user that has some limited administrative access
$data = array();
$data['uName'] =  DEMO_USER_USERNAME;
$data['uEmail'] =  DEMO_USER_EMAIL;
$data['uPassword'] = DEMO_USER_PASSWORD;
$data['uPasswordConfirm'] = DEMO_USER_PASSWORD;
$demoUser = UserInfo::register($data);
$adminGroup = Group::getByID(ADMIN_GROUP_ID);
$demoU = $demoUser->getUserObject();
$demoU->enterGroup($adminGroup);

// Lock down file set permissions
Loader::model('file_set');
$fs = FileSet::getGlobal();
$fs->setPermissions($adminGroup, FilePermissions::PTYPE_ALL, FilePermissions::PTYPE_ALL, FilePermissions::PTYPE_NONE, FilePermissions::PTYPE_NONE, FilePermissions::PTYPE_NONE);

// Disable emails on site.
$configFile = DIR_BASE."/config/site.php";
$fp = fopen($configFile,'a');
fwrite($fp, "define('ENABLE_EMAILS', false); ?>");
fclose($fp);

// finally, we install the packages we wish to install.
// These must be located in the root packages/ directory
$packages = explode(',',PACKAGES_TO_INSTALL);
foreach($packages as $pkgHandle) {
	$pkgHandle = trim($pkgHandle);
	$pkg = @Loader::package($pkgHandle);
	if (is_object($pkg)) {
		@$pkg->install();
	}
}

// reset certain task permissions
$permissions = array('access_task_permissions','sudo', 'install_packages', 'uninstall_packages', 'delete_user', 'backup');
foreach($permissions as $handle) {
	$tp = TaskPermission::getByHandle($handle);
	$tp->clearPermissions();
}

// set to production
Config::save('SITE_DEBUG_LEVEL', DEBUG_DISPLAY_PRODUCTION);
*/