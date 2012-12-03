<?php
        require_once('Smarty/Smarty.class.php');
	require_once(__DIR__ . '/classes/db.php');
	
	$db = new Db();
        
	// if the user has never been here before, cookie them
	// this way, we can allow them to hide ads "forever".
	// also, lets store this value so that, when the user hides
	// an ad, we can validate their uniqueId
	
	if (empty($_COOKIE['uniqueId'])) {
		$value = uniqid('', true);

		// this should never happen, but who knows
		if ($db->user_exists($value)) {
			$value = uniqid('', true);
		}
		
		$db->add_user($value);
		setcookie('uniqueId', $value, 0x7FFFFFFF, '/');
	}
	
        $template = new Smarty();
        
        $template->display(__DIR__ . '/templates/index.tpl');
?>
