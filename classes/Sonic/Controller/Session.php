<?php

// Define namespace

namespace Sonic\Controller;

// Start Session Class

abstract class Session extends \Sonic\Controller
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
			$this->view->assign ('user', $this->user);
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
			
			switch ($auth)
			{
				
				case 'invalid_session':
//					new \Sonic\Message ('error', 'Please login to continue');
					break;
				
				case 'user_read_error':
					new \Sonic\Message ('error', 'There seems to be a problem, please login to continue'); break;
				
				case 'inactive':
					new \Sonic\Message ('error', 'Account not activated'); break;
				
				case 'timeout':
					new \Sonic\Message ('error', 'Your session has expired, please login to continue'); break;
				
			}
			
			$this->template	= $this->authModule? strtolower ($this->authModule) . '/' : NULL;
			$this->template	.= 'login.tpl';
			
			return FALSE;
			
		}
		
		// Return
		
		return TRUE;
		
	}
	
	
}