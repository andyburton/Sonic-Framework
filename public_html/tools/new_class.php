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

// If there is a table reload

if (isset ($_POST['reload_tables']) && $_POST['reload_tables'] == '1')
{
	$class->fromPost ();
}

// If the form is submitted or there is a URL action (from an overwrite/merge request)

if (isset ($_POST['create_class']) || isset ($_POST['create_save_class']) || 
	(isset ($_POST['create_action']) && !empty ($_POST['create_action'])))
{
	
	// Set action, overwrite and merge status
	
	$action		= $_POST['create_action'] == 'save' || isset ($_POST['create_save_class'])? 'save' : 'view';
	$overwrite	= $_POST['class_overwrite'] == '1';
	$merge		= $_POST['class_merge'] == '1';
	
	// Get the class details from the form
	
	$class->fromPost ();

	// Split namespace to work out directory

	$namespace	= explode ('\\', $class->get ('namespace'));

	// Set class path

	$classDir	= ABS_CLASSES . implode ('/', $namespace) . '/';
	$className	= $class->get ('name') . '.php';
	$classPath	= $classDir . $className;
	
	// If the class already exists and there is no overwrite or merge
	
	if (file_exists ($classPath) && !$overwrite && !$merge)
	{
		
		// Request whether to overwrite or merge
		
		$tplPage	= 'newclass_exists.tpl';
		$tpl->assign ('action',		$action);
		
	}
	
	// Else the class doesn't exist or we are overwriting or merging it
	
	else
	{
		
		// If we are merging
		
		if ($merge)
		{
			
			// Load the existing class
			
			$classData	= file_get_contents ($classPath);
			
			// Generate new attributes
			
			$class->tabUp (FALSE);
			$class->generateAttributes ();
			
			// Replace attributes
			
			$properties	= Model\Tools\Comment::phpdocComment ($class->generateProperties ());
			
			if (preg_match ('/\/\*\*\n \* Class Properties:\n(.*?)\n \*\//si', $classData) == 1)
			{
				$classData	= preg_replace ('/\/\*\*\n \* Class Properties:\n(.*?)\n \*\//si', $properties, $classData);
			}
			else
			{
				$classData	= preg_replace ('/\nclass ' . $class->get ('name') . '/si', "\n" . $properties . "\n\n" . 'class ' . $class->get ('name'), $classData);
			}
			
			$classData	= preg_replace ('/\tprotected static \$attributes(.*?)\);\n/si', $class->class, $classData);
			
		}
		
		// Else just generate the class from scratch
		
		else
		{
			$classData	= $class->Generate ();
		}

		// Set page variables

		$tpl->assign ('class_generated', $classData);
		$tplPage	= 'newclass_submitted.tpl';
		
		// If save

		if ($action == 'save')
		{

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
	
}

// Set classes

$tpl->assign ('class',	$class);
$tpl->assign ('db',		$db);

// Display

$tpl->display ($tplPage);