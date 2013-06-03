<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set variables

$date		= '';
$format		= 'd/m/Y H:i:s';
$timestamp	= '';

// If the form is submitted

if (isset ($_POST['format_timestamp']))
{
	
	// Get date format
	
	$format		= $_POST['date_format'];
	
	// Get timestamp
	
	$timestamp	= $_POST['timestamp'];
	
	// Format
	
	$date		= date ($format, $timestamp);
	
}

// Else if format date

else if (isset ($_POST['format_date']))
{
	
	// Get date format
	
	$format		= $_POST['date_format'];
	
	// Get date
	
	$date		= $_POST['date'];
	
	// Convert to timestamp
	
	$timestamp	= Sonic::getResource ('parser')->convertToUnixtime ($date);
	
}

// Set template variables

$tpl->assign ('date',			$date);
$tpl->assign ('date_format',	$format);
$tpl->assign ('timestamp',		$timestamp);

// Display

$tpl->display ('timestamp.tpl');