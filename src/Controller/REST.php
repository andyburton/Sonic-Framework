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
	
	
}