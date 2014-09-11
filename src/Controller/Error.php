<?php

// Define namespace

namespace Sonic\Controller;

// Start Error Class

class Error
{
	
	
	/**
	 * Error codes and messages
	 * @var array
	 */
	
	public static $errors	= array ();
	
	/**
	 * Error code
	 * @var integer 
	 */
	
	private $code	= FALSE;
	
	
	/**
	 * Create new error object
	 * @param integer $code Error code to assign to object
	 * return \Sonic\Controller\Error
	 */
	
	public function __construct ($code)
	{
		$this->setCode ($code);
	}
	
	
	/**
	 * Set error code
	 * @param integer $code Error code
	 * @return void
	 */
	
	public function setCode ($code)
	{
		$this->code	= $code;
	}
	
	
	/**
	 * Return error code
	 * @return integer
	 */
	
	public function getCode ()
	{
		return $this->code;
	}
	
	
	/**
	 * Return error message
	 * @return string 
	 */
	
	public function getMessage ()
	{
		return isset (static::$errors[$this->code])? static::$errors[$this->code] : 'Unknown Error';
	}
	
	
}