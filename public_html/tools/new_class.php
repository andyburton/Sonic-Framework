<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set template to display

$tplPage	= 'newclass.tpl';

// Create tool classes

$class		= new Model\Tools\NewClass;
$db			= new Model\Tools\Db;

// If the form is submitted

if (isset ($_POST['create_class']) || isset ($_POST['create_save_class']))
{
	
	// Set template to display

	$tplPage	= 'newclass_submitted.tpl';
	
	// Get the class details from the form
	
	$class->fromPost ();
	
	// Generate class
	
	$classData	= $class->Generate();
	
	// Set page variables
	
	$tpl->assign ('class_generated', $classData);
	
	// If save
	
	if (isset ($_POST['create_save_class']))
	{
		
		// Split namespace to work out directory
		
		$namespace	= explode ('\\', $class->get ('namespace'));
		
		// Set class path
		
		$classDir	= ABS_CLASSES . implode ('/', $namespace) . '/';
		$className	= $class->get ('name') . '.php';
		$classPath	= $classDir . $className;
		
		// Make directory
		
		if (!is_dir ($classDir))
		{
			mkdir ($classDir, 0700, TRUE);
		}
		
		// Write class
		
		if (file_put_contents ($classPath, $classData))
		{
			$tpl->assign ('msg_success', 'Class saved to ' . $classPath);
		}
		else
		{
			$tpl->assign ('msg_error', 'Class could not be saved to ' . $classPath);
		}
		
	}
	
}

// Set classes

$tpl->assign ('class',	$class);
$tpl->assign ('db',		$db);

// Display

$tpl->display ($tplPage);