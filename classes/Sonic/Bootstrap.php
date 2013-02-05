<?php

// Define namespace

namespace Sonic;

// Start Bootstrap Class

class Bootstrap
{
	
	
	/**
	 * Default controller class path
	 * @var string
	 */
	
	public $controllerPath			= 'Sonic\\Controller';
	
	/**
	 * Controller module
	 * @var string 
	 */
	
	public $controllerModule		= '';
	
	/**
	 * URL processor to determine controller/action
	 * Defaults to Resource\Controller\URL\REST
	 * @var string
	 */
	
	public $urlProcessor			= FALSE;
	
	/**
	 * Whether to use the controller exception handler
	 * @var boolean
	 */
	
	public $useExceptionHandler		= FALSE;
	
	/**
	 * View object to render the template
	 * @var object
	 */
	
	public $view					= FALSE;
	
	
	/**
	 * Instantiate class
	 * @param boolean $run Run the bootstrap from the constructor, default to FALSE
	 * @return void
	 */
	
	public function __construct ($run = FALSE)
	{
		
		// Run the bootstrap if requested
		
		if ($run)
		{
			$this->Run ();
		}
		
	}
	
	
	/**
	 * Determine the controller and run the action
	 * @param boolean $displayView Whether to display the view
	 * @return \Sonic\Controller
	 */
	
	public function Run ($displayView = TRUE)
	{
		
		// Set controller class path
		
		$controllerClass	= $this->controllerPath;
		
		// Add module to the class path
		
		$controllerClass .= $this->controllerModule? '\\' . $this->controllerModule : NULL;
		
		// Default to REST url processor if none is set
		
		if (!$this->urlProcessor)
		{
			$this->urlProcessor	= new Resource\Controller\URL\REST;
		}
		
		// Run url processor to work out the controller and action
		
		$this->urlProcessor->Process ();
		
		// If there is no controller check for default index class
		
		if (!$this->urlProcessor->controller && Sonic::_classExists ($controllerClass	. '\\Index'))
		{
			$this->urlProcessor->controller	= 'Index';
		}
		
		// Append controller to the controller class if there is one

		$controllerClass	.= $this->urlProcessor->controller? '\\' . $this->urlProcessor->controller : NULL;
		
		// Instantiate controller
		
		$controllerObj		= new $controllerClass;
		
		// Set controller variables
		
		$controllerObj->module		= $this->controllerModule;
		$controllerObj->controller	= $this->urlProcessor->controller;
		$controllerObj->action		= $this->urlProcessor->action;
		
		// Set controller view is none is set and one is set in the bootstrap
		
		if ($this->view && !$controllerObj->view)
		{
			$controllerObj->view	= $this->view;
		}
		
		// Set controller exception handler
		
		if ($this->useExceptionHandler)
		{
			set_exception_handler (array ($controllerObj, 'Exception'));
		}
		
		// If the controller action is public and is valid to call
		
		if (is_callable (array ($controllerObj, $controllerObj->action)) && 
			$controllerObj->isActionValid ())
		{
			
			// Call controller action
			
			$controllerObj->callAction ();
			
		}
		
		// Otherwise throw exception
		
		else
		{
			throw new Exception ('Invalid controller action: ' . $controllerObj->action);
		}
		
		// Display view
		
		if ($displayView && $controllerObj->render)
		{
			$controllerObj->View ();
		}
		
		// Return controller
		
		return $controllerObj;
		
	}
	
	
}