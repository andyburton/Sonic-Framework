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
	
	public static $resources			= array ();
	
	/**
	 * Resource settings
	 * @var array
	 */
	
	private static $resourceSettings	= array ();
	
	
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
	 * @return void
	 */
	
	public static function _autoload ($class)
	{
		
		// Strip first slash
		
		$class		= ltrim ($class, '\\');
		$namespace	= NULL;
		$filename	= NULL;
		
		// If we're loading a sonic class
		
		if (stripos ($class, 'Sonic\\') === 0)
		{
			
			// Process namespace
			
			if ($nsPos = strripos ($class, '\\'))
			{
				$namespace	= substr ($class, 0, $nsPos);
				$class		= substr ($class, $nsPos + 1);
				$filename	= str_replace ('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			
			// Replace _ with directory seperator in class
			
			$filename	.= str_replace ('_', DIRECTORY_SEPARATOR, $class) . '.php';
			
			// Check for framework class first
			
			if (defined ('ABS_SONIC') && file_exists (ABS_SONIC . $filename))
			{
				require_once (ABS_SONIC . $filename);
			}
			
			// Else check class library
			
			else if (defined ('ABS_CLASSES') && file_exists (ABS_CLASSES . $filename))
			{
				require_once (ABS_CLASSES . $filename);
			}
			
		}
		
	}
	
	
	/**
	 * Check if a sonic class exists
	 * @param string $class Class name
	 * @return boolean 
	 */
	
	public static function _classExists ($class)
	{
		return class_exists ($class);
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
	 * @param boolean $select Set as default resource for the group (overwriting any existing)
	 * @param boolean $override Override any existing resources with the name group and name
	 * @param boolean $default Whether to check and set the group default resource
	 *   Default TRUE will set the first group resource as the default
	 *   FALSE will not check, and as such will not set the resource as the default unless $select is TRUE
	 * @return void
	 */
	
	public static function newResource ($name, $resource, $select = FALSE, $overrride = FALSE, $default = TRUE)
	{
		
		// Set group and name
		
		list ($group, $name) = self::getResourceGroup ($name);
		
		// If the resource already exists and we're not overrriding it throw an exception
		
		if (isset (self::$resources[$group][$name]) && !$overrride)
		{
			throw new Exception ('The ' . ($group? $group . '\\': '') . $name . ' resource already exists.');
		}
		
		// If we're setting the resource as default or no resource default exists, set as default
		
		if ($select || ($default && !self::getGroupSetting ($group, 'default')))
		{
			self::setGroupSetting ($group, 'default', $name);
		}
		
		// Set the resource
		
		self::$resources[$group][$name]	= $resource;
		
	}
	
	
	/**
	 * Set a group of framework resources
	 * @param string $group Resource group name
	 * @param array $resources Resource objects to add to the group
	 *   Each item can be an object which will have an auto generated name or an array with:
	 *   array (0 => resource object, 1 => name, 2 => select, 3 => override)
	 * @param boolean $setDefault Whether to check and set the group default resource
	 *   Default TRUE will set the first group resource as the default
	 *   FALSE will not check, and as such will not set the resource as the default unless $select is TRUE
	 *   @see newResource
	 * @param boolean $defaultOverride Default override flag if not specified for the resource
	 * @return void
	 */
	
	public static function newResources ($group, $resources, $setDefault = TRUE, $defaultOverride = FALSE)
	{
		
		// Add each resource
		
		foreach ($resources as $resource)
		{
			
			// Default settings
			
			$select		= FALSE;
			$name		= FALSE;
			$override	= $defaultOverride;
			
			// The resource is an object so add it as-is
			
			if (is_object ($resource))
			{
				$obj	= $resource;
			}
			
			// Else the resource is not an object and is not an array with its 1st item being an object
			
			elseif (!is_array ($resource) || !$resource || !is_object ($resource[0]))
			{
				throw new Exception ('Invalid resource to add to group ' . $group);
			}
			
			// Else the resource is an array so lets work out any specific settings
			
			else
			{
				foreach ($resource as $arg => $val)
				{
					
					switch ($arg)
					{
						
						// Resource object
						
						case 0:
							$obj	= $val; break;
						
						// Resource name
						
						case 1:
							$name	= $val; break;
						
						// Default resource
						
						case 2:
							$select	= $val; break;
						
						// Override
						
						case 3:
							$override	= $val; break;
							
					}
					
				}
				
			}
			
			// Auto generate name if none is specified
			
			if (!$name)
			{
				$name	= $group . '-' . (self::countResourceGroup ($group) +1);
			}
			
			// Add the resource to the group
			
			self::newResource (array ($group, $name), $obj, $select, $override, $setDefault);
			
		}
		
	}
	
	
	/**
	 * Delete resource or resource group
	 * @param string $group Resource group
	 * @param string $name Resource name, if not specified the entire resource group will be removed
	 * @return void
	 */
	
	public static function removeResource ($group, $name = FALSE)
	{
		
		// Remove resource from group
		
		if ($name)
		{
			
			unset (self::$resources[$group][$name]);
			
			// Remove default if set to resource
			
			if (self::getGroupSetting ($group, 'default') == $name)
			{
				self::removeDefault ($group);
			}
			
		}
		
		// Else remove group
		
		else
		{
			unset (self::$resources[$group]);
		}
		
		// Remove group settings if nothing left in the group
		
		if (self::countResourceGroup ($group) < 1)
		{
			unset (self::$resourceSettings[$group]);
		}
		
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
		
		// For each group get default
		
		foreach (array_keys (self::$resourceSettings) as $group)
		{
			
			// Reference selected resource
			
			$resources[$group]	=& self::getResource (array ($group, self::getGroupSetting ($group, 'default')));
			
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
		
		// If the resource group is not set
		
		if (!isset (self::$resources[$group]) || !self::getGroupSetting ($group, 'default'))
		{
			
			// Return FALSE
			
			$bln = FALSE;
			return $bln;
			
		}
		
		// Return resource reference
		
		return self::getResource (array ($group, self::getGroupSetting ($group, 'default')));
		
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
		
		self::setGroupSetting ($group, 'default', $name);
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Remove the default selected resource for a group
	 * @param string $group Resource group
	 * @return void
	 */
	
	public static function removeDefault ($group)
	{
		self::setGroupSetting ($group, 'default', FALSE);
	}
	
	
	/**
	 * Return a random group resource name
	 * @param string $group Resource group
	 * @return string|boolean
	 */
	
	public static function selectRandomResource ($group)
	{
		
		// If the resource group is not set
		
		if (!isset (self::$resources[$group]))
		{
			
			// Return FALSE
			
			$bln = FALSE;
			return $bln;
			
		}
		
		// Return random resource name
		
		return array_rand (self::$resources[$group]);
		
	}
	
	
	/**
	 * Return number of resource in a group
	 * @param string $group Resource group
	 * @return integer
	 */
	
	public static function countResourceGroup ($group)
	{
		return isset (self::$resources[$group])? count (self::$resources[$group]) : 0;
	}
	
	
	/**
	 * Set group setting
	 * @param string $group Resource group
	 * @param string $setting Setting name
	 * @param mixed $val Setting value
	 * @return void
	 */
	
	public static function setGroupSetting ($group, $setting, $val)
	{
		self::$resourceSettings[$group][$setting]	= $val;
	}
	
	
	/**
	 * Return group setting or FALSE if not set
	 * @param string $group Resource group
	 * @param string $setting Setting name
	 * @return mixed|FALSE
	 */
	
	public static function getGroupSetting ($group, $setting)
	{
		return isset (self::$resourceSettings[$group][$setting])? self::$resourceSettings[$group][$setting] : FALSE;
	}
	
	
}

// End Sonic Class