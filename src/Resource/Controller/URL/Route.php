<?php

// Define namespace

namespace Sonic\Resource\Controller\URL;

// Start Route Class

class Route extends \Sonic\Resource\Controller\URL
{
	
	
	/**
	 * Process URL and work out controller/action
	 * @return void
	 */
	
	public function Process ()
	{
		
		// Set redirect path
		
		$redirect	= isset ($_SERVER['REQUEST_URI'])? strtok ($_SERVER['REQUEST_URI'], '?') : '';
		
		/**
		 * Action is the last section after the final /
		 * Controller is the path before it
		 */
		
		// Get the position of the last / in the URL
		
		$pos			= strrpos ($redirect, '/');
		
		// Set the action
		
		$this->action	= substr ($redirect, $pos+1);
		
		if (!$this->action)
		{
			$this->action	= 'index';
		}
		
		// Set controller, if there is no / besides the first one then there is no controller
		
		$controller		= ($pos < 1)? '' : substr ($redirect, 1, $pos-1);
		
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
		
		// Append route controller to the controller class if there is one

		$controller	.= $this->controller? '\\' . $this->controller : NULL;

		// Try controller with action
		// e.g. /admin/login -> \Sonic\Controller\Admin->login ()

		if (class_exists ($controller) && 
			method_exists ($controller, $this->action) &&
			static::isInstantiable ($controller))
		{
			$this->controller	= $controller;
			return TRUE;
		}

		// Try action as controller with index action
		// e.g. /admin -> \Sonic\Controller\Admin->index ()

		elseif (class_exists ($controller . '\\' . ucfirst ($this->action)) && 
			method_exists ($controller . '\\' . ucfirst ($this->action), 'index') &&
			static::isInstantiable ($controller . '\\' . ucfirst ($this->action)))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->action);
			$this->action		= 'index';
			return TRUE;
		}

		// If no controller

		else if (!$this->controller)
		{

			// Try index controller

			if (class_exists ($controller . '\\Index') &&
			static::isInstantiable ($controller . '\\Index'))
			{

				// Try action on index controller
				// e.g. /admin -> \Sonic\Controller\Index->admin ()

				if (method_exists ($controller . '\\Index', $this->action))
				{
					$this->controller	= $controller . '\\Index';
					return TRUE;
				}

				// Try capture all on index controller
				// e.g. /admin -> \Sonic\Controller\Index->captureall ()

				else if (method_exists ($controller . '\\Index', 'captureall'))
				{
					$this->controller	= $controller . '\\Index';
					$this->action		= 'captureall';
					return TRUE;
				}

			}

		}

		// Try capture all on the controller
		// e.g. /admin/login -> \Sonic\Controller\Admin->captureall ()

		if ($captureall && 
			class_exists ($controller) && 
			method_exists ($controller, 'captureall') &&
			static::isInstantiable ($controller))
		{
			$this->controller	= $controller;
			$this->action		= 'captureall';
			return TRUE;
		}

		// Try action as controller with captureall action
		// e.g. /admin -> \Sonic\Controller\Admin->captureall ()

		elseif ($captureall && 
			class_exists ($controller . '\\' . ucfirst ($this->action)) && 
			method_exists ($controller . '\\' . ucfirst ($this->action), 'captureall') &&
			static::isInstantiable ($controller . '\\' . ucfirst ($this->action)))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->action);
			$this->action		= 'captureall';
			return TRUE;
		}

		// Try Action\Index as the controller with index action
		// e.g. /admin -> \Sonic\Controller\Admin\Index->index ()

		elseif (class_exists ($controller . '\\' . ucfirst ($this->action) . '\\Index') && 
			method_exists ($controller . '\\' . ucfirst ($this->action) . '\\Index', 'index') &&
			static::isInstantiable ($controller . '\\' . ucfirst ($this->action) . '\\Index'))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->action) . '\\Index';
			$this->action		= 'index';
			return TRUE;
		}

		// Try Action\Index as the controller with captureall action
		// e.g. /admin -> \Sonic\Controller\Admin\Index->captureall ()

		elseif ($captureall && 
			class_exists ($controller . '\\' . ucfirst ($this->action) . '\\Index') && 
			method_exists ($controller . '\\' . ucfirst ($this->action) . '\\Index', 'captureall') &&
			static::isInstantiable ($controller . '\\' . ucfirst ($this->action) . '\\Index'))
		{
			$this->controller	= $controller . '\\' . ucfirst ($this->action) . '\\Index';
			$this->action		= 'captureall';
			return TRUE;
		}
		
		// No match
		
		return FALSE;
		
	}
	
	
}