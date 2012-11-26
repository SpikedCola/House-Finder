<?php
        if (array_key_exists('location', $_POST) && array_key_exists('uniqueid', $_COOKIE) && !empty($_POST['location'])) {
		// Returns a string of the form "lat_lo,lng_lo,lat_hi,lng_hi" for this bounds, 
		// where "lo" corresponds to the SW of the bounding box and
		// "hi" corresponds to the NE corner of the box.
		$location = $_POST['location'];
		list($lowLat, $lowLng, $highLat, $highLng) = explode(',', $location);
		
                $response = array(
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

                $jsonResponse = get($url);

		// need to escape backslashes so json_decode doesnt error out :)
		$jsonResponse['response'] = str_replace('\\', '\\\\', $jsonResponse['response']);
		
		if (!empty($jsonResponse['response'])) {
                        $json = json_decode($jsonResponse['response']);

                        if (isset($json->NumberSearchResults)) {
				if ($json->NumberSearchResults > 0 && isset($json->MapSearchResults)) {
					// data is probably valid :)
					$response['status'] = 'ok';
					$response['results'] = $json->MapSearchResults;
				}
				else if ($json->NumberSearchResults >= 500 && !isset($json->MapSearchResults)) {
					$response['status'] = 'toomany';
				}
				else if ($json->NumberSearchResults == 0) {
					$response['status'] = 'none';
				}
				else {
					$response['status'] = 'error';
					$response['results'] = $jsonResponse['response'];
				}
                        }
			else {
				$response['status'] = 'error';
				$response['results'] = $jsonResponse['response'];
			}
                }
                
                echo json_encode($response);
                exit;
        }
        
        header("HTTP/1.1 403 Forbidden");
	
	/**
	 * GET a url
	 * 
	 * @param string $url Url to get
	 * @param bool $doLoginCheck False to bypass "should login" check
	 * @return array containing response, info and error
	 */
	function get($url) {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11');

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);

		curl_close($ch);

		return array(
		    'response' => $response,
		    'info' => $info,
		    'error' => $error
		    );
	}
?>
