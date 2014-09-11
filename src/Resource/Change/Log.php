<?php

// Define namespace

namespace Sonic\Resource\Change;

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
		'name'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> ''
		),
		'type'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_ENUM,
			'values'	=> array ('create','update','delete'),
			'default'	=> ''
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
		'object_id'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> self::INT_MIN_UNSIGNED,
			'max'		=> self::INT_MAX_UNSIGNED,
			'default'	=> 0
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
	 * @param string $type log type - create, update or delete
	 * @param object $obj1 object changed, created or deleted
	 * @param object $obj2 new object with changed values, only required for update type, must be the same object as $obj1
	 * @param array $exclude Attributes from an update that are not being updated
	 * @param string $user created_by field, default to self::$user
	 * @param integer $userID  user_id field, default to self::$userID
	 * @param string $ip IP address of request
	 * @return boolean
	 */
	
	public static function _Log ($type, $obj1, $obj2 = FALSE, $exclude = array (), $user = FALSE, $userID = 0, $ip = FALSE)
	{
		
		// Set user
		
		if (!$user)
		{
			$user	= static::$user;
		}
		
		if (!$userID)
		{
			$userID	= static::$userID;
		}
		
		// Check object is a model object
		
		if (!($obj1 instanceof \Sonic\Model))
		{
			new \Sonic\Message ('error', 'Cannot create changelog - object must extend \Sonic\Model');
			return FALSE;
		}
		
		// Create new log object
		
		$log	= new static;
		
		// Set log variables
		
		try
		{
			$log->set ('name',			get_class ($obj1));
			$log->set ('type',			$type);
			$log->set ('created_by',	$user);
			$log->set ('object_id',		$obj1->get ($obj1::$pk));
			$log->set ('user_id',		$userID);
			
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
			new \Sonic\Message ('error', 'Error setting changelog attributes - ' . $e->getMessage ());
			return FALSE;
		}
		
		// Begin transaction
		
		$log->db->beginTransaction ();
		
		// Create log entry
		
		if (!$log->Create ())
		{
			new \Sonic\Message ('error', $e->getMessage ());
			$log->db->rollBack ();
			return FALSE;
		}
		
		// Set column class to log class\Column
		
		$column_class	= get_class ($log) . '\Column';
		$oldValueAttr	= $column_class::_attributeProperties ('old_value');
		$newValueAttr	= $column_class::_attributeProperties ('new_value');
		
		// Switch log type to create columns
		
		try
		{

			switch ($type)
			{

				// Create

				case 'create':

					// Log object attributes

					foreach ($obj1->toArray () as $name => $val)
					{
						
						if (isset ($newValueAttr['max']) && strlen ((string)$val) > $newValueAttr['max'])
						{
							$val = substr ((string)$val, 0, $newValueAttr['max']);
						}
						
						$column	= new $column_class;
						$column->set ('change_log_id',	$log->get ('id'));
						$column->set ('name',			$name);
						$column->set ('new_value',		$val);
						
						if (!$column->Create ())
						{
							new \Sonic\Message ('error', 'Error creating changelog column - ' . $e->getMessage ());
							$log->db->rollBack ();
							return FALSE;
						}
						
					}

					break;


				// Update

				case 'update':
					
					// Check objects are the same class
					
					if (get_class ($obj1) != get_class ($obj2))
					{
						new \Sonic\Message ('error', 'Cannot create changelog - objects must be the same class. obj1: ' . get_class ($obj1) . ', obj2: ' . get_class ($obj2));
						$log->db->rollBack ();
						return FALSE;
					}
					
					// Get updated attributes

					$arrNew	= $obj2->toArray ();
					
					// Remove any that are excluded
					
					foreach ($exclude as $attr)
					{
						unset ($arrNew[$attr]);
					}
					
					// Log object attributes
					
					foreach ($arrNew as $name => $val)
					{
						
						$originalVal = $obj1->get ($name);
						
						if (isset ($oldValueAttr['max']) && strlen ($originalVal) > $oldValueAttr['max'])
						{
							$originalVal = substr ($originalVal, 0, $oldValueAttr['max']);
						}
						
						if (isset ($newValueAttr['max']) && strlen ($val) > $newValueAttr['max'])
						{
							$val = substr ($val, 0, $newValueAttr['max']);
						}
						
						if ($val !== $originalVal)
						{

							$column	= new $column_class;
							$column->set ('change_log_id',	$log->get ('id'));
							$column->set ('name',			$name);
							$column->set ('old_value',		$originalVal);
							$column->set ('new_value',		$val);

							if (!$column->Create ())
							{
								new \Sonic\Message ('error', 'Error creating changelog column - ' . $e->getMessage ());
								$log->db->rollBack ();
								return FALSE;
							}
							
						}
						
					}

					break;


				// Delete

				case 'delete':

					// Log object attributes

					foreach ($obj1->toArray () as $name => $val)
					{
					
						if (isset ($oldValueAttr['max']) && strlen ($val) > $oldValueAttr['max'])
						{
							$val = substr ($val, 0, $oldValueAttr['max']);
						}
						
						$column	= new $column_class;
						$column->set ('change_log_id',	$log->get ('id'));
						$column->set ('name',			$name);
						$column->set ('old_value',		$val);
						
						if (!$column->Create ())
						{
							new \Sonic\Message ('error', 'Error creating changelog column - ' . $e->getMessage ());
							$log->db->rollBack ();
							return FALSE;
						}
						
					}

					break;


				// Catch anything else

				default:

					// Error

					new \Sonic\Message ('error', 'Cannot create changelog - invalid log type: ' . $type);
					$log->db->rollBack ();
					return FALSE;
					break;

			}
			
		}
			
		// Set errors as framework messages
		
		catch (\Exception $e)
		{
			new \Sonic\Message ('error', 'Exception creating changelog column - ' . $e->getMessage ());
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
