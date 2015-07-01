<?php

/**
 * Main include file for all pages
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 
 */

require_once(__DIR__.'/../inc.php');

global $user, $db;

$db = new Db();

// try and find the active user
if ($id = ValidateArrayKey('uniqueId', $_COOKIE)) {
	// user exists, try to load
	try {
		$user = new mlsUser($id);
	}
	catch (Exception $ex) {
		// user id doesnt exist (user could have altered it)
		// generate a new user and drop a cookie
		$user = new mlsUser();
		$user->save();
		$user->dropUserCookie();
	}
}
else {
	// user isnt cookied. generate a new user and drop a cookie
	$user = new mlsUser();
	$user->save();
	$user->dropUserCookie();
}
