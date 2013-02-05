<?php

// Define namespace

namespace Sonic\View;

// Start Basic Class

class Basic
{
	
	
	/**
	 * Template variables for use in the template
	 * @var array
	 */
	
	private $vars			= array ();
	
	/**
	 * Template directory
	 * @var string 
	 */
	
	private $templateDir	= FALSE;
	
	/**
	 * Start delimiter for variable replacement
	 * @var string 
	 */
	
	public $startDelimiter	= '{';
	
	/**
	 * End delimiter for variable replacement
	 * @var string 
	 */
	
	public $endDelimiter	= '}';
	
	
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
		
		$template	.= $controller? str_replace ('\\', '/', $controller) . '/': '';
		
		// If there is no template file yet just use the action
		
		$template	.= $action;
		
		// Lowercase and add tpl extension
		
		$template	= strtolower ($template) . '.tpl';
		
		// Return template
		
		return $template;
		
	}
	
	
	/**
	 * Display a template
	 * @param string $file Template file to display
	 * @param boolean $output Whether to output the template
	 * @return string
	 */
	
	public function display ($file, $output = TRUE)
	{
		
		// If there is no template directory set then try and use default root/templates directory
		
		if (!$this->templateDir)
		{
			$this->templateDir	= ABS_ROOT . 'templates' . DIRECTORY_SEPARATOR;
		}
		
		// Load template
		
		$template	= $this->loadTemplateFile ($file);
		
		// Replace variables
		
		$rendered	= $this->replaceVars ($template);
		
		// Output
		
		if ($output)
		{
			echo $rendered;
		}
		
		// Return template
		
		return $rendered;
		
	}
	
	
	/**
	 * Assign variable for use in the template
	 * @param string $key Variable identifier
	 * @param mixed $val Variable value
	 * @return void
	 */
	
	public function assign ($key, $val)
	{
		$this->vars[$key] = $val;
	}
	
	
	/**
	 * Set template directory
	 * @param string $dir Template directory
	 * @return void
	 */
	
	public function setTemplateDir ($dir)
	{
		$this->templateDir	= $dir;
	}
	
	
	/**
	 * Load a template file and return its contents
	 * @param string $file Template file to load
	 * @throws Exception
	 * @return string
	 */
	
	protected function loadTemplateFile ($file)
	{
		
		// Load template
		
		$template	= @file_get_contents ($this->templateDir . $file);
		
		if ($template === FALSE)
		{
			throw new \Sonic\Exception ('Unable to load template file ' . $this->templateDir . $file);
		}
		
		// Return template data
		
		return $template;
		
	}
	
	
	/**
	 * Replace variables in the template
	 * @param string $data Template data to replace variables in
	 * @return string
	 */
	
	protected function replaceVars ($data)
	{
		
		// Replace variables
		
		foreach ($this->vars as $key => $val)
		{
			$data	= str_replace ($this->startDelimiter . $key . $this->endDelimiter, $val, $data);
		}
		
		// Remove any remaining variables in the template
		
		$data	= preg_replace ('/' . $this->startDelimiter . '.*' . $this->endDelimiter . '/', '', $data);
		
		// Return replaced data string
		
		return $data;
		
	}
	
	
}