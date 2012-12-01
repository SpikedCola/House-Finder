<?php
	// any actions will be user-specific, so check for the cookie too
        if (array_key_exists('action', $_POST) && array_key_exists('uniqueId', $_COOKIE)) {
		switch ($_POST['action']) {
			case 'ignore':
				if (!empty($_POST['']) {
					require_once(__DIR__ . '/actions/ignore.php');
					exit;
				}
				break;
			case 'save':
				if (!empty($_POST['']) {
					require_once(__DIR__ . '/actions/save.php');
					exit;
				}
				break;
			case 'search':
				if (!empty($_POST['location']) && substr_count($_POST['location'], ',') == 3) {
					require_once(__DIR__ . '/actions/search.php');
					exit;
				}
				break;
			default: 
				break;
				
			
		}
	}
		
		
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
