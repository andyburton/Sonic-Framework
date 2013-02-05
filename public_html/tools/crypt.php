<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set variables

$original	= '';
$type		= '';
$crypt		= '';

// If the form is submitted

if (isset ($_POST['crypt']))
{
	
	// Get variables
	
	$original	= $_POST['original'];
	$type		= $_POST['type'];
	
	// Crypt
	
	switch ($type)
	{
		
		case 'bcrypt':
			$crypt	= \Sonic\Resource\User::_Hash ($original);
			break;
		
	}
	
}

// Set template variables

$tpl->assign ('original',	$original);
$tpl->assign ('type',		$type);
$tpl->assign ('crypt',		$crypt);
$tpl->assign ('options',	array (
	'bcrypt'	=> 'bcrypt'
));

// Display

$tpl->display ('crypt.tpl');