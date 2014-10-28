<?php

// Define namespace

namespace Sonic\Controller;

// Start Developer Class

class Developer extends JSON
{
	
	
	/**
	 * Constructor
	 */
	
	public function __construct ()
	{
		if (!defined ('MODE_DEVELOPER') || MODE_DEVELOPER !== TRUE)
		{
			exit;
		}
	}
	
    
	/**
	 * Index action
	 */
	
    public function index () 
    {
		$this->view	= new \Sonic\View\Smarty;
    }
    
	
	/**
	 * Return list of controllers
	 */

	public function listcontrol ()
	{
		$this->success (array ('controllers' => $this->getControllers ()));
	}
	
	
	/**
	 * Return list of controller methods 
	 */
    
	public function listmethod ()
	{
		$this->success (array ('methods' => $this->getControllerMethods ($this->getURLArg ('control'))));
	}
	
	
	/**
	 * Return controller methods using reflection
	 * @param string $controller Controller to reflect
	 * @return array 
	 */
	
	private function getControllerMethods ($controller)
	{
		
		// Check controller exists
		
		if (!\Sonic\Sonic::_classExists (__NAMESPACE__ . '\\' . $controller))
		{
			return array ();
		}
		
		// Set actions
		
		$actions	= array ();
		
		/**
		 * Reflect controller
		 */
		
		$reflection	= new \ReflectionClass (__NAMESPACE__ . '\\' . $controller);
		$className	= $reflection->getName ();
		
		// Get class methods
		
		foreach ($reflection->getMethods () as $method)
		{
			
			// Dont use methods from parent classes
			
			if ($method->getDeclaringClass ()->getName () != $className)
			{
				continue;
			}
			
			// Skip unless the method is public
			
			if (!$method->isPublic ())
			{
				continue;
			}
			
			// Skip methods starting with __
			
			if (substr ($method->getName (), 0, 2) == '__')
			{
				continue;
			}
			
			// Get class name and comment
			
			$name		= $method->getName ();
			$comment	= new \Sonic\Model\Tools\Comment ($method->getDocComment ());
			
			// Skip if @ignore is set in the method comment
			
			if ($comment->hasTag ('ignore'))
			{
				continue;
			}
			
			// Add action
			
			$actions[$name] = array (
				'description'	=> $comment->getShortDescription (),
				'notes'			=> $comment->getLongDescription (),
				'url'			=> URL_ROOT . str_replace ('\\', '/', strtolower ($controller)) . '/' . $name,
				'auth'			=> FALSE
			);
			
			// Deal with authenticated method
			
			if ($comment->hasTag ('authenticated'))
			{
				$actions[$name]['auth']	= TRUE;
			}
			
			// Add method parameters
			
			$params	= $comment->getTags ('param');
			
			if ($params)
			{
				
				foreach ($params as $param)
				{
					
					if (!preg_match ('/^(\w+)\s+\$([\w\d]+)\s*(.*?)$/', $param, $arr))
					{
						continue;
					}
					
					$actions[$name]['param'][]	= array (
						'type'			=> $arr[1],
						'name'			=> $arr[2],
						'description'	=> $arr[3]
					);
					
				}
				
			}
			
		}
		
		// Return actions
		
		return $actions;
		
	}
	
	
	/**
	 * Return controllers
	 * @param string $dir Directory path to scan
	 * @param boolean $recursive Whether to call recursively
	 * @return array 
	 */
	
	private function getControllers ($dir = FALSE, $recursive = TRUE)
	{
		
		// Excluded Controllers

		$ignore		= array ('.', '..', '.svn', 'Developer.php');
		
		// Append / to dir if it doesnt end in one
		
		if ($dir && substr ($dir, -1) != '/')
		{
			$dir	.= DIRECTORY_SEPARATOR;
		}
		
		// Set path with seperator

		$path	= __DIR__ . DIRECTORY_SEPARATOR . $dir;
		
		// Extract Actual Controllers

		$controllers	= array ();
		
		foreach (scandir ($path) as $entry)
		{
			
			$name	= str_replace ('.php', '', $entry);
			
			// If the entry is . or .. move on
			
			if (in_array ($entry, $ignore))
			{
				continue;
			}
			
			// Else if the entry is a .php file
			
			else if (is_file ($path . $entry) && stripos ($entry, '.php'))
			{
				if (!array_key_exists ($name, $controllers))
				{
					$controllers[$name] = 0;
				}
			}
			
			// Else if we're calling recursively and the entry is a directory 
			
			else if ($recursive && is_dir ($path . $entry))
			{
				$controllers[$name] = $this->getControllers ($dir . $entry, TRUE);
			}
			
		}
		
		return $controllers;

	}
	
	
}