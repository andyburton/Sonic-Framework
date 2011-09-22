<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set links

$links	= array ();

// set hidden files and folders

$hidden	= array ('.', '..', '.svn', 'css', 'gfx', 'js', 'index.php');

// For each path in the directory

foreach (scandir ('./') as $path)
{
	
	// If the path is not hidden
	
	if (!in_array ($path, $hidden))
	{
		
		// Add to links
		
		$links[]	= $path;
		
	}
	
}

// Set links

$tpl->assign ('links', $links);

// Display

$tpl->display ('index.tpl');