<?php
        require_once('Smarty/Smarty.class.php');
        
	// if the user has never been here before, cookie them
	// this way, we can allow them to hide ads "forever"
	
	if (empty($_COOKIE['uniqueid'])) {
		$value = uniqid('', true);
		setcookie('uniqueid', $value, 0x7FFFFFFF, '/');
		$_COOKIE['uniqueid'] = $value; // so we can use the value during the current session
	}
	
        $template = new Smarty();
        
        $template->display(__DIR__ . '/templates/index.tpl');
?>
