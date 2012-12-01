<?php
        require_once('Smarty/Smarty.class.php');
	require_once(__DIR__ . '/classes/db.php');
        
	// if the user has never been here before, cookie them
	// this way, we can allow them to hide ads "forever".
	// also, lets store this value so that, when the user hides
	// an ad, we can validate their uniqueId
	
	if (empty($_COOKIE['uniqueId'])) {
		$value = uniqid('MLS', true);
		setcookie('uniqueId', $value, 0x7FFFFFFF, '/');
		$db = new Db();
		$db->add_user($value);
	}
	
        $template = new Smarty();
        
        $template->display(__DIR__ . '/templates/index.tpl');
?>
