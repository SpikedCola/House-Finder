<?php
	require_once(__DIR__.'/../inc.php');

	$r = new mlsRequest($user);
	$r->setLocation(POST('location'));
	$response = $r->fetch();
	
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

	echo json_encode($ret);
	
        /**
         * This takes the raw JSON object from MLS and converts it into something
         * we can pass back to our page. We also need to check if any of the IDs
         * in the response are being ignored by the user.
         * 
         * @param Object $results The JSON object to parse
         * @return array An array of listings
         */
        function processResults($results) {
                global $db, $user;
                $ret = array();
		
		$q = new Query();
		$q->addTable('ignored_listings');
		$q->addWhere('user_id', $user->user_id);
                $q->addColumn('listing_id');
		$ignored = $db->getAssoc($q, 'listing_id', 'listing_id');

                foreach ($results as $result) {
                        // skip the result if we're ignoring it
                        if (isset($ignored[$result->MLS])) {
                                continue;
                        }
                        
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
					case 'PidKey': 
                                                $listing['pidKey'] = $value;
						break;
					case 'PropertyID':
                                                $listing['propertyId'] = $value;
						break;
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
        
			if ($user->photos && !$listing['photos']) {
				// skip this listing if the user only 
				// wants listings with photos
				continue;
			}
			
			if ($user->address && !$listing['address']) {
				// skip this listing if the user only 
				// wants listings with an address
				continue;
			}	
			
                        $listing['bedrooms']['total'] = $listing['bedrooms']['above'] + $listing['bedrooms']['below'];
                        
                        ksort($listing); // for consistency
                        
                        $ret[] = $listing;
                }
                
                return $ret;
        }	
?>