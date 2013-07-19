<?php

/**
 * @author Jordan Skoblenick <jordan@pause.ca> Jul 12, 2013 
 */

// @todo INI file or something
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'spikedcola');
define('DB_SERVER', 'localhost');
define('DB_DATABASE', 'mls');

define('PROJECT_STATUS', 'development'); // flag to switch between development and live

define('TEMPLATES_DIR', __DIR__.'/templates/');
define('TEMPLATES_C_DIR', __DIR__.'/templates_c/');

require_once(__DIR__.'/Skoba.php');

global $db, $user;

$db = new Db();

// try and find the active user
if (!$id = ValidateArrayKey('uniqueId', $_COOKIE)) {
	// user isnt cookied. generate a new user and drop a cookie
	$user = mlsUser::CreateUser();
}
else {
	// user exists, try to load
	try {
		$user = new mlsUser($id);
	}
	catch (Exception $ex) {
		// user id doesnt exist (user could have altered it)
		// generate a new user and drop a cookie
		$user = mlsUser::CreateUser();
	}
}