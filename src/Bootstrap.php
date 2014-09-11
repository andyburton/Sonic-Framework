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
	 * Check for controller captureall function if action is not found
	 * @var boolean
	 */
	
	public $captureall				= TRUE;
	
	
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
		
		// If there is no controller check for default index class and action
		
		if (!$this->urlProcessor->controller && 
			Sonic::_classExists ($controllerClass . '\\Index') && 
			method_exists ($controllerClass . '\\Index', $this->urlProcessor->action))
		{
			$this->urlProcessor->controller	= 'Index';
		}
		
		// Append controller to the controller class if there is one

		$controllerClass	.= $this->urlProcessor->controller? '\\' . $this->urlProcessor->controller : NULL;
		
		// If the controller doesnt exist or the action doesnt exist on the controller
		
		if (!Sonic::_classExists ($controllerClass) || !method_exists ($controllerClass, $this->urlProcessor->action))
		{
			
			// Try capture all on the controller
			// e.g. /admin -> \Sonic\Controller\Index->captureall ()
			
			if ($this->captureall && 
				Sonic::_classExists ($controllerClass) && 
				method_exists ($controllerClass, 'captureall'))
			{
				$this->urlProcessor->action		= 'captureall';
			}
			
			// Try action as controller with index action
			// e.g. /admin -> \Sonic\Controller\Admin->index ()
			
			elseif (Sonic::_classExists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action)) && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action), 'index'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->action		= 'index';
			}
			
			// Try action as controller with captureall action
			// e.g. /admin -> \Sonic\Controller\Admin->captureall ()
			
			elseif ($this->captureall && 
				Sonic::_classExists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action)) && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action), 'captureall'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->action		= 'captureall';
			}
			
			// Try Action\Index as the controller with index action
			// e.g. /admin -> \Sonic\Controller\Admin\Index->index ()

			elseif (Sonic::_classExists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index') && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index', 'index'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->action		= 'index';
			}
			
			// Try Action\Index as the controller with captureall action
			// e.g. /admin -> \Sonic\Controller\Admin\Index->captureall ()

			elseif ($this->captureall && 
				Sonic::_classExists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index') && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index', 'captureall'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->action		= 'captureall';
			}
			
			// Remove starting \ from controller
			
			if (substr ($this->urlProcessor->controller, 0, 1) == '\\')
			{
				$this->urlProcessor->controller	= substr ($this->urlProcessor->controller, 1);
			}
			
		}
		
		// Error if the controller and action do not exist
		
		if (!Sonic::_classExists ($controllerClass) || !method_exists ($controllerClass, $this->urlProcessor->action))
		{
			throw new Exception ('Invalid controller action: ' . $this->urlProcessor->controller . '\\' . $this->urlProcessor->action, 404);
		}
		
		// Instantiate controller
		
		$controllerObj		= new $controllerClass;
		
		// Set controller variables
		
		if ($this->controllerModule)
		{
			$controllerObj->module		= $this->controllerModule;
		}
		
		$controllerObj->controller	= $this->urlProcessor->controller;
		$controllerObj->action		= $this->urlProcessor->action;
		
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