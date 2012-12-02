<?php
	global $db;
	
	if (array_key_exists('undo', $_POST)) {
		$query = $db->db->prepare('
			DELETE FROM ignored_listings 
			WHERE user_id = ?
			AND listing_id = ?
		');

	}
	else {
		$query = $db->db->prepare('
			INSERT INTO ignored_listings 
			(user_id, listing_id, date) VALUES (?, ?, UNIX_TIMESTAMP())
		');
	}
	
	$query->bind_param('ss', $_COOKIE['uniqueId'], $_POST['id']);

	$query->execute();
?>
