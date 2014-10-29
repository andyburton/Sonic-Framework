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
		
		$redirect	= isset ($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '';
		
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
	
	
}