<?php

/**
 * Basic user class
 * 
 * @author Jordan Skoblenick <jordan@pause.ca> Jul 12, 2013 
 */	

class mlsUser extends DbTable {
	protected $tableName = 'users';
	
	protected function beforeCreate() {
		$this->data['timestamp'] = time();
		$this->data['user_id'] = uniqid('', true);
	}
	
	public function dropUserCookie() {
		setcookie('uniqueId', $this->user_id, 0x7FFFFFFF, '/');
	}
	
	/**
	 * Checks if a user has ignored the specified $listingId
	 * 
	 * @param mixed $listingId
	 * @param mixed $providerName
	 * @return boolean True if the user has ignored the specified $listingId
	 */
	public function hasIgnoredListing($listingId, $providerName) {
		// @TODO: may want to cache the user's list of ignored listings 
		// instead of individually querying... 
		$q = new Query();
		$q->addTable('ignored_listings');
		$q->addWhere('user_id', $this->user_id);
		$q->addWhere('listing_id', $listingId);
		$q->addWhere('provider', $providerName);
		
		return $this->db->getCount($q) > 0;		
	}
}