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
		
		$this->setTemplateDir (SMARTY_TEMPLATE_DIR);
		$this->setCacheDir (SMARTY_CACHE_DIR);
		$this->setCompileDir (SMARTY_COMPILE_DIR);
		$this->setConfigDir (SMARTY_CONFIG_DIR);
		$this->addPluginsDir (SMARTY_PLUGINS_DIR);
		
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
		
		// Add the controller path and convert \ to /
		
		$template	.= $controller? str_replace ('\\', '/', $controller) . '/' : '';
		
		// If there is no template file yet just use the action
		
		$template	.= $action;
		
		// Lowercase and add tpl extension
		
		$template	= strtolower ($template) . '.tpl';
		
		// Return template
		
		return $template;
		
	}
	
	
}