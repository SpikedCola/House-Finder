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
		
                $url = 'http://www.realtor.ca/handlers/MapSearchHandler.ashx?xml=' . urlencode($xml->asXML());

                $jsonResponse = file_get_contents($url);
                
                if (!empty($jsonResponse)) {
                        $json = json_decode($jsonResponse);

                        if (isset($json->MapSearchResults) && isset($json->NumberSearchResults)) {
                                // data is probably valid :)
                                $response['status'] = 'ok';
                                $response['results'] = $json->MapSearchResults;
                        }
                }
                
                echo json_encode($response);
                exit;
        }
        
        header("HTTP/1.1 403 Forbidden");
?>
