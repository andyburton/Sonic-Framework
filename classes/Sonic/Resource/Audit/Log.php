<?php

// Define namespace

namespace Sonic\Resource\Audit;

// Start Log Class

class Log extends \Sonic\Model
{
	
	/**
	 * Class attributes
	 * @var array
	 */	
	
	protected static $attributes = array (
		'id'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> 1,
			'max'		=> self::INT_MAX_UNSIGNED,
			'default'	=> 0
		),
		'action'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> ''
		),
		'params'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> NULL,
			'null'		=> TRUE
		),
		'response'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> NULL,
			'null'		=> TRUE
		),
		'result_id'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> self::SMALLINT_MAX_UNSIGNED,
			'default'	=> 0,
			'relation'	=> 'Sonic\Model\Audit\Log\Result'
		),
		'ip'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 15,
			'default'	=> NULL,
			'null'		=> TRUE
		),
		'created_on'	=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_DATETIME,
			'charset'	=> 'datetime',
			'min'		=> 0,
			'max'		=> 19,
			'default'	=> 'CURRENT_UTC_DATE'
		),
		'created_by'	=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> ''
		),
		'user_id'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> self::INT_MIN_UNSIGNED,
			'max'		=> self::INT_MAX_UNSIGNED,
			'default'	=> NULL,
			'null'		=> TRUE
		)
	);
	
	/**
	 * Dont write changelog
	 * @var boolean 
	 */
	
	protected static $changelogIgnore	= TRUE;
	
	/**
	 * Last log entry ID
	 * @var integer 
	 */
	
	private static $_lastID	= FALSE;
	
	/**
	 * User that is making the change
	 * @var string
	 */
	
	public static $user		= 'system';
	
	/**
	 * User ID that is making the change
	 * @var integer 
	 */
	
	public static $userID	= NULL;
	
	
	/**
	 * Create a new log entry
	 * @param string $action Action requested
	 * @param integer $result_id Result ID of action
	 * @param string|array $params Action parameters
	 * @param string|array $response Response to action
	 * @param string created_by field, default to self::$user
	 * @param string $ip IP address of request
	 * @param integer $userID User ID, default to self:$userID
	 * @return boolean
	 */
	
	public static function _Log ($action, $result_id, $params = FALSE, $response = FALSE, $user = FALSE, $ip = FALSE, $userID = 0)
	{
		
		// Set user
		
		if (!$user)
		{
			$user	= static::$user;
		}
		
		// Set user id
		
		if (!$userID)
		{
			$userID	= static::$userID;
		}
		
		// Create new log object
		
		$log	= new static;
		
		// Set log variables
		
		try
		{
			
			$log->set ('action',		$action);
			$log->set ('result_id',		$result_id);
			$log->set ('created_by',	$user);
			$log->set ('user_id',		$userID);
			
			// Log Parameters
			
			if ($params)
			{
				
				// Convert array to JSON string
				
				if (is_array ($params))
				{
					$params		= json_encode ($params);
				}
				
				// Truncate if too long
				
				$maxLength	= self::_attributeProperties ('params', 'max');
				
				if ($maxLength !== FALSE && strlen ((string)$params) > $maxLength)
				{
					$params = substr ((string)$params, 0, $maxLength);
				}
				
				// Set
				
				$log->set ('params',	$params);
				
			}
			
			// Log Response
			
			if ($response)
			{
				
				// Convert array to JSON string
				
				if (is_array ($response))
				{
					$response		= json_encode ($response);
				}
				
				// Truncate if too long
				
				$maxLength	= self::_attributeProperties ('response', 'max');
				
				if ($maxLength !== FALSE && strlen ((string)$response) > $maxLength)
				{
					$response = substr ((string)$response, 0, $maxLength);
				}
				
				// Set
				
				$log->set ('response',	$response);
				
			}
			
			// Log IP
			// If an IP is specified use that otherwise
			// check for Client-IP header before using REMOTE_ADDR
			// This gets around the netscaler reverse-proxy with the additional host header
			
			if ($ip)
			{
				$log->set ('ip',		$ip);
			}
			else if (isset ($_SERVER['HTTP_CLIENT_IP']))
			{
				$log->set ('ip',		$_SERVER['HTTP_CLIENT_IP']);
			}
			else if (isset ($_SERVER['REMOTE_ADDR']))
			{
				$log->set ('ip',		$_SERVER['REMOTE_ADDR']);
			}
			
		}
		
		// Set errors as framework messages
		
		catch (\Exception $e)
		{
			new \Sonic\Message ('error', 'Error setting auditlog attributes - ' . $e->getMessage ());
			return FALSE;
		}
		
		// Begin transaction
		
		$log->db->beginTransaction ();
		
		try
		{

			// Create log entry

			if (!$log->Create ())
			{
				new \Sonic\Message ('error', $e->getMessage ());
				$log->db->rollBack ();
				return FALSE;
			}
			
		}
			
		// Set errors as framework messages
		
		catch (\Exception $e)
		{
			new \Sonic\Message ('error', 'Exception creating auditlog - ' . $e->getMessage ());
			$log->db->rollBack ();
			return FALSE;
		}
		
		// Set last ID
		
		self::$_lastID	= $log->get ('id');
		
		// Commit and return TRUE
		
		$log->db->commit ();
		return TRUE;
		
	}
	
	
	/**
	 * Remove the last log entry
	 * @return boolean
	 */
	
	public static function _rollBack ()
	{
		
		if (self::$_lastID)
		{
			
			if (!static::_delete (self::$_lastID))
			{
				return FALSE;
			}
			
			self::$_lastID	= FALSE;
			
		}
		
		return TRUE;
		
	}
	
}

// End Log Class
