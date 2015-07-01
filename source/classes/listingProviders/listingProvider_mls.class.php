<?php

/**
 * 
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 2015-06-30
 */
// config
define('MLS_API_URL', 'http://www.realtor.ca/api/Listing.svc/PropertySearch_Post');

// # of records at once seems to be limited on their end.
// had success with up to 432 records at once, but this seems 
// unreliable. instead grab less and page through all the data
define('RECORDS_TO_FETCH_AT_ONCE', 200);

define('MLS_CACHE_DIR', __DIR__.'/../../cache/mls/');
define('MLS_CACHE_TIME', 60 * 60 * 24); // 1 day

class listingProvider_mls extends listingProvider {

	public function getNiceName() {
		return 'MLS';
	}

	/**
	 * Matches up with 'BuildingTypeId' in the request
	 * 
	 * @var array
	 */
	public $BuildingTypeIds = [
	    'Any' => 0,
	    'House' => 1,
	    'Row / Townhouse' => 16,
	    'Apartment' => 17,
	    'Duplex' => 2,
	    'Triplex' => 3,
	    'Fourplex' => 19,
	    'Garden Home' => 20,
	    'Mobile Home' => 6,
	    'Manufactured Home/Mobile' => 27,
	    'Special Purpose' => 12,
	    'Residential Commercial Mix' => 5,
	    'Other' => 14,
	    'Manufactured Home' => 29,
	    'Commercial Apartment' => 28
	];

	/**
	 * Matches up with 'OwnershipTypeGroupId' in the request
	 * 
	 * @var array
	 */
	public $OwnershipTypeGroupIds = [
	    'Any' => 0,
	    'Freehold' => 1,
	    'Condo/Strata' => 2,
	    'Timeshare/Fractional' => 3,
	    'Leasehold' => 4
	];

	/**
	 * Matches up with 'BuildingStyleId' in the request
	 * 
	 * @var array
	 */
	public $BuildingStyleIds = [
	    'All' => 0,
	    'Detached' => 3,
	    'Semi-detached' => 5,
	    'Attached' => 1,
	    'Link' => 9
	];

	/**
	 * Matches up with 'TransactionTypeId' in the request
	 * 
	 * @var array
	 */
	public $TransactionTypeIds = [
	    'For Sale And Rent' => 1,
	    'For Sale' => 2,
	    'For Rent' => 3
	];

	/**
	 * Default options for MLS request. Filters will be added after. 
	 * Hardcoded some options here, eg. Freehold / House / Detached,
	 * which are non-negotiable
	 * 
	 * @return type
	 */
	protected function getDefaultRequestData() {
		return [
		    'RecordsPerPage' => RECORDS_TO_FETCH_AT_ONCE,
		    'MaximumResults' => RECORDS_TO_FETCH_AT_ONCE,
		    'BuildingStyleId' => $this->BuildingStyleIds['Detached'],
		    'BuildingTypeId' => $this->BuildingTypeIds['House'],
		    'OwnershipTypeGroupId' => $this->OwnershipTypeGroupIds['Freehold'],
		    'CultureId' => 1, // this must be set
		    'ApplicationId' => 1, // more results come back without this... investigate
		    // these dont seem to have any effect (yet):
//		    'PropertyTypeId' => 300,
//		    'TransactionTypeId' => 2,
//		    'viewState' => 'm',
		    'SortOrder' => 'A',
		    'SortBy' => 1,
//		    'BedRange' => '0-0',
//		    'BathRange' => '0-0',
//		    'ParkingSpaceRange' => '0-0',
		    'CurrentPage' => 1
		];
	}

	public function getListings($location) {
		$requestData = $this->getDefaultRequestData();

		// apply pre-request filters
		$parsedLocation = $this->parseLocation($location);
		$requestData['LatitudeMin'] = $parsedLocation['LatitudeMin'];
		$requestData['LatitudeMax'] = $parsedLocation['LatitudeMax'];
		$requestData['LongitudeMin'] = $parsedLocation['LongitudeMin'];
		$requestData['LongitudeMax'] = $parsedLocation['LongitudeMax'];
		$requestData['PriceMin'] = $this->user->min_price;
		$requestData['PriceMax'] = $this->user->max_price;

		switch ($this->user->search_type) {
			case 'sale':
				$requestData['TransactionTypeId'] = $this->TransactionTypeIds['For Sale'];
				break;
			case 'rent':
				$requestData['TransactionTypeId'] = $this->TransactionTypeIds['For Rent'];
				break;
			case 'both':
				$requestData['TransactionTypeId'] = $this->TransactionTypeIds['For Sale and Rent'];
				break;
		}

		$listingObjects = [];

		$page = 1;
		$totalPages = 1;
		do {
			// set page on each pass
			$requestData['CurrentPage'] = $page;
			$response = Request::post(MLS_API_URL, $requestData); //, [CURLOPT_COOKIE=>'SHOW_ADV_HME_SRCH=1']); // maybe helpful cookie?

			$json = $this->validateResponseAndJSON($requestData, $response);
			// curious which of these is 
			if ($json->Paging->TotalRecords > $json->Paging->RecordsShowing) {
				// this is bad - we wont see all possible listings.. might want to do something about this
				throw new Exception('too many listings?');
			}
			
			$totalPages = $json->Paging->TotalPages;

			$objects = $this->createListingObjects($json->Results);
			$listingObjects = array_merge($listingObjects, $objects);
		}
		while ($page++ < $totalPages);
		
		return $listingObjects;
	}
	
	protected function createListingObjects($results) {
		$listingObjects = [];
		
		// @TODO: clean up exceptions, maybe wrap the whole block below and add extra exception debug data in one shot
			
		// we can only check land size after fetching MLS data
		// loop response and remove anything not meeting our criteria
		foreach ($results as $listing) {
			if ($this->user->hasIgnoredListing($listing->Id, $this->getNiceName())) {
				// skip listing if user has already seen and ignored it
				continue;
			}

			// if we have enabled lot size filtering, do it here, before the object is even created
			if ($this->user->min_lot_size > 0) {
				// some listings are missing "Land" ... 
				// not sure how for a house, maybe it makes sense in another scenario
				if (!empty($listing->Land)) {
					// (keep these)
					// have seen SizeTotal and SizeFrontage fields -
					// curious what else is there, these will let me know
					if (empty($listing->Land->SizeTotal) && empty($listing->Land->SizeFrontage)) {
						throw new Exception('Land->SizeTotal field doesnt exist on this listing: '.print_r($listing, true).print_r($response, true));
					}
					if (count($listing->Land) > 1) {
						throw new Exception('!!! more than 1 Land sub-field on this listing: '.print_r($listing, true).print_r($response, true));
					}
					// (end keep these)
					// attempt to parse total land size into a length and width,
					// so I can determine if it's too narrow
					if (!empty($listing->Land->SizeTotal)) {
						if ($parsedSize = $this->parseLandSizeFromText($listing->Land->SizeTotal)) {
							// only check/skip listing if parsing is successful (better to include too much than ignore something good)
							if (($parsedSize['x'] < $this->user->min_lot_size) || ($parsedSize['y'] < $this->user->min_lot_size)) {
								// this property is too small!!
								// don't create an object below
								continue;
							}
						}
					}
				}
			}

			$listingObjects[] = $this->createListingObjectFromResult($listing);
		}
		
		return $listingObjects;
	}

	protected function createListingObjectFromResult($listing) {
		$listingObj = new listing();
		// ID
		$listingObj->id = $listing->Id;

		// MLS info
		$listingObj->MLSnumber = $listing->MlsNumber;
		$listingObj->MLSlink = "http://www.realtor.ca{$listing->RelativeDetailsURL}";

		// price
		$listingObj->price = $listing->Property->Price;

		// address data
		$listingObj->address['latitude'] = $listing->Property->Address->Latitude;
		$listingObj->address['longitude'] = $listing->Property->Address->Longitude;
		// AddressText uses a pipe to separate lines 1 & 2 (possibly more)
		$listingObj->address['text'] = str_replace('|', "\n", $listing->Property->Address->AddressText);

		// bedrooms/bathrooms
		if (!empty($listing->Building)) {
			if (!empty($listing->Building->Bedrooms)) {
				// bedrooms is sometimes a number (3) and sometimes
				// stupid (3 + 0). show sum 
				$bedrooms = $listing->Building->Bedrooms;
				$matches = [];
				if (preg_match('/(\d+) \+ (\d+)/', $bedrooms, $matches)) {
					$bedrooms = (int) $matches[1] + (int) $matches[2];
				}
				$listingObj->bedrooms = $bedrooms;
			}
			if (!empty($listing->Building->BathroomTotal)) {
				$listingObj->bathrooms = $listing->Building->BathroomTotal;
			}
		}

		// land size
		if (!empty($listing->Land) && !empty($listing->Land->SizeTotal)) {
			if ($parsedSize = $this->parseLandSizeFromText($listing->Land->SizeTotal)) {
				// parsing was successful, parsed ['text'] should be most useful
				$listingObj->landSize = $parsedSize['text'];
			}
			else {
				// parsing failed, use original text
				// put a marker at the front to show parsing failed on this one
				$listingObj->landSize = "** ".$listing->Land->SizeTotal;
			}
		}

		// at the moment, not sure how to determine how many images are available in a "sequence"
		// all photos seem to be present on the listing details page, maybe just load that?
		if (!empty($listing->Property->Photo)) {
			// can be more than 1 set of photos per property, just append them all together for now
			foreach ($listing->Property->Photo as $sequence) {
				// need to find a way to find the max # of images allowed, and add them all
				// (change the _1 at the end of the image for the next image)
				$listingObj->photos[] = $sequence->HighResPath;
			}
		}

		return $listingObj;
	}

	/**
	 * Attempt to parse land size (Im going to call them X and Y) from text on page.
	 * Puts result in $size argument, and returns whether or
	 * not we "succeeded" in parsing
	 * 
	 * @param string $text Text to parse
	 * @return mixed An array if successful, otherwise false
	 * @throws Exception
	 */
	protected function parseLandSizeFromText($text) {
		$matches = [];
		
		// often size is specified with an X as a divider, like "12 X 23"
		// this should be easy to match on - lets try this first.
		// well.. this got complicated fast. appears to work, probably not perfect
		// note that we only capture the whole number (and ignore the decimal)
		// wont lose much accuracy and makes parsing easier
		if (preg_match(
			  "/"
			.   "(\d+)(?:\.\d*)?"	// number, followed by optional: decimal point and number
			.   "(?:\s*(?:ft|feet|')\s*)?"	// optional: whitespace, "ft/feet/foot symbol", whitespace
			.   "\s*(?:x|by)\s*"	// optional whitespace, "X" or "by", optional whitespace
			.   "(\d+)(?:\.\d*)?"	// number, followed by optional: decimal point and number
			.   "(?:\s*(?:ft|feet|')\s*)?"	// optional: whitespace, "ft/feet/foot symbol", whitespace
			. "/i"
			, $text, $matches)) {
			$x = $matches[1];
			$y = $matches[2];
			
			return [
			    'text' => "{$x} x {$y}",
			    'x' => $x,
			    'y' => $y
			];
		}

		return false;
	}

	protected function validateResponse($requestData, array $response) {
		// check HTTP response
		if ($response['info']['http_code'] !== 200) {
			throw new Exception('Non-200 response from MLS: '.print_r($response, true).print_r($requestData, true));
		}
	}

	/**
	 * Validate what we get back from MLS
	 * 
	 * @param mixed $requestData Request data sent to MLS (for debug only, not actually used)
	 * @param array $response Response array from MLS/cURL 
	 * @return JSON Returns JSON response object on success
	 * @throws Exception
	 */
	protected function validateResponseAndJSON($requestData, array $response) {
		// check HTTP response
		$this->validateResponse($requestData, $response);

		// decode JSON
		if (!$responseData = json_decode($response['response'])) {
			throw new Exception('Failed to parse MLS JSON: '.print_r($response, true).print_r($requestData, true));
		}

		// check JSON
		if (empty($responseData->ErrorCode) || empty($responseData->ErrorCode->Id)) {
			throw new Exception('ErrorCode/ID missing within MLS response data: '.print_r($responseData, true).print_r($requestData, true));
		}

		// check JSON error code
		if ($responseData->ErrorCode->Id !== 200) {
			throw new Exception('Non-200 response within MLS data: '.print_r($responseData, true).print_r($requestData, true));
		}

		return $responseData;
	}

	protected function getXpathNodeValueCapitalized($xpath, $xpathObj) {
		$node = $xpathObj->query($xpath);
		if ($node->length) {
			return ucwords(strtolower(trim($node->item(0)->nodeValue)));
		}
		// nothing found
		return '---';
	}

	public function getAdditionalInformation($mlsLink) {
		// load MLS page for listing, and parse out Building details not available from the API
		// ideally cache the page so we don't hammer their server for every little detail

		$html = $this->loadLinkFromCache($mlsLink);

		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		$heatingFuel = $this->getXpathNodeValueCapitalized('//span[@id="m_property_dtl_blddata_val_heatingfuel"]', $xpath);
		$heatingType = $this->getXpathNodeValueCapitalized('//span[@id="m_property_dtl_blddata_val_heatingtype"]', $xpath);
		$cooling = $this->getXpathNodeValueCapitalized('//span[@id="m_property_dtl_blddata_val_cooling"]', $xpath);
		$water = $this->getXpathNodeValueCapitalized('//span[@id="m_property_dtl_blddata_val_utilitywater"]', $xpath);
		$sewer = $this->getXpathNodeValueCapitalized('//span[@id="m_property_dtl_blddata_val_utilitysewer"]', $xpath);
		$age = $this->getXpathNodeValueCapitalized('//span[@id="ageofbuilding_value"]', $xpath);

		// photos!!
		$photos = [];
		$nodes = $xpath->query('//a[contains(@id, "propimg_lnk")]');
		if ($nodes->length) {
			foreach ($nodes as $node) {
				$photos[] = trim($node->getAttribute('href'));
			}
		}
		
		return [
		    'heatingFuel' => $heatingFuel,
		    'heatingType' => $heatingType,
		    'cooling' => $cooling,
		    'water' => $water,
		    'sewer' => $sewer,
		    'age' => $age,
		    'photos' => $photos
		];
	}

	protected function loadLinkFromCache($mlsLink) {
		// attempt to load, if old or non existant fetch new
		$filename = MLS_CACHE_DIR.md5($mlsLink).'.json';

		if (!file_exists($filename) || filemtime($filename) - time() > MLS_CACHE_TIME) {
			// get new copy
			$response = Request::get($mlsLink);

			$this->validateResponse($mlsLink, $response);

			if (!is_dir(MLS_CACHE_DIR)) {
				mkdir(MLS_CACHE_DIR);
			}
			//file_put_contents($filename, gzcompress($response['response']));
			file_put_contents($filename, $response['response']);

			return $response['response'];
		}

		//return gzdeflate(file_get_contents($filename));
		return file_get_contents($filename);
	}

}
