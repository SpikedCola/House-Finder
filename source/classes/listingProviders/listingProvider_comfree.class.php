<?php

/**
 * 
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 2015-06-30
 */

class listingProvider_comfree extends listingProvider {
	public function getNiceName() {
		return 'ComFree';
	}
	public function getListings() {
		throw new Exception('Unimplemented');
	}
	
	public function setMinimumLotSizeInAnyDirectionFilter($minimumSize) {
		throw new Exception('Unimplemented');
	}
	public function setPriceFilter($minPrice, $maxPrice) {
		throw new Exception('Unimplemented');
	}
	
}