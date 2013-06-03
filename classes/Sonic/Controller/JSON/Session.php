<?php

/**
 * Same as \Sonic\Controller\Session but extends \Sonic\Controller\JSON to work with JSON request
 */

// Define namespace

namespace Sonic\Controller\JSON;

// Start Session Class

abstract class Session extends \Sonic\Controller\JSON
{
	
	
	/**
	 * Controller auth module
	 * @var string 
	 */
	
	public $authModule		= 'admin';
	
	
	/**
	 * User object
	 * @var \Sonic\Model\User
	 */
	
	protected $user			= FALSE;
	
	
	/**
	 * Call controller action method
	 * @return void 
	 */
	
	public function callAction ()
	{
		
		// Only call the method if the user is authenticated
		
		if ($this->checkAuthenticated ())
		{
			parent::callAction ();
		}
		
	}
	
	
	/**
	 * Check the user is authenticated
	 * @return boolean
	 */
	
	protected function checkAuthenticated ()
	{
		
		// Create user
		
		$this->user	= new \Sonic\Model\User;
		
		// Check authenticated
		
		$auth = $this->user->initSession ();
		
		if ($auth !== TRUE)
		{
			return $this->error ($auth);
		}
		
		// Return
		
		return TRUE;
		
	}
	
	
}