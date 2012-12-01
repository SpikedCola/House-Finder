<?php
	global $db;
	
	$query = $db->prepare('
		INSERT INTO ignored_listings 
		(user_id, listing_id, date) VALUES (?, ?, UNIX_TIMESTAMP())
	');

	$query->bind_param('ii', $_COOKIE['uniqueId'], $_POST['id']);

	$query->execute();
?>
