<?php

require_once(__DIR__.'/../inc.php');

if ($id = POST('id')) {
	$data = [
	    'user_id' => $user->id,
	    'listing_id' => $id
	];
	$q = new Query();
	$q->addTable('ignored_listings');
	$q->addWhere($data);
	$q->addFields($data);
	if (array_key_exists('undo', $_POST)) {
		$db->delete($q);
	}
	else {
		$q->addField('timestamp', time());
		$db->insert($q);
	}
}