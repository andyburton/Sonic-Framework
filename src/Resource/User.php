<?php

// Define namespace

namespace Sonic\Resource;

// Start User Class

class User extends \Sonic\Model
{
	
	
	/**
	 * Default length of automatically generated passwords
	 */
	
	const DEFAULT_PASSWORD_LENGTH		= 10;

	/**
	 * Default characters to use for automatically generated passwords
	 */
	
	const DEFAULT_PASSWORD_CHARACTERS	= '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ!"%&*-=_+#';
	
	
	/**
	 * Session resource
	 * @var \Sonic\Resource\Session
	 */
	
	public $session				= FALSE;
	
	
	/**
	 * Tmp Session ID
	 * @var string
	 */
	
	private $sessionID			= FALSE;
	
	/**
	 * User Permissions
	 * @var array
	 */
	
	public $permissions			= array ();
	
	/**
	 * Login timestamp
	 * @var integer
	 */
	
	protected $loginTimestamp	= FALSE;
	
	/**
	 * Last action timestamp
	 * @var integer
	 */
	
	protected $lastAction		= FALSE;
	
	/**
	 * Login status
	 * @var boolean
	 */
	
	protected $loggedIn			= FALSE;
	
	/**
	 * Session data
	 * @var array
	 */
	
	private $_sessionData		= array ();
	
	
	
	/**
	 * Instantiate class
	 * @param string $sessionID Session ID
	 * @return
	 */
	
	public function __construct ($sessionID = FALSE)
	{
		
		// Call parent
		
		parent::__construct ();
		
		// Set session ID
		
		$this->sessionID	= $sessionID;
		
	}
	
	
	/**
	 * Create a new user
	 * @param array $exclude Attributes not to set
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function Create ($exclude = array (), &$db = FALSE)
	{
		
		// Check email is unique
		
		if (!$this->uniqueEmail ())
		{
			
			// Set message
			
			new \Sonic\Message ('error', 'The email address `' . $this->iget ('email') . '` is already assigned to an account! Please choose another.');
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Hash password
		
		$password	= $this->iget ('password');
		$this->iset ('password', self::_Hash ($password));
		
		// Call parent method
		
		$parent	= parent::Create ($exclude, $db);
		
		// Reset password
		
		$this->iset ('password', $password);
		
		// Return
		
		return $parent;
		
	}

	
	/**
	 * Update a user
	 * @param array $exclude Attributes not to update
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function Update ($exclude = array (), &$db = FALSE)
	{
		
		// Check email is unique
		
		if (!$this->uniqueEmail ())
		{
			
			// Set message
			
			new \Sonic\Message ('error', 'The email address `' . $this->iget ('email') . '` is already assigned to an account! Please choose another.');
			
			// Return FALSE
			
			return FALSE;
			
		}
		
		// Hash password
		
		$password	= $this->iget ('password');
		
		// If password is not in exclude array
		
		if (!in_array ('password', $exclude))
		{
			
			// If no password is set
			
			if (!$this->attributeHasValue ('password'))
			{
				
				// Get the existing password hash
				
				$this->readAttribute ('password');
				
			}
			
			// Else password is set
			
			else
			{
				
				// Hash password
				
				$this->iset ('password', self::_Hash ($password));
				
			}
			
		}
		
		// Call parent method
		
		$parent	= parent::Update ($exclude, $db);
		
		// Reset password
		
		$this->iset ('password', $password);
		
		// Return
		
		return $parent;
		
	}
	
	
	/**
	 * Delete an object in the database
	 * @param array|integer $params Primary key value or parameter array
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function Delete ($params = FALSE, &$db = FALSE)
	{
		
		// Get database master for write

		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// Get objects
		
		$objs	= is_array ($params)? self::_getObjects ($params, $db) : [self::_read ($params, $db)];
		
		// Remove each
		
		$db->beginTransaction ();
		
		foreach ($objs as $obj)
		{
			if (!$obj->Remove ())
			{
				$db->rollBack ();
				return FALSE;
			}
		}
		
		$db->commit ();
		return TRUE;
		
	}
	
	
	/**
	 * Mark a user as removed
	 * @return boolean
	 */
	
	public function Remove ()
	{
		return $this->updateAttribute ('removed', '1');
	}
	
	
	/**
	 * Check whether the email address for the account already exists
	 * @return boolean
	 */
	
	public function uniqueEmail ()
	{
		
		// Get count
		
		$count	= static::_Count (array (
			'where'	=> array (
				array ('email',		$this->iget ('email')),
				array ('id',		$this->iget ('id'),	'<>'),
				array ('removed',	0)
			)
		));
		
		return $count == 0;
		
	}
	
	
	/**
	 * Return user session
	 * @return \Sonic\Resource\Session
	 */
	
	public function session ()
	{
		
		if (!($this->session instanceof Session))
		{
			$this->session	= Session::singleton ($this->sessionID);
		}
		
		return $this->session;
		
	}
	
	/**
	 * Set user data from a session
	 * @return array
	 */
	
	public function getSessionData ()
	{
		
		if (!$this->_sessionData)
		{
			$this->_sessionData	= unserialize ($this->session ()->get (get_called_class ()));
		}
		
		return $this->_sessionData;
		
	}
	
	
	/**
	 * Set user data from a session
	 * @return void
	 */
	
	public function fromSessionData ()
	{
		
		$arr	= $this->getSessionData ();
		
		if (isset ($arr['id']))
		{
			$this->iset ('id', $arr['id']);
		}
		
		$this->fromArray ($arr, FALSE, FALSE);
		
		$this->loginTimestamp	= !Parser::_ak ($arr, 'login_timestamp', FALSE);
		$this->lastAction		= !Parser::_ak ($arr, 'last_action', FALSE);
		
	}
	
	
	/**
	 * Set user data in a session
	 * @return void
	 */
	
	public function setSessionData ()
	{
		
		$arr	= $this->toArray ();
		
		$arr['login_timestamp']	= $this->loginTimestamp;
		$arr['last_action']		= $this->lastAction;
		
		$this->session ()->set (get_called_class (), serialize ($arr));
		
	}
	
	
	/**
	 * Update the last action time to the current time
	 * @return void
	 */
	
	public function updateLastAction ()
	{
		
		$arr	= $this->getSessionData ();
		
		$this->lastAction		= time ();
		$arr['last_action']		= $this->lastAction;
		
		$this->session ()->set (get_called_class (), serialize ($arr));
		
	}
	
	
	/**
	 * Initialise a user session and check it is valid
	 * @return string|boolean Error
	 */
	
	public function initSession ()
	{
		
		// Get session
		
		$session	= $this->getSessionData ();
		
		// Check session is valid
		
		if ($this->checkSession ($session) !== TRUE)
		{
			return $this->Logout ('invalid_session');
		}
		
		// Load user from session
		
		$this->fromSessionData ();
		
		// Read user

		if (!$this->Read ())
		{
			return $this->Logout ('user_read_error');
		}
		
		// Reset password
		
		$this->reset ('password');
		
		// If the user is not active or the active status has changed
		
		if ($session['active'] != $this->iget ('active') || !$this->iget ('active'))
		{
			return $this->Logout ('inactive');
		}
		
		// Check if the session has timed out
		
		if ($this->session ()->timedOut ($session['last_action']))
		{
			return $this->Logout ('timeout');
		}
		
		// Update action time
		
		$this->updateLastAction ();
		
		// Set login status
		
		$this->loggedIn	= TRUE;
		
		// Redirect to originally requested URL
		
		if ($this->session ()->get ('requested_url'))
		{
			$url = $this->session ()->get ('requested_url');
			$this->session ()->set ('requested_url', FALSE);
			new Redirect ($url);
		}
		
		// return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Whether the user has a valid session
	 * @return boolean
	 */
	
	public function validSession ()
	{
		
		// Get session
		
		$session	= $this->getSessionData ();
		
		// Check session is valid
		
		if ($this->checkSession ($session) !== TRUE)
		{
			$this->loggedIn	= FALSE;
			return FALSE;
		}
		
		// If the user is not active or the active status has changed
		
		if ($session['active'] !== $this->iget ('active') || !$this->iget ('active'))
		{
			$this->loggedIn	= FALSE;
			return FALSE;
		}
		
		// Check if the session has timed out
		
		if ($this->session ()->timedOut ($session['last_action']))
		{
			$this->loggedIn	= FALSE;
			return FALSE;
		}
		
		// Set login status
		
		$this->loggedIn	= TRUE;
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Check that a session is valid
	 * @param array $session Session data to check
	 * @return string|boolean Error
	 */
	
	public function checkSession ($session = FALSE)
	{
		
		// If there is no session, get it
		
		if ($session === FALSE)
		{
			$session	= $this->getSessionData ();
		}
		
		// No id
		
		if (!Parser::_ak ($session, 'id', FALSE))
		{
			return 'no_id';
		}
		
		// No email
		
		if (!Parser::_ak ($session, 'email', FALSE))
		{
			return 'no_email';
		}
		
		// No login timestamp
		
		if (!Parser::_ak ($session, 'login_timestamp', FALSE))
		{
			return 'no_login_timestamp';
		}
		
		// No last action
		
		if (!Parser::_ak ($session, 'last_action', FALSE))
		{
			return 'no_last_action';
		}
		
		// No active status
		
		if (!Parser::_ak ($session, 'active', FALSE))
		{
			return 'no_active';
		}
		
		// return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Return currrent login status
	 * @return boolean
	 */
	
	public function loggedIn ()
	{
		return $this->loggedIn;
	}
	
	
	/**
	 * Logout the user
	 * return string Reason
	 */
	
	public function Logout ($reason = FALSE)
	{
		
		// Set login status
		
		$this->loggedIn	= FALSE;
		
		// Destroy session
		
		$this->session ()->Destroy ();
		
		// Remove session data
		
		$this->_sessionData	= FALSE;
		
		// Create a new session
		
		$this->session ()->Create ();
		$this->session ()->Refresh ();
		
		// Store requested URL if there is a reason we're logging out
		// If no reason then just a logout request, so we dont want to store
		// it else we'll create a loop should they try to log back in.
		
		if ($reason !== FALSE)
		{
			$this->session ()->set ('requested_url', $_SERVER['REQUEST_URI']);
		}
		
		// Return
		
		return $reason;
		
	}
	
	
	/**
	 * Log a user in
	 * @param string $email User email address
	 * @param string $password User password
	 * @return \Sonic\Resource\User|boolean Error
	 */
	
	public static function _Login ($email, $password)
	{
		
		// Authenticate
		
		if (static::_Authenticate ($email, $password) !== TRUE)
		{
			return 'authentication_fail';
		}
		
		// Get user
		
		$user	= static::_readFromEmail ($email);
		
		if (!$user)
		{
			return 'invalid_user';
		}
		
		// Reset password
		
		$user->reset ('password');
		
		// Set timestamps
		
		$user->loginTimestamp	= time ();
		$user->lastAction		= $user->loginTimestamp;
		
		// Set session
		
		$user->setSessionData ();
		
		// Return user object
		
		return $user;
		
	}
	
	
	/**
	 * Authenticate and attempt to login a user
	 * @param string $email User email address
	 * @param string $password User password
	 * @return boolean
	 */
	
	public static function _Authenticate ($email, $password)
	{
		
		// Get user
		
		$user	= static::_readFromEmail ($email);
		
		if (!$user)
		{
			return FALSE;
		}
		
		// Check password
		
		return $user->checkPassword ($password);
		
	}
	
	
	/**
	 * Return whether a password is valid
	 * @param string $check Password to check
	 * @return boolean
	 */
	
	public function checkPassword ($check)
	{
		return password_verify ($check, $this->get ('password'));
	}
	
	
	/**
	 * Return a user given an email address
	 * @param string $email User email address
	 * @return \Sonic\Resource\User
	 */
	
	public static function _readFromEmail ($email)
	{
		
		// Set user parameters
		
		$params['where']	= array (
			array ('email',		$email),
			array ('active',	'1'),
			array ('removed',	'0')
		);
		
		// Return user
		
		return static::_read ($params);
		
	}
	
	
	/**
	 * Hash a password
	 * @param string $password Password
	 * return string
	 */
	
	public static function _Hash ($password)
	{
		return password_hash ($password, PASSWORD_BCRYPT, ['cost' => 10]);
	}
	
	
	/**
	 * Generate a random password
	 * @param integer $length Password length
	 * @param string $charset Password character set
	 * @return type 
	 */
	
	public static function _randomPassword ($length = self::DEFAULT_PASSWORD_LENGTH, $charset = self::DEFAULT_PASSWORD_CHARACTERS)
	{
		
		// Set password variable
		
		$password	= '';

		// Set i to the password length
		
		$i = $length;

		// Loop until the password is the desired length
		
		while ($i > 0)
		{
			
			// Choose a random character from the charset
			
			$char = $charset[mt_rand (0, strlen ($charset) -1)];

			// Make sure that the first character of the password is a number or letter
			
			if ($password == '' && !preg_match('/[0-9A-Za-z]/', $char))
			{
				continue;
			}
			
			// Add the character to the password
			
			$password .= $char;

			// Decrement rounds
			
			$i--;

		}

		// Return the password
		
		return $password;

	}
	
	
}

// End User Class