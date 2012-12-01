<?php
	// Returns a string of the form "lat_lo,lng_lo,lat_hi,lng_hi" for this bounds, 
	// where "lo" corresponds to the SW of the bounding box and
	// "hi" corresponds to the NE corner of the box.
	$location = $_POST['location'];
	list($lowLat, $lowLng, $highLat, $highLng) = explode(',', $location);

	$ret = array(
	    'status' => 'error',
	    'results' => array()
	);

	$xmlFile = __DIR__ . '/request.xml';
	$xml = simplexml_load_file($xmlFile);

	// add a bit of padding to the user's box so we dont return results 
	// right on/over the edge

	$padding = 0.00125; // this seems okay

	$xml->LatitudeMax = $highLat - $padding;
	$xml->LatitudeMin = $lowLat + $padding;
	$xml->LongitudeMax = $highLng - $padding;
	$xml->LongitudeMin = $lowLng + $padding;

	// feeling lazy and dont really want to implement domdocument,
	// just use it to remove whitespace in xml
	$dom = new DOMDocument(); 
	$dom->preserveWhiteSpace = false; 
	$dom->formatOutput = false; 
	$dom->loadXML($xml->asXML()); 
	$xmlString = $dom->saveXML();

	$url = 'http://www.realtor.ca/handlers/MapSearchHandler.ashx?xml=' . urlencode($xmlString);

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11');

	$response = curl_exec($ch);
	$info = curl_getinfo($ch);

	curl_close($ch);

	if (!empty($response) && $info['http_code'] == 200) {
		// need to escape backslashes so json_decode doesnt error out :)
		$json = json_decode(str_replace('\\', '\\\\', $response));

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

	echo json_encode($ret);
?>
