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
		
		// Run url processor to work out the controller and action

		$this->urlProcessor->Process ();
		
		// Set controller paths to look in
		
		$controllerPaths	= is_array ($this->controllerPath)? $this->controllerPath : [$this->controllerPath];
		
		// Look through paths to look for controller
		
		foreach ($controllerPaths as $controllerClass)
		{

			// Add module to the class path

			$controllerClass .= $this->controllerModule? '\\' . $this->controllerModule : NULL;

			// Append controller to the controller class if there is one

			$controllerClass	.= $this->urlProcessor->controller? '\\' . $this->urlProcessor->controller : NULL;
			
			/**
			 * 
			 * Try the following route conversions:
			 * 
			 * Controller/Action	-> Controller->action
			 * Controller/Action	-> Action->index
			 * Action				-> Index->action
			 * Action				-> Index->captureall
			 * Controller/Action	-> Controller->captureall
			 * Controller/Action	-> Action->captureall
			 * Controller/Action	-> Action\Index->index
			 * Controller/Action	-> Action\Index->captureall
			 * 
			 */
			
			// Try controller with action
			// e.g. /admin/login -> \Sonic\Controller\Admin->login ()

			if (class_exists ($controllerClass) && 
				method_exists ($controllerClass, $this->urlProcessor->action))
			{
				break;
			}

			// Try action as controller with index action
			// e.g. /admin -> \Sonic\Controller\Admin->index ()

			elseif (class_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action)) && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action), 'index'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->action		= 'index';
				break;
			}
			
			// If no controller
			
			else if (!$this->urlProcessor->controller)
			{
				
				// Try index controller
				
				if (class_exists ($controllerClass . '\\Index'))
				{
				
					// Try action on index controller
					// e.g. /admin -> \Sonic\Controller\Index->admin ()
					
					if (method_exists ($controllerClass . '\\Index', $this->urlProcessor->action))
					{
						$this->urlProcessor->controller	= 'Index';
						$controllerClass .= '\\Index';
						break;
					}

					// Try capture all on index controller
					// e.g. /admin -> \Sonic\Controller\Index->captureall ()

					else if (method_exists ($controllerClass . '\\Index', 'captureall'))
					{
						$this->urlProcessor->controller	= 'Index';
						$controllerClass .= '\\Index';
						$this->urlProcessor->action		= 'captureall';
						break;
					}
					
				}
				
			}
			
			// Try capture all on the controller
			// e.g. /admin/login -> \Sonic\Controller\Admin->captureall ()

			If ($this->captureall && 
				class_exists ($controllerClass) && 
				method_exists ($controllerClass, 'captureall'))
			{
				
				$this->urlProcessor->action		= 'captureall';
				break;
			}

			// Try action as controller with captureall action
			// e.g. /admin -> \Sonic\Controller\Admin->captureall ()

			elseif ($this->captureall && 
				class_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action)) && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action), 'captureall'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action);
				$this->urlProcessor->action		= 'captureall';
				break;
			}

			// Try Action\Index as the controller with index action
			// e.g. /admin -> \Sonic\Controller\Admin\Index->index ()

			elseif (class_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index') && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index', 'index'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->action		= 'index';
				break;
			}

			// Try Action\Index as the controller with captureall action
			// e.g. /admin -> \Sonic\Controller\Admin\Index->captureall ()

			elseif ($this->captureall && 
				class_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index') && 
				method_exists ($controllerClass . '\\' . ucfirst ($this->urlProcessor->action) . '\\Index', 'captureall'))
			{
				$controllerClass				.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->controller	.= '\\' . ucfirst ($this->urlProcessor->action) . '\\Index';
				$this->urlProcessor->action		= 'captureall';
				break;
			}
			
		}
		
		// Error if the controller and action do not exist
		
		if (!class_exists ($controllerClass) || !method_exists ($controllerClass, $this->urlProcessor->action))
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