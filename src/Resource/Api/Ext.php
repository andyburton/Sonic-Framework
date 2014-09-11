<?php

/**
 * A module extension class to provide required API module-specifc methods and properties.
 * All API modules should extend this class.
 */

// Define namespace

namespace Sonic\Resource\Api;

// Start Ext Class

class Ext
{
	
	
	/**
	 * API reference
	 * @var \Sonic\Resource\Api
	 */
	
	public $api		= FALSE;
	
	/**
	 * Module result
	 * @var array
	 */
	
	public $result	= array ();
	
	
	/**
	 * Return module result
	 * @return array
	 */
	
	public function getResult ()
	{
		
		// If the status is not set
		
		if (!isset ($this->result['status']))
		{
			
			// Set it
			
			$this->result['status']	= 'fail';
			
			// If no message is set
			
			if (!isset ($this->result['message']))
			{
				
				// Set it
				
				$this->result['message']	= 'no method return status set';
				
			}
			
		}
		
		// Return result
		
		return $this->result;
		
	}
	
	
	/**
	 * Set a result fail status
	 * @return boolean
	 */
	
	public function Error ($msg = NULL)
	{
		
		$this->result['status']		= 'fail';
		$this->result['message']	= $msg;
		
		return FALSE;
		
	}
	
	
	/**
	 * Set a result success status
	 * @params array Optional parameters to return
	 * @return boolean
	 */
	
	public function Success ($params = FALSE)
	{
		
		if ($params)
		{
			$this->result	= array_merge ($this->result, $params);
		}
		
		$this->result['status']		= 'ok';
		return TRUE;
	}
	
	
	/**
	 * Executed before the module method call
	 * @param \Sonic\Resource\Api\Module $module Module Object
	 * @param string $method Method to call
	 * @param array $args Api Arguments
	 * @return boolean
	 */
	
	public function callInit ($module, $method, $args)
	{
		
		// If the user is authenticated
		
		if ($this->api->isAuthenticated ())
		{
			
			// Check the user permission
			
			if (!$this->api->user->checkPermission ($module, $method))
			{
				
				// If no permission set result
				
				return $this->Error ('invalid permission');
				
			}
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
}

// End Ext Class