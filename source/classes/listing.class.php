<?php

/**
 * Class to represent a generic listing
 * 
 * To be "filled in" by each listing provider's class
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 2015-06-30
 */
class listing {
	
	public $MLSnumber = 0;
	
	public $id = 0;
	
	public $bedrooms = 0;
	
	public $bathrooms = 0;
	
	public $landSize = null;
	
	public $address = [
	    'text' => null,
	    'latitude' => 0,
	    'longitude' => 0
	];
	
	public $photos = [];

}
