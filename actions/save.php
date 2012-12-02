<?php
	global $db;
	
	ksort($_POST);
	list($address, $maxPrice, $minPrice, $photos, $type) = array_values($_POST);
	
	$address = ($address == 'true') ? 1 : 0;
	$photos = ($photos == 'true') ? 1 : 0;
	$type = ($type == 'sale') ? 'sale' : 'rent';
	$minPrice = is_numeric($minPrice) ? $minPrice : 0;
	$maxPrice = is_numeric($maxPrice) ? $maxPrice : 0;
	
	$query = $db->db->prepare('
		INSERT INTO user_options 
		(user_id, address, photos, type, minPrice, maxPrice, date) VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())
	');

	$query->bind_param('siisss', $_COOKIE['uniqueId'], $address, $photos, $type, $minPrice, $maxPrice);

	$query->execute();
?>
