<?php
        require_once('Smarty/Smarty.class.php');
        
        
   //     print_r($json);
        
        $template = new Smarty();
        
        $template->display(__DIR__ . '/templates/index.tpl');
?>
