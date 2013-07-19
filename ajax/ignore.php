<?php

require_once(__DIR__.'/../inc.php');

if ($id = POST('id', 'int')) {
	$q = new Query();
	$q->addTable('ignored_listings');
	$q->addWhere('user_id', $user->user_id);
	$q->addWhere('listing_id', $id);
	if (array_key_exists('undo', $_POST)) {
		$db->delete($q);
	}
	else {
		$q->addField('date', time());
		$db->insert($q);
	}
}