<?php
	require_once(__DIR__ . '/classes/db.php');
	$db = new Db();

	// any actions will be user-specific, so check for the user too
        if (!empty($_POST['action']) && !empty($_COOKIE['uniqueId']) && $db->user_exists($_COOKIE['uniqueId'])) {
		$action = $_POST['action']; // so we can list() $_POST
		unset($_POST['action']);
		switch ($action) {
			// ignore a listing
			case 'ignore':
				if (!empty($_POST['id'])) {
					require_once(__DIR__ . '/actions/ignore.php');
					exit;
				}
				break;
			// save configuration options
			case 'save':
				if (array_key_exists('type', $_POST) 
					&& array_key_exists('minPrice', $_POST) 
					&& array_key_exists('maxPrice', $_POST) 
					&& array_key_exists('photo', $_POST) 
					&& array_key_exists('address', $_POST)) {
					
					require_once(__DIR__ . '/actions/save.php');
					exit;
				}
				break;
			// perform a search
			case 'search':
				if (!empty($_POST['location']) && substr_count($_POST['location'], ',') == 3) {
					require_once(__DIR__ . '/actions/search.php');
					exit;
				}
				break;
			// fail
			default: 
				break;
		}
	}
        
        header("HTTP/1.1 403 Forbidden"); // kbye!
?>
