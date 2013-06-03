<?php

// Define namespace

namespace Sonic\Controller;

// Start JSON Class

abstract class JSON extends \Sonic\Controller
{
	
	
	/**
	 * Instantiate class
	 * @return void
	 */
	
	public function __construct ()
	{
		
		// Call parent constructor
		
		parent::__construct ();
		
		// Set raw input data
		
		$this->request['raw']	= file_get_contents ('php://input');
		
		// Decode JSON input
		
		$this->request['json']	= json_decode ($this->request['raw']);
		
		// Set JSON view
		
		$this->view	= new \Sonic\View\JSON;
		
	}
	
	
	/**
	 * Return an argument from JSON request
	 * @param string $name Argument name
	 * @return mixed
	 */
	
	protected function getArg ($name)
	{
		return isset ($this->request['json']->$name)? $this->request['json']->$name : parent::getArg ($name);
	}
	
	
	/**
	 * Set success response
	 * @param array $response Response
	 * @return TRUE
	 */
	
	protected function success ($response = array ())
	{
		$this->view->response				= $response;
		$this->view->response['success']	= 1;
		$this->view->response['time']		= time ();
		return TRUE;
	}
	
	
	/**
	 * Set error response
	 * @param \Sonic\DM\Error|integer|string $message Error message object, error code or message string
	 * @param integer $code Error code, only used if message is a string
	 * @return FALSE
	 */
	
	protected function error ($message = 'invalid request', $code = 0) 
	{
		
		if (is_numeric ($message))
		{
			$error		= new Error ($message);
			$code		= $error->getCode ();
			$message	= $error->getMessage ();
		}
		else if ($message instanceof Error)
		{
			$code		= $message->getCode ();
			$message	= $message->getMessage ();
		}
		
		$this->view->response	= array (
			'success'			=> 0,
			'error_code'		=> $code,
			'error_description'	=> $message
		);
		
		return FALSE;
		
	}
	
	
	/**
	 * Render view
	 * @return void 
	 */
	
	public function View ()
	{
		
		// If the view is JSON
		
		if ($this->view instanceof \Sonic\View\JSON)
		{
			
			// Display view

			$response = $this->view->display ();
			
		}
		
		// Else call parent method
		
		else
		{
			parent::View ();
		}
		
	}

	
}