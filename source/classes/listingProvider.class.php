<?php

/**
 * 
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 2015-06-30
 */

abstract class listingProvider {
	static $db;
	protected $user;
	
	public function __construct(mlsUser $user) {
		if (!self::$db) {
			self::$db = new Db();
		}
		$this->user = $user;
	}
	
	/**
	 * Create a listing object from a provider's listing.
	 * Provider-specific
	 * 
	 * @return listing A listinb object
	 */
	abstract protected function createListingObjectFromResult($listing);
	
	/**
	 * Gets the listings for this provider and applies current filters to results before returning listings
	 * 
	 * @param string $location Google maps bounding box string
	 * @return array<listing> An array of listing objects
	 */
	abstract public function getListings($location);
	
	/*
	 * Gets the name of this provider
	 *
	 * @return string Nice name of this provider 
	 */
	abstract public function getNiceName();
	
	/**
	 * Parses a google maps location string and adds a bit of padding
	 * 
	 * @param string $location A string in the form "lat_lo,lng_lo,lat_hi,lng_hi", where "lo" is the SW corner, and "hi" is the NE corner of the bounding box
	 * @return array With fields "LatitudeMax/Min", "LongitudeMax/Min"
	 * @throws Exception
	 */
	protected function parseLocation($location) {
		$parts = explode(',', $location, 4);
		if (count($parts) != 4) {
			throw new Exception('Invalid location value: '.$location);
		}
		list($lowLat, $lowLng, $highLat, $highLng) = $parts;

		// add a bit of padding to the user's box so we dont return results 
		// right on/over the edge
		$padding = 0.00125; 

		return [
		    'LatitudeMax' => $highLat - $padding,
		    'LatitudeMin' => $lowLat + $padding,
		    'LongitudeMax' => $highLng - $padding,
		    'LongitudeMin' => $lowLng + $padding
		];
	}
}
