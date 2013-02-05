<?php

// Define namespace

namespace Sonic;

// Start Sonic Class

class Sonic
{
	
	
	/**
	 * Framework resources
	 * @var array 
	 */
	
	public static $resources	= array ();
	
	/**
	* Selected resources
	* @var array
	*/
	
	public static $selectedResources	= array ();
	
	
	/**
	 * Instantiate class
	 */
	
	public function __construct ()
	{
	}
	
	
	/**
	 * Set framework class autoloader
	 * @return void
	 */
	
	public static function autoload ()
	{
		spl_autoload_register (array ('self', '_autoload'));
	}
	
	
	/**
	 * Class autoloader
	 * @param string $class Class name
	 */
	
	public static function _autoload ($class)
	{
		
		// If we're loading a sonic class
		
		if (stripos ($class, 'Sonic\\') === 0)
		{
			
			// Replace namespace \ with dir seperate

			if ('\\' !== DIRECTORY_SEPARATOR)
			{
				$class = str_replace ('\\', DIRECTORY_SEPARATOR, $class);
			}
			
			if (defined ('ABS_SONIC') && file_exists (ABS_SONIC . $class . '.php'))
			{
				@include_once (ABS_SONIC . $class . '.php');
			}
			else
			{
				@include_once (ABS_CLASSES . $class . '.php');
			}
			
		}
		
	}
	
	
	/**
	 * Check if a sonic class exists
	 * @param string $class Class name
	 * @return type 
	 */
	
	public static function _classExists ($class)
	{
		
		// Replace namespace \ with dir seperate

		if ('\\' !== DIRECTORY_SEPARATOR)
		{
			$class = str_replace ('\\', DIRECTORY_SEPARATOR, $class);
		}

		if (defined ('ABS_SONIC') && file_exists (ABS_SONIC . $class . '.php'))
		{
			return TRUE;
		}
		else
		{
			return file_exists (ABS_CLASSES . $class . '.php');
		}
		
	}
	
	
	/**
	 * Return a resource group and name given a name variable
	 * @param string|array $name Resource name
	 *   You can group resources by passing array ('group', 'name')
	 * @return array (group, name)
	 */
	
	public static function getResourceGroup ($name)
	{
		
		// Set group
		
		$group	= FALSE;
		
		// If the name is an array
		
		if (is_array ($name))
		{
			
			// Count name elements
			
			$count	= count ($name);
			
			// If the name is longer than 2 elements or less than 1
			
			if ($count < 1 || $count > 2)
			{
				throw new Exception ('You must only pass the resource group and name for a resource.');
			}
			
			// Else if the name only has 1 element
			
			else if ($count == 1)
			{
				
				// Set name to the single element
				
				$name	= $name[0];
				
			}
			
			// Else we have 2 elements for the group and name
			
			else
			{
				
				// Set group and name
				
				$group	= $name[0];
				$name	= $name[1];
				
			}
			
		}
		
		// If there is no group
		
		if (!$group)
		{
			
			// Set group to name
			
			$group	= $name;
			
		}
		
		// Return group and name
		
		return array ($group, $name);
		
	}	
	
	
	/**
	 * Set a new framework resource
	 * @param string|array $name Resource name
	 *   You can group resources by passing array ('group', 'name')
	 * @param object $resource Resource object
	 * @param boolean $select Set as default resource for the group
	 * @param boolean $override Override any existing resources with the name group and name
	 * @return void
	 */
	
	public static function newResource ($name, $resource, $select = FALSE, $overrride = FALSE)
	{
		
		// Set group and name
		
		list ($group, $name) = self::getResourceGroup ($name);
		
		// If the resource already exists and we're not overrriding it throw an exception
		
		if (isset (self::$resources[$group][$name]) && !$overrride)
		{
			throw new Exception ('The ' . ($group? $group . '\\': '') . $name . ' resource already exists.');
		}
		
		// If no resource default exists or we're selecting the resource as default, set as default
		
		if (!isset (self::$selectedResources[$group]) || $select)
		{
			self::$selectedResources[$group]	= $name;
		}
		
		// Set the resource
		
		self::$resources[$group][$name]	= $resource;
		
	}
	
	
	/**
	 * Return a framework resource reference
	 * @param string|array $name Resource name
	 * @return object|boolean
	 */
	
	public static function &getResource ($name)
	{
		
		// Set group and name
		
		list ($group, $name) = self::getResourceGroup ($name);
		
		// If the resource is not set
		
		if (!isset (self::$resources[$group][$name]))
		{
			
			// Return FALSE
			
			$bln = FALSE;
			return $bln;
			
		}
		
		// Return resource reference
		
		return self::$resources[$group][$name];
		
	}
	
	
	/**
	 * Return an array of reference resource objects which are currently selected
	 * @return array
	 */
	
	public static function getSelectedResources ()
	{
		
		// Set resource array
		
		$resources	= array ();
		
		// For each selected resource
		
		foreach (self::$selectedResources as $group => $name)
		{
			
			// Reference selected resource
			
			$resources[$group]	=& self::getResource (array ($group, $name));
			
		}
		
		// Return resources
		
		return $resources;
		
	}
	
	
	/**
	 * Return the currently selected resource for a resource group
	 * @param string $group Resource group
	 * @return object|boolean
	 */
	
	public static function &getSelectedResource ($group)
	{
		
		// If the resource is not set
		
		if (!isset (self::$resources[$group]))
		{
			
			// Return FALSE
			
			$bln = FALSE;
			return $bln;
			
		}
		
		// Return resource reference
		
		return self::getResource (array ($group, self::$selectedResources[$group]));
		
	}
	
	
	/**
	 * Set the selected resource for a resource group
	 * @param string $group Resource group
	 * @param string $name New selected resource
	 * @return boolean
	 */
	
	public static function setSelectedResource ($group, $name)
	{
		
		// If the resource is not set
		
		if (!isset (self::$resources[$group][$name]))
		{
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Set selected resource
		
		self::$selectedResources[$group]	= $name;
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
}

// End Sonic Class