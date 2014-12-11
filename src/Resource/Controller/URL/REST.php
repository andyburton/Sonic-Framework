<?php

// Define namespace

namespace Sonic\Resource\Controller\URL;

// Start REST Class

class REST extends \Sonic\Resource\Controller\URL
{
	
	
	/**
	 * Object ID to process
	 * @var mixed
	 */
	
	public $id = FALSE;
	
	
	/**
	 * Class constructor
	 */
	
	public function __construct ()
	{
		
		// CORS
		
		header ('Access-Control-Allow-Orgin: *');
		header ('Access-Control-Allow-Methods: *');
		
	}
	
	
	/**
	 * Process URL and work out controller/action
	 * @return void
	 */
	
	public function Process ()
	{
		
		// Work out which HTTP method were using and set as controller action
		// Default to get
		
		$this->action	= isset ($_SERVER['REQUEST_METHOD'])? strtolower ($_SERVER['REQUEST_METHOD']) : 'get';
		
		// HTTP_X_HTTP_METHOD override
		
		if ($this->action == 'post' && array_key_exists ('HTTP_X_HTTP_METHOD', $_SERVER))
		{
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE' || 
				$_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
			{
				$this->action = strtolower ($_SERVER['HTTP_X_HTTP_METHOD']);
			}
			else
			{
				throw new \Sonic\Exception ('Unexpected HTTP_X_HTTP_METHOD header');
			}
		}
		
		// Check method is valid
		
		if (!in_array ($this->action, ['get','put','post','delete']))
		{
			throw new \Sonic\Exception ('Invalid Method', 405);
		}
		
		// Get redirect path
		
		$redirect	= isset ($_SERVER['REQUEST_URI'])? strtok ($_SERVER['REQUEST_URI'], '?') : '';
		
		// Get the position of the last / in the URL

		$pos		= strrpos ($redirect, '/');
		
		// If there is no / besides the first one then there is no ID
		
		if ($pos < 1)
		{
			$controller = substr ($redirect, $pos+1);
		}
		else
		{

			// Set the object ID and controller

			$this->id	= substr ($redirect, $pos+1);
			$controller	= substr ($redirect, 1, $pos-1);
			
		}
		
		// If there is a controller
		// Split controller by module and uppercase first character of each module
		// to keep in fitting with the rest of the framework
		
		if ($controller)
		{

			$arrController	= explode ('/', $controller);

			foreach ($arrController as &$controllerModule)
			{
				$controllerModule	= ucfirst ($controllerModule);
			}

			// Replace / in controller with \ for correct path and set controller

			$this->controller	= join ('\\', $arrController);
			
		}
		
	}
	
	
	/**
	 * Find and confirm the controller and action
	 * @param string $controller Initial controller path
	 * @param boolean $captureall Use captureall method on controller
	 * @return boolean
	 */
	
	public function findController ($controller = NULL, $captureall = TRUE)
	{

		/**
		 * 
		 * Try the following route conversions:
		 * 
		 * Controller/ID	-> Controller->action
		 * Controller/ID	-> Controller->captureall
		 * Controller/ID	-> Controller\ID->action
		 * Controller/ID	-> Controller\ID->captureall
		 * 
		 */
		
		// Append route controller to the controller class if there is one

		$controller	.= $this->controller? '\\' . $this->controller : NULL;

		// Try controller with action
		// e.g. GET /message/1 -> \Sonic\Controller\Message->get ()

		if (class_exists ($controller) && 
			method_exists ($controller, $this->action))
		{
			$this->controller	= $controller;
			return TRUE;
		}

		// Try capture all on the controller
		// e.g. GET /message/1 -> \Sonic\Controller\Message->captureall ()

		elseif ($captureall && 
			class_exists ($controller) && 
			method_exists ($controller, 'captureall'))
		{
			$this->controller	= $controller;
			$this->action		= 'captureall';
			return TRUE;
		}

		// Try using the ID as part of the controller
		// e.g. POST /message/send -> \Sonic\Controller\Message\Send->post ()

		elseif (class_exists ($controller . '\\' . ucfirst ($this->id)) && 
			method_exists ($controller . '\\' . ucfirst ($this->id), $this->action))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->id);
			return TRUE;
		}

		// Try capture all on the controller with ID as part of it
		// e.g. POST /message/send -> \Sonic\Controller\Message\Send->captureall ()

		elseif ($captureall && 
			class_exists ($controller . '\\' . ucfirst ($this->id)) && 
			method_exists ($controller . '\\' . ucfirst ($this->id), 'captureall'))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->id);
			$this->action		= 'captureall';
			return TRUE;
		}

		// No match
		
		return FALSE;
		
	}
	
	
	/**
	 * Instantiate and return controller object
	 * @return \Sonic\Controller\REST
	 */
	
	public function createController ()
	{
		
		// Instantiate controller
		
		$controllerObj		= new $this->controller;
		
		// Set controller variables from the url processor
		
		$controllerObj->controller	= $this->controller;
		$controllerObj->action		= $this->action;
		$controllerObj->id			= $this->id;
		
		// Return controller object
		
		return $controllerObj;
		
	}
	
	
}