<?php

// Define namespace

namespace Sonic\Resource\Api;

// Start Module Class

class Module extends \Sonic\Model
{
	
	
	/**
	 * Module methods
	 * @var array
	 */
	
	protected $methods	= array ();
	
	
	/**
	 * Return an array of module method objects
	 * @param string $order Order Column, default ABS(name)
	 * @return array
	 */
	
	public function getMethods ($order = FALSE)
	{
		
		// If the methods have not been loaded
		
		if (!$this->methods || $order !== FALSE)
		{
			
			// Set the method class name
			
			$class		=  $this->getNamespace () . '\Method';
			
			// Load the methods
			
			$params['where'][]	= array ('module_id', $this->get ('id'));
			$params['orderby']	= $order?: 'ABS(name)';
			
			$this->methods		= $class::_getObjects ($params);
			
		}
		
		// Return the methods
		
		return $this->methods;
		
	}
	
	
	/**
	 * Return a module method if it exists
	 * @param string $method Method name
	 * @return boolean
	 */
	
	public function getMethod ($method)
	{
		
		// Get methods
		
		$this->getMethods ();
		
		// Loop through the methods
		
		foreach ($this->methods as $obj)
		{
			
			// If the method names match return 
			
			if (strtolower ($obj->get ('name')) == strtolower ($method))
			{
				return $obj;
			}
			
		}
		
		// Return FALSE
		
		return FALSE;
		
	}
	
	
	
	/**
	 * Check a module method if it exists
	 * @param string $method Method name
	 * @return boolean
	 */
	
	public function checkMethod ($method)
	{
		
		// Method name
		
		$method	= strtolower ($method);
		
		// Get module class name
		
		$class	= $this->getModuleClass ();
		
		// Get methods
		
		$this->getMethods ();
		
		// Loop through the methods
		
		foreach ($this->methods as $obj)
		{
			
			// If the method names match return TRUE and the method exists in the class
			
			if (strtolower ($obj->get ('name')) == $method && method_exists ($class, $method))
			{
				return TRUE;
			}
			
		}
		
		// Return FALSE
		
		return FALSE;
		
	}
	
	
	
	/**
	 * Call a module method
	 * @param \Sonic\Resource\Api $api Api Object
	 * @param string $module Module name
	 * @param string $method Method name to call
	 * @param array $args Api Arguments
	 * @return array
	 */
	
	public function callMethod (&$api, $module, $method, $args = array ())
	{
		
		// Set module class name
		
		$class		= $this->getModuleClass ($module);
		
		// Create module object
		
		$obj		= new $class;
		
		// Set the API reference
		
		$obj->api	= $api;
		
		// Call the method init function for any custom code before the action
		
		if ($obj->callInit ($this, $method, $args))
		{
			
			// Call module method and pass the arguments list as 
			
			$return	= call_user_func_array (array ($obj, $method), array ($args));
			
		}
		
		// Return result
		
		return $obj->getResult ();
		
	}
	
	
	/**
	 * Count the number of methods
	 * @return integer
	 */
	
	public function countMethods ()
	{
		
		// Get methods
		
		$this->getMethods ();
		
		// Return count
		
		return count ($this->methods);
		
	}
	
	
	/**
	 * Return the class name of the module
	 * @param string $name Module name
	 * @return string
	 */
	
	public function getModuleClass ($name = FALSE)
	{
		
		// Set name if none is passed
		
		if ($name === FALSE)
		{
			$name	= $this->get ('name');
		}
		
		// Convert . in module name to \
		// and ucfirst each section
		
		$arr	= explode ('.', $name);
		
		foreach ($arr as &$val)
		{
			$val	= ucfirst ($val);
		}
		
		$name	= implode ('\\', $arr);
		
		// Return module class
		
		return get_called_class () . '\\' . $name;
		
	}
	
	
}

// End Module Class