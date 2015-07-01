<?php

require_once(__DIR__.'/inc.php');

// config: set which providers should be checked here
$listingProviders = [
    new listingProvider_mls($user)
];
// end config

//////////////////////////////

// i think this is a bounding box that gets sent back
$location = POST('location');

$resultCount = 0;
$results = [];
foreach ($listingProviders as $provider) {
	$listings = $provider->getListings($location);
	$resultCount += count($listings);
	$results[] = [
	    'name' => $provider->getNiceName(),
	    'listings' => $listings
	];
}

$response = [
    'status' => 'ok',
    'resultCount' => $resultCount,
    'results' => $results
];

echo json_encode($response);

/*
	
	if ($json = json_decode($response)) {
		if (isset($json->NumberSearchResults)) {
			if ($json->NumberSearchResults > 0 && isset($json->MapSearchResults)) {
				// data is probably valid :)
				$ret['status'] = 'ok';
				$ret['results'] = processResults($json->MapSearchResults);
			}
			else if ($json->NumberSearchResults > 0 && !isset($json->MapSearchResults)) {
				// when we have a count but no results, MLS is saying "too many"
				$ret['status'] = 'toomany';
			}
			else if ($json->NumberSearchResults == 0) {
				$ret['status'] = 'none';
			}
			else {
				$ret['status'] = 'error';
				$ret['results'] = $response;
			}
		}
		else {
			$ret['status'] = 'error';
			$ret['results'] = $response;
		}
	}
	else {
		$ret['status'] = 'error';
	}

*/