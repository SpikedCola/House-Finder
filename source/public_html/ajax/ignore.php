<?php

require_once(__DIR__.'/../inc.php');

if (($id = POST('id')) && ($provider = POST('provider'))) {
	$data = [
	    'user_id' => $user->id,
	    'listing_id' => $id,
	    'provider' => $provider
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