<?php

class mlsRequest {
	/**
	 * @var mlsUser
	 */
	protected $user = null;
	
	/**
	 * @var DOMDocument
	 */
	protected $doc = null;
	
	/**
	 * @var DOMNode
	 */
	protected $root = null;
	
	/**
	 * Request constructor. Requires a user since request
	 * details will be user-specific.
	 * 
	 * @param mlsUser $user
	 */
	public function __construct(mlsUser $user) {
		$this->user = $user;
		$this->doc = new DOMDocument();
		$this->doc->preserveWhitespace = true;
		$this->doc->formatOutput = true;
		$this->root = $this->doc->createElement('ListingSearchMap');
		$this->doc->appendChild($this->root);
		$this->addDefaultElements();
		$this->addUserElements();
	}
	
	/**
	 * Add all the static elements to our XML doc
	 */
	protected function addDefaultElements() {
		$this->root->appendChild($this->doc->createElement('Culture', 'en-CA'));
		$this->root->appendChild($this->doc->createElement('OrderBy', '1'));
		$this->root->appendChild($this->doc->createElement('OrderDirection', 'A'));
		$this->root->appendChild($this->doc->createElement('PropertyTypeID', 300));
		$this->root->appendChild($this->doc->createElement('MinBath', 0));
		$this->root->appendChild($this->doc->createElement('MaxBath', 0));
		$this->root->appendChild($this->doc->createElement('MinBed', 0));
		$this->root->appendChild($this->doc->createElement('MaxBed', 0));
		$this->root->appendChild($this->doc->createElement('StoriesTotalMin', 0));
		$this->root->appendChild($this->doc->createElement('StoriesTotalMax', 0));
	}
	
	/**
	 * Add the user-specific elements to our XML doc
	 */
	protected function addUserElements() {
		if ($this->user->search_type == 'rent') {
			$this->root->appendChild($this->doc->createElement('TransactionTypeID', 3)); // rent
			$this->root->appendChild($this->doc->createElement('LeaseRentMin', $this->user->min_price));
			$this->root->appendChild($this->doc->createElement('LeaseRentMax', $this->user->max_price));
		}
		else {
			$this->root->appendChild($this->doc->createElement('TransactionTypeID', 2)); // sale
			$this->root->appendChild($this->doc->createElement('PriceMin', $this->user->min_price));
			$this->root->appendChild($this->doc->createElement('PriceMax', $this->user->max_price));
		}
	}
	
	/**
	 * Sets the location of the request to a specific bounds string
	 * 
	 * @param string $value A string in the form "lat_lo,lng_lo,lat_hi,lng_hi" 
	 * @throws Exception If location value is bad
	 */
	public function setLocation($value) {
		// 'Returns a string of the form "lat_lo,lng_lo,lat_hi,lng_hi" 
		// for this bounds, where "lo" corresponds to the SW of the 
		// bounding box and "hi" corresponds to the NE corner of the box'
		$parts = explode(',', $value, 4);
		if (count($parts) != 4) {
			throw new Exception('Invalid location value: '.$value);
		}
		list($lowLat, $lowLng, $highLat, $highLng) = $parts;

		// add a bit of padding to the user's box so we dont return results 
		// right on/over the edge
		$padding = 0.00125; 

		$fields = [
		    'LatitudeMax' => $highLat - $padding,
		    'LatitudeMin' => $lowLat + $padding,
		    'LongitudeMax' => $highLng - $padding,
		    'LongitudeMin' => $lowLng + $padding
		];
		foreach ($fields as $field => $value) {
			$node = $this->doc->createElement($field, $value);
			if (($elements = $this->doc->getElementsByTagName($field)) && $elements->length) {
				$this->root->replaceChild($node, $elements->item(0));
			}
			else {
				$this->root->appendChild($node);
			}
		}
	}
	
	/**
	 * Builds the XML for our current request
	 * 
	 * @return string
	 */
	public function buildXml() {
		return $this->doc->saveXML();
	}
	
	/**
	 * Fetches the response from MLS
	 * 
	 * @return string
	 * @throws Exception If HTTP return code is not 200
	 */
	public function fetch() {
		$ch = curl_init('http://www.realtor.ca/handlers/MapSearchHandler.ashx?xml='.urlencode($this->buildXml()));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11');
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if ($info['http_code'] != 200) {
			throw new Exception('Got something besides 200: '.print_r($info, true).print_r($response, true));
		}
		
		// need to escape backslashes so json_decode doesnt error out :)
		return str_replace('\\', '\\\\', $response);
	}
}
