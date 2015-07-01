<?php

/**
 * Main include file for all pages
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 
 */

mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

define('PROJECT_STATUS', 'development'); // flag to switch between development and live

define('DISABLE_ERROR_REPORTING', true);

define('TEMPLATES_DIR', __DIR__.'/public_html/templates/');
define('TEMPLATES_C_DIR', __DIR__.'/templates_c/');
define('CACHE_DIR', __DIR__.'/cache/');

require_once(__DIR__.'/credentials.php');
require_once('Smarty3/Smarty.class.php'); // required for Template
require_once(__DIR__.'/Skoba.php');

// autoload classes
spl_autoload_register(function($class) {
	$file = __DIR__."/classes/{$class}.class.php";
	if (file_exists($file)) {
		require_once($file);
	}	
});

// autoload listingProviders
spl_autoload_register(function($class) {
	$file = __DIR__."/classes/listingProviders/{$class}.class.php";
	if (file_exists($file)) {
		require_once($file);
	}	
});
