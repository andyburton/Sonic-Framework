<?php

// Define namespace

namespace Sonic;

// Start Controller Class

class Controller
{
	
	
	/**
	 * Methods that cannot be called as actions 
	 * @var array
	 */
	
	protected $invalidActions	= array ('isactionvalid', 'callaction', 'view', 'exception');
	
	/**
	 * Request variables
	 * @var array
	 */
	
	public $request				= array ();
	
	/**
	 * Controller module
	 * @var string 
	 */
	
	public $module				= FALSE;
	
	/**
	 * Controller to call
	 * @var string 
	 */
	
	public $controller			= FALSE;
	
	/**
	 * Action to call on controller
	 * @var string
	 */
	
	public $action				= FALSE;
	
	/**
	 * View object to render the template
	 * @var object
	 */
	
	public $view				= FALSE;
	
	/**
	 * Whether to render the view for the controller
	 * @var boolean
	 */
	
	public $render				= TRUE;
	
	/**
	 * View template file to display
	 * @var string
	 */
	
	public $template			= FALSE;
	
	
	/**
	 * Instantiate class
	 * @return void
	 */
	
	public function __construct ()
	{
		
		// Set request variables
		
		$this->request	= array (
			'get'		=> $_GET,
			'post'		=> $_POST,
			'server'	=> $_SERVER
		);
		
	}
	
	
	/**
	 * Check that the controller action is valid
	 * Any action in invalidAction or beginning with __ is invalid
	 * @return boolean 
	 */
	
	public function isActionValid ()
	{
		return substr ($this->action, 0, 2) !== '__' && !in_array (strtolower ($this->action), $this->invalidActions);
	}
	
	
	/**
	 * Call controller action method
	 * @return void 
	 */
	
	public function callAction ()
	{
		$this->{$this->action} ();
	}
	
	
	/**
	 * Render view
	 * @return void 
	 */
	
	public function View ()
	{
		
		// If there is no view then use basic by default
		
		if (!$this->view)
		{
			$this->view		= new View\Basic;
		}
		
		// If there is no template then set default
		
		if (!$this->template)
		{
			$this->template	= $this->view->defaultTemplate ($this->module, $this->controller, $this->action);
		}
		
		// Display view
		
		$this->view->display ($this->template);
		
	}
	
	
	/**
	 * Return an argument, first from POST or from GET if POST fails
	 * @param string $name Argument name
	 * @return mixed
	 */
	
	protected function getArg ($name)
	{
		
		if (isset ($this->request['post'][$name]))
		{
			return $this->request['post'][$name];
		}
		else
		{
			return $this->getURLArg ($name);
		}
			
	}
	
	
	/**
	 * Return a $_GET argument or FALSE if not set
	 * @param string $name Argument name
	 * @return mixed
	 */
	
	protected function getURLArg ($name)
	{
		return isset ($this->request['get'][$name])? $this->request['get'][$name] : FALSE;
	}
	
	
	/**
	 * Return a $_POST argument or FALSE if not set
	 * @param string $name Argument name
	 * @return mixed
	 */
	
	protected function getPostArg ($name)
	{
		return isset ($this->request['post'][$name])? $this->request['post'][$name] : FALSE;
	}
	
	
	/**
	 * Deal with an exception
	 * @param Exception $exception Exception
	 * @return void
	 */
	
	public function Exception ($exception)
	{

		// Auditlog
		
		$auditlog	= \Sonic\Sonic::getResource ('auditlog');

		if ($auditlog instanceof \Sonic\Resource\Audit\Log)
		{
			$auditlog::_Log (get_called_class () . '\\' . $this->action, 7, $this->request, array (
				'file'		=> $exception->getFile (),
				'line'		=> $exception->getLine (),
				'message'	=> $exception->getMessage ()
			));
		}
		
		// Display message
		
		echo 'Uncaught exception `' . $exception->getMessage () . '` in ' . $exception->getFile () . ' on line ' . $exception->getLine ();
		
	}
	

}