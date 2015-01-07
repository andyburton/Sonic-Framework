<?php

// Define namespace

namespace Sonic\Controller;

// Start REST Class

abstract class REST extends \Sonic\Controller\JSON
{
	
	
	/**
	 * Object ID
	 * @var mixed
	 */
	
	public $id			= FALSE;
	
	
	/**
	 * Return an argument from request
	 * @param string $name Argument name
	 * @return mixed
	 */
	
	protected function getArg ($name)
	{
		
		// Work out which request arguments to use based on the request method
		
		switch ($this->action)
		{
			
			case 'get';
			case 'put':
				return $this->getURLArg ($name);
				break;
			
			case 'post';
			case 'delete':
				return $this->getPostArg ($name);
				break;
			
			default:
				return parent::getArg ($name);
				break;
			
		}
		
	}
	
	
	/**
	 * Set error response
	 * By default $httpCode will be the same as $code
	 * @param \Sonic\Controller\Error|integer|string $message Error message object, error code or message string
	 * @param integer $code Error code, only used if message is a string
	 * @param integer $httpCode HTTP status code
	 * @return FALSE
	 */
	
	protected function error ($message = 'invalid request', $code = 0, $httpCode = FALSE) 
	{
		
		if ($httpCode === FALSE)
		{
			$httpCode = $code;
		}
		
		return parent::error ($message, $code, $httpCode);
		
	}
	
	
	/**
	 * Get resource
	 * @return boolean
	 */
	
	public function get ()
	{
		return $this->error ('Method Not Allowed', 405);
	}
	
	
	/**
	 * Create resource
	 * @return boolean
	 */
	
	public function post ()
	{
		return $this->error ('Method Not Allowed', 405);
	}
	
	
	/**
	 * Update resource
	 * @return boolean
	 */
	
	public function put ()
	{
		return $this->error ('Method Not Allowed', 405);
	}
	
	
	/**
	 * Delete resource
	 * @return boolean
	 */
	
	public function delete ()
	{
		return $this->error ('Method Not Allowed', 405);
	}
	
	
}