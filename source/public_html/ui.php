<?php

/**
 * @author Jordan Skoblenick <jordan@pause.ca> Jul 12, 2013 
 */
require_once(__DIR__.'/inc.php');

global $template;

$template = new Template();
$template->wrapper = 'wrapper.tpl';
$template->assign('user', $user);
