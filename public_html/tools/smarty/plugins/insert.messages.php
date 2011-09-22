<?php

// Smarty insert function

function smarty_insert_messages ($params, &$tpl)
{
	
	// Set messages
	
	$messages	= NULL;
	
	if (isset ($_GET['message']) || isset ($_GET['error']) || $tpl->getTemplateVars ('msg_success') || $tpl->getTemplateVars ('msg_error'))
	{

		$messages	= '<div class="message_container">';
		$messages	.= isset ($_GET['message'])? '<div class="message_success"><p>' . $_GET['message'] . '</p></div>' : NULL;
		$messages	.= $tpl->getTemplateVars ('msg_success')? '<div class="message_success"><p>' . $tpl->getTemplateVars ('msg_success') . '</p></div>' : NULL;
		$messages	.= isset ($_GET['error'])? '<div class="message_error"><p>' . $_GET['error'] . '</p></div>' : NULL;
		$messages	.= $tpl->getTemplateVars ('msg_error')? '<div class="message_error"><p>' . $tpl->getTemplateVars ('msg_error') . '</p></div>' : NULL;
		$messages	.= '</div>';
		
	}
	
	// Return messages
	
	return $messages;
	
}
