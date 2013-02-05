<?php

// Define namespace

namespace Sonic\Resource;

// Start Api Class

class Api
{
	
	
	/**
	 * API Action
	 * @var string
	 */
	
	protected $action		= NULL;
	
	/**
	 * API Result
	 * @var array
	 */
	
	protected $result		= array ();
	
	/**
	 * Method Arguments
	 * @var array
	 */
	
	protected $args			= array ();
	
	/**
	 * Request Type
	 * @var string
	 */
	
	protected $requestType	= 'post';
	
	/**
	 * Return Format
	 * @var string
	 */
	
	protected $returnType	= 'json';
	
	/**
	 * API Modules
	 * @var array
	 */
	
	protected $modules		= array ();
	
	/**
	 * Root node name for XML
	 * @var string
	 */
	
	protected $rootNode		= 'api';
	
	/**
	 * API method limitations
	 * Defaults to all modules/methods accessible.
	 * This allows you to determine which modules and methods are accessible through the API.
	 * The 'all' key is either TRUE or FALSE, and overrides any other limits.
	 *   This needs to be removed if you have set specific module limitations.
	 * Module limits can be defined by setting them as a key, and method permissions as a sub array.
	 *   Method values can be TRUE or FALSE to determine where they can be called or not.
	 *   The module value can also be TRUE or FALSE to set a limit for the entire module is no methods are set.
	 * @var array
	 */
	
	protected $actionLimit	= array ('all' => TRUE);
	
	
	/**
	 * Deal with an api exception
	 * @param Exception $exception Exception
	 * @return void
	 */
	
	public function Exception ($exception)
	{
		
		// Clear output buffers
		
		while (ob_get_level ())
		{
			ob_end_clean ();
		}
		
		// Set error
		
		$this->result	= array (
			'status'	=> 'fail',
			'message'	=> 'exception',
			'exception'	=> array (
				'file'		=> $exception->getFile (),
				'line'		=> $exception->getLine (),
				'message'	=> $exception->getMessage ()
			)
		);
		
		// Auditlog
		
		$auditlog	= \Sonic\Sonic::getResource ('auditlog');

		if ($auditlog instanceof \Sonic\Resource\Audit\Log)
		{
			$auditlog::_Log ($this->action, 7, $this->args, $this->result['exception']);
		}
		
		// Display
		
		$this->Display ();
		
	}
	
	
	/**
	 * Wrapper function to complete an API request
	 * Set request type, action arguments and response type.
	 * Call the action and then log.
	 * @param string $action Action
	 * @return void
	 */
	
	public function callAction ($action = FALSE)
	{
		
		// Set the request type
		
		$this->setRequestType ();
		
		// Set arguments
		
		$this->setArguments ();
		
		// Set the return type
		
		$this->setReturnType ();
		
		// Call action
		
		$this->Action ($action);
		
	}
	
	
	/**
	 * Set the API request type
	 * @param string $type Request Type
	 * @return void
	 */
	
	public function setRequestType ($type = FALSE)
	{
		
		// If there is a request argument
		
		if ($type !== FALSE)
		{
			
			// Set it
			
			$this->requestType	= $type;
			
		}
		
		// Else if there is a request format URL variable
		
		else if (isset ($_GET['request_type']))
		{
			
			// Set it
			
			$this->requestType	= $_GET['request_type'];
			
		}
		
	}
	
	
	/**
	 * Set the API return type
	 * @param string $strType Return Type
	 * @return void
	 */
	
	public function setReturnType ($type = FALSE)
	{
		
		// If there is a return argument
		
		if ($type !== FALSE)
		{
			
			// Set it
			
			$this->returnType	= $type;
			
		}
		
		// Else if there is a return type in the arguments
		
		else if (isset ($this->args['return_type']))
		{
			
			// Set it
			
			$this->returnType	= $this->args['return_type'];
			
		}
		
	}
	
	
	
	/**
	 * Set the API request arguments
	 * @return void
	 */
	
	public function setArguments ()
	{
		
		// Switch the request type
		
		switch ($this->requestType)
		{
			
			// GET
			
			case 'get':
				
				$this->setArguments_GET ();
				break;
			
			// POST
			
			case 'post':
			default:
				
				$this->setArguments_POST ();
				break;
			
		}
		
	}
	
	
	/**
	 * Set the API arguments from a POST request
	 * @return void
	 */
	
	public function setArguments_POST ()
	{
		$this->args	= $_POST;
	}
	
	
	/**
	 * Set the API arguments from a GET request
	 * @return void
	 */
	
	public function setArguments_GET ()
	{
		$this->args	= $_GET;
	}
	
	
	/**
	 * Call an API action
	 * @param string $strAction Action
	 * @return mixed
	 */
	
	public function Action ($strAction = FALSE)
	{
		
		// If an action argument is set
		
		if ($strAction)
		{
			
			// Set it
			
			$this->action	= $strAction;
			
		}
		
		// Else if a method argument is set
		
		else if (isset ($this->args['method']))
		{
			
			// Set it
			
			$this->action	= $this->args['method'];
			
		}
		
		// Auditlog
		
		$auditlog		= \Sonic\Sonic::getResource ('auditlog');
		$logType		= 2; // Default to success
		$logParams		= FALSE;
		$logResponse	= FALSE;
		
		// Explode action
		
		$arrAction	= explode ('.', $this->action);
		
		// If there are 2 or more items then there is a module and permission
		
		if (count ($arrAction) >= 2)
		{
			
			// Set the module and method names

			$strMethod	= array_pop ($arrAction);
			$strModule	= implode ('.', $arrAction);

			// Get the module object

			$objModule	= $this->getModule ($strModule);
			
			// If the module was loaded
			
			if ($objModule)
			{
				
				// If the method exists
				
				if ($objModule->checkMethod ($strMethod))
				{
					
					// Check the method limitation
					
					if ($this->checkLimitation ($strModule, $strMethod))
					{
						
						// Call the method
						
						$arrReturn	= $objModule->callMethod ($this, $strModule, $strMethod, $this->args);
						
						// If the return is ok
						
						if ($arrReturn['status'] == 'ok')
						{
							
							// Set as success
							
							$this->result['status']	= 'ok';
							
							// Remove any result status
							
							unset ($arrReturn['status']);
							
							// Add return
							
							if ($arrReturn)
							{
								
								$this->result[$strMethod]	= $arrReturn;
								
							}
							
							// Auditlog

							$logType		= 2;
//							$logParams		= $this->args;
//							$logResponse	= $this->result;
							
						}
						else
						{
							
							// Set any results
							
							foreach ($arrReturn as $strKey => $strVal)
							{
								
								$this->result[$strKey]	= $strVal;
								
							}
							
							// Auditlog

							$logType		= 3;
							$logParams		= $this->args;
							$logResponse	= @$this->result['message'];
							
						}
						
					}
					
					// Else the module or method is limited
					
					else
					{
						
						// Set error
						
						$this->result['status']		= 'fail';
						$this->result['message']	= 'method access limited';
						
						// Set auditlog type

						$logType = 11;
					
					}
					
				}
				
				// Else the method was not loaded
				
				else
				{
					
					// Set error
					
					$this->result['status']		= 'fail';
					$this->result['message']	= 'invalid method';

					// Set auditlog type

					$logType = 10;

				}
			
			}
			
			// Else the module was not loaded
			
			else
			{
				
				// Set error
				
				$this->result['status']		= 'fail';
				$this->result['message']	= 'invalid module';

				// Set auditlog type
				
				$logType = 9;

			}
			
		}
		
		// Else invalid action format
		
		else
		{
			
			// Set error
			
			$this->result['status']		= 'fail';
			$this->result['message']	= 'invalid action';

			// Set auditlog type

			$logType = 8;

		}
		
		// Set the request and response types and the action
		
		$this->result['method']			= $this->action;
		$this->result['request_type']	= $this->requestType;
		$this->result['return_type']	= $this->returnType;
		
		// Auditlog

		if ($auditlog instanceof \Sonic\Resource\Audit\Log)
		{
			$auditlog::_Log ($this->action, $logType, $logParams, $logResponse);
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	
	/**
	 * Return an API module object if it exists
	 * @param string $module Module name
	 * @return Api\Module
	 */
	
	public function getModule ($module)
	{
		
		// Load modules
		
		$this->getModules ();
		
		// Loop through the modules
		
		foreach ($this->modules as $obj)
		{
			
			// If the module names match and the class exists
			
			if (strtolower ($obj->get ('name')) == $module && class_exists ($obj->getModuleClass ()))
			{
				
				// Return the module
				
				return $obj;
				
			}
			
		}
		
		// Return FALSE
		
		return FALSE;
		
	}
	
	
	
	/**
	 * Load the API modules
	 * @return array
	 */
	
	public function getModules ()
	{
		
		// If the modules have not been loaded
		
		if (!$this->modules)
		{
			
			// Set the module classs
			
			$class	= get_called_class () . '\Module';
			
			// Load the modules
			
			$this->modules	= $class::_getObjects ();
			
		}
		
		// Return the modules
		
		return $this->modules;
		
	}
	
	
	/**
	 * Check whether a module method is limited
	 * @param string $module Module Name
	 * @param string $method Method Name
	 * @return param
	 */
	
	public function checkLimitation ($module, $method)
	{
		
		// If no methods are defined or all is denied
		
		if (!$this->actionLimit || !Parser::_ak ($this->actionLimit, 'all', TRUE))
		{
			
			// Deny all
			
			return FALSE;
			
		}
		
		// If all methods are enabled
		
		if (Parser::_ak ($this->actionLimit, 'all', FALSE))
		{
			
			// Allow all
			
			return TRUE;
			
		}
		
		// If the module method is allowed
		
		if (Parser::_ak ($this->actionLimit, array ($module, $method), FALSE))
		{
			
			// Allow
			
			return TRUE;
			
		}
		
		// If the module is allowed
		
		if (Parser::_ak ($this->actionLimit, $module, FALSE) === TRUE)
		{
			
			// Allow
			
			return TRUE;
			
		}
		
		// Deny
		
		return FALSE;
		
	}
	
	
	
	/**
	 * Display the API result
	 * @return string
	 */
	
	public function Display ()
	{
		
		// Send headers
		
		header ("Expires: Sun, 23 Nov 1984 03:13:37 GMT");
		header ("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
		header ("Cache-Control: no-store, no-cache, must-revalidate");
		header ("Cache-Control: post-check=0, pre-check=0", FALSE);
		header ("Pragma: no-cache");
		
		// Remove auth error if the user is authenticated
		// This is created during the user init even if they are later authenticated
		
		if (\Sonic\Resource\Parser::_ak ($this->result, 'authenticated', FALSE) !== 0)
		{
			unset ($this->result['auth_error']);
		}
		
		//ob_start ('ob_gzhandler');
		
		// Return
		
		switch ($this->returnType)
		{
				
			// XML
			
			case 'xml':
				
				header ('Content-Type: application/xml');
				echo $this->outputXML ();
				break;
			
			// Serialized
			
			case 'serialized':
				
				header ('Content-Type: application/vnd.php.serialized');
				echo $this->outputSerialized ();
				break;
				
			// ExtJS
			
			case 'extjs':
				
				// ExtJS must return success status, so convert the status variable
				
				$this->result['success']	= $this->result['status'] == 'ok';
				unset ($this->result['status']);
				
				header ('Content-Type: application/json');
				echo $this->outputJSON ();
				break;
                            
			// Special case for file uploads as chrome cannot handle JSON content type responses
                            
			case 'fileupload':
				
				// ExtJS must return success status, so convert the status variable
				
				$this->result['success']	= $this->result['status'] == 'ok';
				unset ($this->result['status']);
				
				header ('Content-Type: text/html');
				echo $this->outputJSON ();
				break;
				
                            
			// JSON
			
			case 'json':
			default:
				
				header ('Content-Type: application/json');
				echo $this->outputJSON ();
				break;
				
		}
		
		//ob_end_flush ();
		
	}
	
	
	
	/**
	 * Return JSON encoded result
	 * @return string
	 */
	
	public function outputJSON ()
	{
		return json_encode ($this->result);
	}
	
	
	
	/**
	 * Return XML encoded result
	 * @return string
	 */
	
	public function outputXML ()
	{
		
		// Create DOMDocument
		
		$doc	= new \DOMDocument ('1.0', 'UTF-8');
		
		// Generate XML
		
		$xml	= $this->generateXML ($doc);
		
		// Append XML
		
		$doc->appendChild ($xml);
		
		// Return XML
		
		return $doc->saveXML ();
		
	}
	
	
	
	/**
	 * Generate XML from result
	 * @param object $doc DOMDocument Object
	 * @param array $arr Array element to convert
	 * @param object $xml DOMElement Parent Object
	 * @return \DomElement
	 */
	
	public function generateXML (&$doc, $arr = FALSE, $xml = FALSE)
	{
		
		// If there is DOMElement reate the root element
		
		if ($xml === FALSE)
		{	
			$xml	= $doc->createElement (strtolower ($this->rootNode));	
		}
		
		// If there is no element set to the result
		
		if ($arr === FALSE)
		{
			$arr	= $this->result;
		}
			
		// For each element in the element types
		
		foreach ($arr as $key => $val)
		{
			
			// If the element is an object and the key is a number
			
			if (is_object ($val) && is_numeric ($key))
			{
				
				// Set key to class name
				
				$key	= get_class ($val);
				
			}
			
			// Else if there is an array key 'class'
			
			else if (is_array ($val) && array_key_exists ('class', $val))
			{
				
				// Set key to class name
				
				$key	= $val['class'];
				
				// Remove class key
				
				unset ($val['class']);
				
			}
			
			// Else if the key is a number
			
			else if (is_numeric ($key))
			{
				
				// Set key to string
				
				$key	= 'result_' . $key;
				
			}
			
			// Create a new element
			
			$element	= $doc->createElement (strtolower ($key));
			
			// If the value is an array generate XML
			
			if (is_array ($val))
			{
				$element	= $this->generateXML ($doc, $val, $element);
			}
			
			// Else set the element value
			
			else
			{
				$element->nodeValue	= htmlentities ($val);
			}
			
			// Append element
			
			$xml->appendChild ($element);
			
		}
		
		return $xml;
		
	}
	
	
	/**
	 * Return serialized result
	 * @return string
	 */
	
	public function outputSerialized ()
	{
		return serialize ($this->result);
	}
	
	
	
}

// End Api Class