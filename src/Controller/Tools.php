<?php

// Define namespace

namespace Sonic\Controller;

use Sonic\Model\Tools\Comment;

// Start Tools Class

class Tools extends \Sonic\Controller
{
	
	
	/**
	 * Constructor
	 */
	
	public function __construct ()
	{
		if (!defined ('MODE_TOOLS') || MODE_TOOLS !== TRUE)
		{
			exit;
		}
	}
	
    
	/**
	 * Index action
	 */
	
    public function index () 
    {
    }
	
	
	/**
	 * Clear the view cache
	 */
	
	public function clearcache ()
	{
		
		if ($this->view instanceof \Sonic\View\Smarty)
		{
			$this->view->clearCompiledTemplate ();
			$this->view->clearAllCache ();
		}
		
		new \Sonic\Message ('success', 'Cache Cleared');
		$this->template = 'tools/index.tpl';
		
	}
	
	
	/**
	 * PHP info
	 */

	public function info ()
	{
		$this->phpinfo ();
	}
	
	public function phpinfo ()
	{
		phpinfo ();
		exit;
	}
	
	
	/**
	 * Session
	 */
	
	public function session ()
	{
		session_start ();
		$this->view->assign ('session', print_r ($_SESSION,1));
	}
	
	
	/**
	 * Comment
	 */
	
	public function comment ()
	{
		
		// Set comment variables

		$comment		= '';
		$commentClean	= '';

		// If the form is submitted

		if (isset ($_POST['create_comment']))
		{

			// Set clean comment

			$commentClean	= Comment::cleanComment ($_POST['comment']);

			// Switch the type

			switch ($_POST['comment_type'])
			{

				// Line comment

				case 'line':
					$comment	= Comment::lineComment ($_POST['comment']);
					break;

				// PHPDoc comment

				case 'phpdoc':
					$comment	= Comment::phpdocComment ($_POST['comment']);
					break;

				// Star comment

				case 'star':
				default:
					$comment	= Comment::starComment ($_POST['comment']);
					break;

			}

		}

		// Set comment

		$this->view->assign ('comment',			$comment);
		$this->view->assign ('clean_comment',	$commentClean);
		
	}
	
	
	/**
	 * Crypt
	 */
	
	public function crypt ()
	{
		
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
				
				case 'md5':
					$crypt	= md5 ($original);
					break;

				case 'sha1':
					$crypt	= sha1 ($original);
					break;

				case 'sha256':
					$crypt	= \Sonic\Resource\Crypt::_sha256 ($original);
					break;

				case 'bcrypt':
					$crypt	= \Sonic\Resource\User::_Hash ($original);
					break;

			}

		}

		// Set template variables

		$this->view->assign ('original',	$original);
		$this->view->assign ('type',		$type);
		$this->view->assign ('crypt',		$crypt);
		$this->view->assign ('options',		[
			'bcrypt'	=> 'bcrypt',
			'md5'		=> 'md5',
			'sha1'		=> 'sha1',
			'sha256'	=> 'sha256'
		]);
		
	}
	
	
	/**
	 * API Permissions
	 */
	
	public function permissions ()
	{
		$this->view->assign ('modules', \Sonic\Model\Api\Module::_getObjects (['orderby' => 'name']));
	}
	
	
	/**
	 * Timestamps
	 */
	
	public function timestamp ()
	{
		
		// Set variables
		
		$date		= '';
		$format		= 'd/m/Y H:i:s';
		$timestamp	= '';

		// If the form is submitted

		if (isset ($_POST['format_timestamp']))
		{

			// Get variables

			$format		= $_POST['date_format'];
			$timestamp	= $_POST['timestamp'];

			// Format

			$date		= date ($format, $timestamp);

		}

		// Else if format date

		else if (isset ($_POST['format_date']))
		{

			// Get variables

			$format		= $_POST['date_format'];
			$date		= $_POST['date'];
			
			// Convert to timestamp

			$timestamp	= \Sonic\Resource\Parser::_convertToUnixtime ($date);
			
		}

		// Set template variables

		$this->view->assign ('date',			$date);
		$this->view->assign ('date_format',		$format);
		$this->view->assign ('timestamp',		$timestamp);
		
	}
	
	
	/**
	 * New class
	 */
	
	public function newclass ()
	{

		// Create tool classes

		$class		= new \Sonic\Model\Tools\NewClass;
		$db			= new \Sonic\Model\Tools\Db;

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

				$this->template	= 'tools/newclass_exists.tpl';
				$this->view->assign ('action', $action);

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

					$properties	= Comment::phpdocComment ($class->generateProperties ());

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

				$this->view->assign ('class_generated', $classData);
				$this->template	= 'tools/newclass_submitted.tpl';

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
						new \Sonic\Message ('success', 'Class saved to ' . $classPath);
					}
					else
					{
						new \Sonic\Message ('error', 'Class could not be saved to ' . $classPath);
					}

				}

			}

		}

		// Set classes

		$this->view->assign ('class',	$class);
		$this->view->assign ('db',		$db);
		
	}
	
	
}