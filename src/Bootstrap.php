<?php

// Define namespace

namespace Sonic;

// Start Bootstrap Class

class Bootstrap
{
	
	
	/**
	 * Default controller class path
	 * @var array
	 */
	
	public $controllerPath			= ['\\Sonic\\Controller'];
	
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
	 * Check for controller captureall function if action is not found
	 * @var boolean
	 */
	
	public $captureall				= TRUE;
	
	
	/**
	 * Instantiate class
	 * @param boolean $run Run the bootstrap from the constructor, default to FALSE
	 * @param boolean $displayView Whether to display the view when run is called
	 * 
	 * @return void
	 */
	
	public function __construct ($run = FALSE, $displayView = TRUE)
	{
		
		// Run the bootstrap if requested
		
		if ($run)
		{
			$this->Run ($displayView);
		}
		
	}
	
	
	/**
	 * Determine the controller and run the action
	 * @param boolean $displayView Whether to display the view
	 * @return \Sonic\Controller
	 */
	
	public function Run ($displayView = TRUE)
	{
	
		// Default to Route url processor if none is set

		if (!$this->urlProcessor)
		{
			$this->urlProcessor	= new Resource\Controller\URL\Route;
		}
		
		// Run url processor to work out the controller and action from URL

		$this->urlProcessor->Process ();
		
		// Set controller paths to look in
		
		$controllerPaths	= is_array ($this->controllerPath)? $this->controllerPath : [$this->controllerPath];
		
		// Look through paths to look for controller
		
		foreach ($controllerPaths as $controllerClass)
		{

			// Add module to the class path

			$controllerClass .= $this->controllerModule? '\\' . $this->controllerModule : NULL;
			
			// Check the controller and action exist
			
			if ($this->urlProcessor->findController ($controllerClass, $this->captureall))
			{
				break;
			}
			
		}
		
		// Error if the controller and action do not exist
		
		if (!class_exists ($this->urlProcessor->controller) || !method_exists ($this->urlProcessor->controller, $this->urlProcessor->action))
		{
			throw new Exception ('Invalid controller action: ' . $this->urlProcessor->controller . '\\' . $this->urlProcessor->action, 404);
		}
		
		// Create controller
		
		$controllerObj		= $this->urlProcessor->createController ($controllerClass);
		
		// Set controller module
		
		if ($this->controllerModule)
		{
			$controllerObj->module		= $this->controllerModule;
		}
		
		// Set controller view if none is set and one is set in the bootstrap
		
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
		
		// Otherwise error 404
		
		else
		{
			throw new Exception ('Invalid controller action: ' . $controllerObj->controller . '\\' . $controllerObj->action, 404);
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