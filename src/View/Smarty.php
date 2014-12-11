<?php

// Define namespace

namespace Sonic\View;

// Require smarty include

require_once (ABS_INCLUDES . 'smarty.php');

// Start Smarty Class

class Smarty extends \Smarty
{
	
	
	/**
	 * Instantiate class
	 * @return void
	 */
	
	public function __construct ()
	{
		
		parent::__construct ();
		
		$this->setTemplateDir (SONIC_SMARTY_TEMPLATE_DIR);
		$this->setCacheDir (SONIC_SMARTY_CACHE_DIR);
		$this->setCompileDir (SONIC_SMARTY_COMPILE_DIR);
		$this->setConfigDir (SONIC_SMARTY_CONFIG_DIR);
		$this->addPluginsDir (SONIC_SMARTY_PLUGINS_DIR);
		
		if (defined ('SONIC_SMARTY_ERROR_REPORTING'))
		{	
			$this->error_reporting	= SONIC_SMARTY_ERROR_REPORTING;
		}
		
	}
	
	
	/**
	 * Return the default template path for the controller
	 * @param string $module Controller module
	 * @param string $controller Controller
	 * @param string $action Action 
	 * @return string
	 */
	
	public function defaultTemplate ($module, $controller, $action)
	{
		
		// Start template path with the controller module
		
		$template	= $module? $module . '/' : '';
		
		// If theres a controller then add it
		
		if ($controller)
		{
		
			// Split controller and remove the final block if its Index

			$arrController	= explode ('\\', $controller);
			$pop = strtolower (array_pop ($arrController));
			
			if ($pop != 'index')
			{
				$arrController[] = $pop;
			}

			$template	.= join ('/', $arrController) . '/';
				
		}
		
		// Remove initial /
		
		if ($template[0] == '/')
		{
			$template	= substr ($template, 1);
		}
		
		// If there is no template file yet just use the action
		
		$template	.= $action;
		
		// Lowercase and add tpl extension
		
		$template	= strtolower ($template) . '.tpl';
		
		// Return template
		
		return $template;
		
	}
	
	
}