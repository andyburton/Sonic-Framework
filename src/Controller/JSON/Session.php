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
		
		if ($this->checkAuthenticated ($this->getArg ('session_id')))
		{
			parent::callAction ();
		}
		
	}
	
	
	/**
	 * Check the user is authenticated
	 * @param string $session_id Session ID
	 * @return boolean
	 */
	
	protected function checkAuthenticated ($session_id = FALSE)
	{
		
		// Create user
		
		if (!$this->user)
		{
			$this->user	= new \Sonic\Model\User ($session_id);
		}
		
		// Check authenticated
		
		$auth = $this->user->initSession ();
		
		if ($auth !== TRUE)
		{
			return $this->authFail ($auth);
		}
		
		// Return
		
		return TRUE;
		
	}


	/**
	* Set response for an authentication failure
	* @param string $message Error message
	* @return false
	*/
     
	protected function authFail ($message = null)
	{
		$this->error ($message);
		$this->view->response['auth_fail']	= TRUE;
		return FALSE;
	}
	
	
}