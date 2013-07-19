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
	
	protected function afterCreate() {
		setcookie('uniqueId', $this->user_id, 0x7FFFFFFF, '/');
	}
	
	public function CreateUser() {
		$user = new mlsUser();
		$user->save();
		return $user;
	}
}