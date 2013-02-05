<?php

// Define namespace

namespace Sonic\Resource;

// Start Session Class

class Session
{
	
	
	/**
	 * Object singleton instance
	 * @var \Sonic\Resource\Session
	 */
	
	private static $_instance	= FALSE;
	
	/**
	 * Session timeout since last action
	 * @var integer
	 */
	
	private $_timeout		= 3600; // 1 hour
	
	/**
	 * Cookie path
	 * @var string
	 */
	
	private $_cookiePath	= '/';
	
	/**
	 * Session cooke timeout
	 * @var integer
	 */
	
	private $_cookieTimeout	= 86400; // 1 day
	
	/**
	 * Session ID
	 * @var string
	 */
	
	private $_sessionID		= NULL;
	
	
	/**
	 * Instantiate the class
	 * @param string $sessionID Session ID  
	 */
	
	private function __construct ($sessionID = FALSE)
	{
		
		// Set session related ini settings
		
		ini_set ('session.use_only_cookies', 1);
		ini_set ('mbstring.http_output', 'UTF-8');
		
		// Set session cookie parameters
		
		session_set_cookie_params ($this->_cookieTimeout, $this->_cookiePath);
		
		// Create session
		
		$this->Create ($sessionID);
		
	}
	
	
	/**
	 * Return a single object instance
	 * @param string $sessionID Session ID
	 * @return \Sonic\Resource\Session
	 */
	
	public static function singleton ($sessionID = FALSE)
	{
		
		// If no instance is set
		
		if (self::$_instance === FALSE)
		{
			
			// Create an instance
			
			self::$_instance	= new static ($sessionID);
			
		}
		
		// Return instance
		
		return self::$_instance;
		
	}
	
	
	/**
	 * Start a new session
	 * @param string $sessionID Session ID
	 * @return void
	 */
	
	public function Create ($sessionID = FALSE)
	{
		
		// If there is no session
		
		if (!session_id ())
		{
			
			// If there is a session ID
			
			if ($sessionID)
			{
				
				// Set it
				
				session_id ($sessionID);
				
			}
			
			// Start session
			
			session_start ();

		}

		
		// Send cache headers

		header ('Expires: Sun, 23 Nov 1984 03:13:37 GMT');
		header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
		header ('Cache-Control: no-store, no-cache, must-revalidate');
		header ('Cache-Control: post-check=0, pre-check=0', FALSE);
		header ('Pragma: no-cache');

		// Set session ID

		$this->_sessionID	= session_id ();
		
	}
	
	
	/**
	 * Return a session variable
	 * @param string $name Variable name
	 * @return mixed
	 */
	
	public function get ($name)
	{
		
		if (array_key_exists ($name, $_SESSION))
		{
			return $_SESSION[$name];
		}
		else
		{
			return FALSE;
		}
		
	}
	
	
	/**
	 * Set a session variable
	 * @param string $name Variable name
	 * @param mixed $val Variable value
	 * @return void
	 */
	
	public function set ($name, $val)
	{
		$_SESSION[$name]	= $val;
	}
	
	
	/**
	 * Check whether a session has timed out
	 * @param integer $timestamp Last action timeout
	 * @return boolean
	 */
	
	public function timedOut ($timestamp)
	{
		return $timestamp < (time () - $this->_timeout);
	}
	
	
	/**
	 * Generate a new session ID
	 * @return void
	 */
	
	public function Refresh ()
	{
		
		// Refresh session
		
		session_unset ();
		session_regenerate_id ();
		
	}
	
	
	/**
	 * Destroy the session
	 * @return boolean
	 */
	
	public function Destroy ()
	{
		
		// If no session has been started, bail here before session_destroy() errors
		
		$sessionID	= session_id ();
		
		if (empty ($sessionID))
		{
			return FALSE;
		}

		// Cookie Destruction
		
		$cookie	= session_get_cookie_params ();
		
		if ((empty ($cookie['domain'])) && (empty ($cookie['secure'])))
		{
			setcookie (session_name (), '', time () -99999, $cookie['path']);
		}
		elseif (empty ($cookie['secure']))
		{
			setcookie (session_name (), '', time () -99999, $cookie['path'], $cookie['domain']);
		}
		else
		{
			setcookie (session_name (), '', time () -99999, $cookie['path'], $cookie['domain'], $cookie['secure']);
		}
		
		// Session Destruction
		
		$_SESSION	= array ();
		
		return session_destroy ();
		
	}
	
	
}

// End Session Class