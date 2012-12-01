<?php
	ksort($_POST);
	list($address, $maxPrice, $minPrice, $photos, $type) = array_values($_POST);
	
	$address = ($address == 'true') ? true : false;
	$photos = ($photos == 'true') ? true : false;
	$type = ($type == 'sale') ? 'sale' : 'rent';
	$minPrice = is_numeric($minPrice) ? $minPrice : 0;
	$maxPrice = is_numeric($maxPrice) ? $maxPrice : 0;
	
	$query = $db->prepare('
		INSERT INTO user_options 
		(user_id, address, photos, type, minPrice, maxPrice, date) VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())
	');

	$query->bind_param('ii', $_COOKIE['uniqueId'], $_POST['id']);

	$query->execute();
?>
