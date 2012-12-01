<?php
	// request for results at given location
        if (array_key_exists('location', $_POST) && array_key_exists('uniqueId', $_COOKIE) && !empty($_POST['location'])) {
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
                exit;
        }
	// request to ignore a certain mls id given a certain uniqueId
	else if (array_key_exists('id', $_POST) && array_key_exists('uniqueId', $_COOKIE)) {
		require_once(__DIR__ . '/classes/db.php');
		$db = new Db();
		
		if ($db->user_exists($_COOKIE['uniqueId'])) {
			$query = $db->prepare('
				INSERT INTO ignored_listings 
				(user_id, listing_id, date) VALUES (?, ?, UNIX_TIMESTAMP())
			');

			$query->bind_param('ii', $_COOKIE['uniqueId'], $_POST['id']);

			$query->execute();

			exit;
		}
	}
        
        header("HTTP/1.1 403 Forbidden");
	
        function processResults($results) {
                $ret = array();
                
                foreach ($results as $result) {
                        $imageUrl = str_replace('lowres', 'highres', $result->PropertyLowResImagePath);

                        $listing = array(
                            'bedrooms' => array(
                                'above' => 0,
                                'below' => 0,
                                'total' => 0
                            ),
                            'photos' => array()
                        );
                        
                        foreach ($result as $key => $value) {
                                switch ($key) {
                                        case 'MLS':
                                                $listing['id'] = $value;
                                                break;
                                        case 'LeaseRent':
                                                $listing['price'] = $value;
                                                break;
                                        case 'LeaseRentPerTime':
                                                $listing['frequency'] = strtolower($value);
                                                break;
                                        case 'Address':
                                                $listing['address'] = ucwords(strtolower($value));
                                                break;
                                        case 'Bathrooms':
                                                $listing['bathrooms'] = $value;
                                                break;
                                        case 'BedroomsAboveGround':
                                                if (is_numeric($value)) {
                                                        $listing['bedrooms']['above'] = $value;
                                                }
                                                break;
                                        case 'BedroomsBelowGround':
                                                if (is_numeric($value)) {
                                                        $listing['bedrooms']['below'] = $value;
                                                }
                                                break;
                                        case 'Latitude':
                                                $listing['latitude'] = $value;
                                                break;
                                        case 'Longitude':
                                                $listing['longitude'] = $value;
                                                break;
                                        case 'PropertyLowResPhotos':
                                                $photos = array();

                                                // to make life easier, store the whole URL
                                                foreach ($value as $p) {
                                                        $photos[] = $imageUrl . $p;
                                                }

                                                $listing['photos'] = $photos;
                                                break;
                                        default: 
                                                break;
                                }
                        }
        
                        $listing['bedrooms']['total'] = $listing['bedrooms']['above'] + $listing['bedrooms']['below'];
                        
                        ksort($listing); // for consistency
                        
                        $ret[] = $listing;
                }
                
                return $ret;
        }	
?>
