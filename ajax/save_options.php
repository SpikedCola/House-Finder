<?php
/**
 * Receives an AJAX request to "save user options" such
 * as the type of property they are searching for (rent/sale),
 * minimum and maximum prices, etc
 */

require_once(__DIR__.'/../inc.php');

$response = [];

try {
	if (!$type = POST('type', 'str', array('sale', 'rent'))) {
		throw new UserException('You must specify either "sale" or "rent" for "type"');
	}
	$address = POST('address', 'bool') ? 1 : 0;
	$photos = POST('photos', 'bool') ? 1 : 0;
	if (!$minPrice = POST('minPrice', 'float', 0)) {
		$minPrice = 0;
	}
	if (!$maxPrice = POST('maxPrice', 'float', 0)) {
		$maxPrice = 0;
	}
	if ($minPrice > $maxPrice) {
		throw new UserException('maxPrice must be greater than minPrice');
	}
	
	$user->search_type = $type;
	$user->address = $address;
	$user->photos = $photos;
	$user->min_price = $minPrice;
	$user->max_price = $maxPrice;
	
	$user->save();
	
	$response['success'] = true;
}
catch (UserException $ex) {
	$response['error'] = $ex->getMessage();
}
catch (Exception $ex) {
	$response['error'] = 'An unknown error has occurred';
	$response['error'] = $ex->getMessage();
}

die(json_encode($response));