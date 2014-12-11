<?php

// Define namespace

namespace Sonic\Resource\Controller;

// Start URL Class

abstract class URL
{
	
	
	/**
	 * Controller class path relative to module class
	 * @var string
	 */
	
	public $controller	= '';
	
	
	/**
	 * Action to call on controller
	 * @var string
	 */
	
	public $action		= FALSE;
	
	
	/**
	 * Process URL and work out controller/action
	 * @return void
	 */
	
	abstract public function Process ();
	
	
	/**
	 * Find and confirm the controller and action
	 * @param string $controller Initial controller path
	 * @param boolean $captureall Use captureall method on controller
	 * @return boolean
	 */
	
	abstract public function findController ($controller = NULL, $captureall = TRUE);
	
	
	/**
	 * Instantiate and return controller object
	 * @param string $stripPath Class path to strip
	 * @return \Sonic\Controller
	 */
	
	public function createController ($stripPath = NULL)
	{
		
		// Instantiate controller
		
		$controllerObj		= new $this->controller;
		
		// Set controller variables from the url processor
		
		$controllerObj->controller	= $this->controller;
		$controllerObj->action		= $this->action;
		
		// Strip initial path from controller
		
		if ($stripPath && substr ($controllerObj->controller, 0, strlen ($stripPath)) == $stripPath)
		{
			$controllerObj->controller = substr ($controllerObj->controller, strlen ($stripPath));
		}
		
		// Return controller object
		
		return $controllerObj;
		
	}
	
	
}