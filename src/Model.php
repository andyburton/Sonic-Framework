<?php

// Define namespace

namespace Sonic;

// Start Model Class

class Model
{
	
	
	/**
	 * Variable type constants
	 */
	
	const TYPE_INT				= 1;
	const TYPE_STRING			= 2;
	const TYPE_BOOL				= 3;
	const TYPE_DATE				= 4;
	const TYPE_DATETIME			= 5;
	const TYPE_DECIMAL			= 6;
	const TYPE_ENUM				= 7;
	const TYPE_BINARY			= 8;
	
	/**
	 * Min/Max type values
	 */
	
	const TINYINT_MIN				= -128;
	const TINYINT_MAX				= 127;
	const TINYINT_MIN_UNSIGNED		= 0;
	const TINYINT_MAX_UNSIGNED		= 255;
	
	const SMALLINT_MIN				= -32768;
	const SMALLINT_MAX				= 32767;
	const SMALLINT_MIN_UNSIGNED		= 0;
	const SMALLINT_MAX_UNSIGNED		= 65535;
	
	const MEDIUMINT_MIN				= -8388608;
	const MEDIUMINT_MAX				= 8388607;
	const MEDIUMINT_MIN_UNSIGNED	= 0;
	const MEDIUMINT_MAX_UNSIGNED	= 16777215;
	
	const INT_MIN					= -2147483648;
	const INT_MAX					= 2147483647;
	const INT_MIN_UNSIGNED			= 0;
	const INT_MAX_UNSIGNED			= 4294967295;
	
	const BIGINT_MIN				= -9223372036854775808;
	const BIGINT_MAX				= 9223372036854775807;
	const BIGINT_MIN_UNSIGNED		= 0;
	const BIGINT_MAX_UNSIGNED		= 18446744073709551615;
	
	
	/**
	 * Model database table name
	 * @var string
	 */
	
	public static $dbTable			= FALSE;
	
	/**
	 * Model primary attribute key
	 * @var string
	 */
	
	public static $pk				= 'id';
	
	/**
	 * Model attribute data
	 * 
	 * This is a multi-dimensional array containing all object variables and any properties for them.
	 * The attribute key is the variable name and database column name (if required).
	 * 
	 * Available attribute properties - 
	 * 
	 *   name - string, friendly name for the attribute, used in validation. Default to the attribute name.
	 *   get - boolean, whether the attribute getter is allowed. Default FALSE.
	 *   set - boolean, whether the attribute setter is allowed. Default FALSE.
	 *   reset - boolean, whether the attribute resetter is allowed. Default TRUE.
	 *   type - int, the data type, use the self::TYPE_ constants. 
	 *   charset - string, the attribute character set to validate against. See Parser::CHARSET_ constants.
	 *     Only allows characters from the given charset e.g. 'default' = CHARSET_DEFAULT). Will cast value to a string.
	 *   valid - string|array, any validation functions the attribute value must meet. See Parser class functions beginning with validate.
	 *     Current validation functions are - email, date, datetime
	 *   values - string|array, single value or array of values that are only allowed for the attribute value.
	 *   min - int, the minimum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   max - int, the maximum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   null - boolean, whether the value can be NULL.
	 *   default - mixed, the default attribute value if none is set.
	 *   relation - string, the related class from this attribute. Must be the full namespace path e.g. 'Sonic\Model\User'
	 * 
	 * Example -
	 * 
	 * array (
	 *   'id' => array (
	 *     'get'      => TRUE,
	 *     'set'      => TRUE,
	 *     'type'     => self::TYPE_INT_UNSIGNED,
	 *     'charset'  => 'int_unsigned',
	 *     'min'      => self::MEDIUMINT_MIN_UNSIGNED,
	 *     'max'      => self::MEDIUMINT_MAX_UNSIGNED
	 *   ),
	 *   'group_id' => array (
	 *     'get'      => TRUE,
	 *     'set'      => TRUE,
	 *     'type'     => self::TYPE_INT_UNSIGNED,
	 *     'charset'  => 'int_unsigned',
	 *     'min'      => self::MEDIUMINT_MIN_UNSIGNED,
	 *     'max'      => self::MEDIUMINT_MAX_UNSIGNED,
	 *     'relation' => 'Sonic\Model\Group'
	 *   ),
	 *   'email' => array (
	 *     'name'     => 'Email Address',
	 *     'get'      => TRUE,
	 *     'set'      => TRUE,
	 *     'type'     => self::TYPE_STRING,
	 *     'charset'  => 'email',
	 *     'valid'    => 'email',
	 *     'min'      => 1,
	 *     'max'      => 255,
	 *     'null'     => TRUE
	 *   ),
	 *   'state' => array (
	 *     'get'      => TRUE,
	 *     'set'      => TRUE,
	 *     'type'     => self::TYPE_ENUM,
	 *     'values'   => array ('0', '1'),
	 *     'default'  => '0'
	 *   )
	 * );
	 * 
	 * @var array
	 */
	
	protected static $attributes	= array ();
	
	/**
	 * Model attribute values
	 * @var array
	 */
	
	protected $attributeValues		= array ();
	
	/**
	 * Model resources
	 * @var array
	 */

	protected $resources			= array ();
	
	/**
	 * Default class resources
	 *   e.g array ('db' => array ('db', 'backup'))
	 * @var array
	 */

	protected static $defaultResources	= array ();
	
	/**
	 * Model children
	 * @var array
	 */
	
	public $children				= array ();
	
	/**
	 * Model db resource
	 * @var PDO
	 */

	public $db						= FALSE;

	/**
	 * Model parser resource
	 * @var Parser
	 */

	public $parser					= FALSE;
	
	/**
	 * Debug mode
	 * @var boolean 
	 */
	
	public $debug					= FALSE; 
	
	
	/**
	 * Instantiate class
	 * @return void
	 */
	
	public function __construct ()
	{
		
		// Set debug mode
		
		$this->debug		= defined ('DEBUG')? DEBUG : $this->debug;
		
		// Set default resources

		$this->resources	= Sonic::getSelectedResources ();
		
		// If the object variable exists and isnt set then assign it
		
		foreach (array_keys ($this->resources) as $name)
		{
			if (isset ($this->$name) && $this->$name === FALSE)
			{
				$this->$name	=& $this->resources[$name];
			}
		}
		
		// If there are any class defaults set them
		
		foreach (static::$defaultResources as $name => $resource)
		{
			if (!$this->setResource ($name, $resource))
			{
				throw new Exception ('Framework resource `' . print_r ($resource, TRUE) . '` does not exist for ' . get_called_class ());
			}
		}
		
	}


	/**
	* Called when the object is serialized
	* @return array
	*/

	public function __sleep ()
	{

		// Return object variables

		$vars	= get_object_vars ($this);

		// Remove framework variables

		unset ($vars['resources']);
		
		// Remove object framework resources

		foreach (array_keys ($this->resources) as $resource)
		{
			
			// If the object variable exists

			if (isset ($vars[$resource]))
			{
				
				// Remove

				unset ($vars[$resource]);

			}

		}

		// Return names

		return array_keys ($vars);

	}


	/**
	* Called when the object is unserialized
	* @return void
	*/

	public function __wakeup ()
	{

		// Call object construct method

		$this->__construct ();

	}
	
	
	/**
	 * Pipe any unassigned get request through the attribute getter
	 * @param string $name Variable name
	 * @return mixed
	 */
	
	public function __get ($name)
	{
		return $this->get ($name);
	}
	
	
	/**
	 * Pipe any unassigned set request through the attribute setter
	 * @param string $name Variable name
	 * @param mixed $val Variable value
	 * @return void
	 */
	
	public function __set ($name, $val)
	{
		
		// If the attribute doesn't exists then create new object property	
	
		if (!$this->attributeExists ($name))
		{
			$this->$name = $val;
		}
		else
		{
			$this->set ($name, $val);
		}
		
	}
	
	
	/**
	 * Return an attribute value if allowed by attribute getter
	 * @param string $name Attribute name
	 * @throws Exception
	 * @return mixed
	 */
	
	public function get ($name)
	{
		
		// If the attribute doesn't exists
		
		if (!$this->attributeExists ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute does not exist!');
		}
		
		// If attribute getter isn't set or is disabled
		
		if (!$this->attributeGet ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute get is disabled!');
		}
		
		// If no attribute value is set
		
		if (!$this->attributeHasValue ($name))
		{
			
			// If there is a default set to value
			
			if (array_key_exists ('default', static::$attributes[$name]))
			{
				$this->iset ($name, static::$attributes[$name]['default'], FALSE);
			}
			
			// Else there is no value and no default
			
			else
			{
				throw new Exception (get_called_class () . '->' . $name . ' attribute isn\'t set and has no default value!');
			}
			
		}
		
		// Return value
		
		return $this->attributeValues[$name];
			
	}
	
	
	/**
	 * Set an attribute value if allowed by attribute setter
	 * @param string $name Attribute name
	 * @param mixed $val Attribute value
	 * @param boolean $validate Whether to validate the value
	 * @throws Exception|Parser\Exception
	 * @return void
	 */
	
	public function set ($name, $val, $validate = TRUE)
	{
		
		// If the attribute doesn't exists
		
		if (!$this->attributeExists ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute does not exist!');
		}
		
		// If attribute setter isn't set or is disabled
		
		if (!$this->attributeSet ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute set is disabled!');
		}
		
		// If we're validating the value
		
		if ($validate)
		{
			
			// Set friendly name if one exists
			
			$friendlyName	= isset (static::$attributes[$name]['name'])? static::$attributes[$name]['name'] : $name;
			
			// Validate it using the parser method
			
			$this->parser->Validate ($friendlyName, static::$attributes[$name], $val);
			
		}
		
		// Set the attribute value
		
		$this->attributeValues[$name]	= $val;
		
	}

	
	/**
	 * Return an attribute value (internal)
	 * @param string $name Attribute name
	 * @throws Exception
	 * @return mixed
	 */
	
	protected function iget ($name)
	{
		
		// If the attribute doesn't exists
		
		if (!$this->attributeExists ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute does not exist!');
		}
		
		// If no attribute value is set
		
		if (!$this->attributeHasValue ($name))
		{
			
			// If there is a default set to value
			
			if (array_key_exists ('default', static::$attributes[$name]))
			{
				$this->iset ($name, static::$attributes[$name]['default'], FALSE);
			}
			
			// Else there is no value and no default
			
			else
			{
				throw new Exception (get_called_class () . '->' . $name . ' attribute isn\'t set and has no default value!');
			}
			
		}
		
		// Return value
		
		return $this->attributeValues[$name];
			
	}

	
	/**
	 * Set an attribute value (internal)
	 * @param string $name Attribute name
	 * @param mixed $val Attribute value
	 * @param boolean $validate Whether to validate the value
	 * @param boolean $cast Whether to cast the value to the attribute datatype
	 * @throws Exception|Parser\Exception
	 * @return void
	 */
	
	protected function iset ($name, $val, $validate = TRUE, $cast = FALSE)
	{
		
		// If the attribute doesn't exists
		
		if (!$this->attributeExists ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute does not exist!');
		}
		
		// If we're validating the value
		
		if ($validate)
		{
			
			// Set friendly name if one exists
			
			$friendlyName	= isset (static::$attributes[$name]['name'])? static::$attributes[$name]['name'] : $name;
			
			// Validate it using the parser method
			
			$this->parser->Validate ($friendlyName, static::$attributes[$name], $val);
			
		}
		
		// Cast the value
		
		if ($cast)
		{
			$val	= $this->cast ($name, $val);
		}
		
		// Set the attribute value
		
		$this->attributeValues[$name]	= $val;
		
	}

	
	/**
	 * Reset an attribute value to its default
	 * @param string $name Attribute name
	 * @throws Exception
	 * @return void
	 */
	
	public function reset ($name)
	{
		
		// If the attribute doesn't exists
		
		if (!$this->attributeExists ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute does not exist!');
		}
		
		// If attribute reset is disabled
		
		if (!$this->attributeReset ($name))
		{
			throw new Exception (get_called_class () . '->' . $name . ' attribute reset is disabled!');
		}
		
		// If there is no default attribute value remove the value
		
		if (!array_key_exists ('default', static::$attributes[$name]))
		{
			unset ($this->attributeValues[$name]);
		}
		
		// Else there is a default attribute value so set it
		
		else
		{
			$this->iset ($name, static::$attributes[$name]['default'], FALSE);
		}
		
	}
	
	
	/**
	 * Reset the primary key to default or set to a specific value
	 * @param mixed $val Value
	 */
	
	public function resetPK ($val = FALSE)
	{
		
		if ($val === FALSE)
		{
			$this->reset (static::$pk);
		}
		else
		{
			$this->attributeValues[static::$pk] = $val;
		}
		
	}
	
	
	/**
	 * Cast a value for an attribute datatype 
	 * @param string $name Attribute name
	 * @param mixed $val Value to cast
	 * @return mixed
	 */
	
	public function cast ($name, $val)
	{
		
		// Ignore if value is null
		
		if (is_null ($val))
		{
			return $val;
		}
		
		// Cast value based upon attribute datatype
		
		switch (@static::$attributes[$name]['type'])
		{
			
			case self::TYPE_INT:
				$val	= (int)$val;
				break;
			
			case self::TYPE_STRING:
				$val	= (string)$val;
				break;
			
			case self::TYPE_BOOL:
				$val	= (bool)$val;
				break;
			
			case self::TYPE_DECIMAL:
				$val	= (float)$val;
				break;
			
			case self::TYPE_BINARY:
				$val	= (binary)$val;
				break;
			
		}
		
		// Return casted value
		
		return $val;
		
	}
	
	
	/**
	 * Create object in the database
	 * @param array $exclude Attributes not to set
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function create ($exclude = array (), &$db = FALSE)
	{
		
		// Get columns and values
		
		$create	= $this->createColumnsAndValues ($exclude);
		
		// Get database master for write
		
		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// Prepare query
		
		$query	= $db->prepare ('
		INSERT INTO `' . static::$dbTable . '` (' . $create['columns'] . ')
		VALUES ( ' . $create['values'] . ')
		');
		
		// Bind attributes
		
		$this->bindAttributes ($query, $exclude);
		
		// Execute
		
		if (!$this->executeCreateUpdateQuery ($query))
		{
			return FALSE;
		}
		
		// Set the pk

		if (!$this->attributeHasValue (static::$pk) || !$this->iget (static::$pk))
		{
			$this->iset (static::$pk, $db->lastInsertID ());
		}
		
		// Changelog
		
		if ($this->changelog ('create'))
		{
			$log =& $this->getResource ('changelog');
			$log->hookTransactions ($db);
			$log::_Log ('create', static::_Read ($this->iget (static::$pk), $db));
		}

		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Create or update an object in the database if it already exists
	 * @param array $update Values to update
	 * @param array $exclude Attributes not to set in create
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function createOrUpdate ($update, $exclude = array (), &$db = FALSE)
	{
		
		// Get columns and values
		
		$create		= $this->createColumnsAndValues ($exclude);
		
		// Generate update values
		
		$updateVals	= NULL;
		
		foreach ($update as $column => $value)
		{
			
			// Add column
			
			$updateVals	.= ', `' . $column . '` = ';
			
			// If the updated value is an array treat first item
			// as literal query insert and the second a params to bind
			
			$updateVals	.= is_array ($value)? $value[0] : $value;
			
		}
		
		// Trim the first character (,) from the update
		
		$updateVals	= substr ($updateVals, 2);
		
		// Get database master for write
		
		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// Prepare query
		
		$query	= $db->prepare ('
		INSERT INTO `' . static::$dbTable . '` (' . $create['columns'] . ')
		VALUES ( ' . $create['values'] . ') 
		ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(' . static::$pk . '), ' . $updateVals . '
		');
		
		// Bind attributes
		
		$this->bindAttributes ($query, $exclude);
		
		// Bind update parameters
		
		foreach ($update as $value)
		{
			if (is_array ($value) && isset ($value[1]) && is_array ($value[1]))
			{
				foreach ($value[1] as $column => $newVal)
				{
					$query->bindValue ($column, $newVal);
				}
			}
		}
		
		// Execute
		
		if (!$this->executeCreateUpdateQuery ($query))
		{
			return FALSE;
		}
		
		// Set the pk

		if (!$this->attributeHasValue (static::$pk) || !$this->iget (static::$pk))
		{
			$this->iset (static::$pk, $db->lastInsertID ());
		}
		
		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Generate create query columns and values
	 * @param array $exclude Attribute exclusion array
	 * @return array ('columns', 'values')
	 */
	
	private function createColumnsAndValues (&$exclude)
	{
		
		// If there is no primary key value exclude it (i.e assume auto increment)
		
		if (!$this->attributeHasValue (static::$pk))
		{
			$exclude[]	= static::$pk;
		}
		
		// Set column and value variables

		$columns	= NULL;
		$values		= NULL;
		
		// Loop through attributes
		
		foreach (static::$attributes as $name => $attribute)
		{
			
			// If we're excluding the attribute then move on
			
			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// If the attribute is not set
			
			if (!$this->attributeIsset ($name))
			{
				
				// If there is a default set it
				
				if (array_key_exists ('default', $attribute))
				{
					$this->iset ($name, $attribute['default'], FALSE);
				}
				
				// Else if the attribute can be set to NULL then do so
				
				else if (isset ($attribute['null']) && $attribute['null'])
				{
					$this->iset ($name, NULL, FALSE);
				}
				
				// Else set a blank value
				
				else
				{
					$this->iset ($name, '', FALSE);
				}
				
			}
			
			// Add the column and values
			
			$columns	.= ', `' . $name . '`';
			$values		.= ', ' . $this->transformValue ($name, $attribute, $exclude);
			
		}
		
		// Trim the first character (,) from the column and values
		
		$columns	= substr ($columns, 2);
		$values		= substr ($values, 2);
		
		// Return columns and values
		
		return array (
			'columns'	=> $columns,
			'values'	=> $values
		);
		
	}
	
	
	/**
	 * Apply any attribute value transformations for SQL query
	 * @param string $name Attribute name
	 * @param array $attribute Attribute property array
	 * @param array $exclude Attribute exclusion array
	 * @return mixed
	 */
	
	private function transformValue ($name, $attribute, &$exclude)
	{
		
		// If an attribute can accept NULL and it's value is '' then NULL will be set

		$value	= $this->iget ($name);

		if (is_null ($value) || (isset ($attribute['null']) && $attribute['null'] && $value === ''))
		{
			$this->iset ($name, NULL);
			$value		= 'NULL';
			$exclude[]	= $name;
		}
		else
		{
			
			// Switch special values
			
			switch ((string)$value)
			{

				case "CURRENT_TIMESTAMP":

					$value		= 'CURRENT_TIMESTAMP';
					$exclude[]	= $name;
					break;

				case 'CURRENT_UTC_DATE':

					$value		= "'" . $this->parser->utcDate () . "'";
					$exclude[]	= $name;
					break;

				default:

					$value	= ':' . $name;
					break;

			}

		}
		
		// Return transformed value
		
		return $value;
		
	}
	
	
	/**
	 * Bind object attribute values to the query
	 * @param \PDOStatement $query Query object to bind values to
	 * @param array $exclude Attributes to exclude
	 * @return void
	 */
	
	private function bindAttributes (&$query, $exclude = array ())
	{
		
		// Loop through attributes
		
		foreach (array_keys (static::$attributes) as $name)
		{
			
			// If we're excluding the attribute then move on
			
			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// Bind paramater
			
			$query->bindValue (':' . $name, $this->iget ($name));
			
		}
		
	}
	
	
	/**
	 * Execute create or update query and cope with an exception
	 * @param \PDOStatement $query Query object to bind values to
	 * @return boolean
	 * @throws PDOException 
	 */
	
	private function executeCreateUpdateQuery (&$query)
	{
		
		// Execute

		try
		{
			$query->execute ();
			return TRUE;
		}
		catch (\PDOException $e)
		{
			
			// Catch some errors
			
			switch ($e->getCode ())
			{
				
				// Duplicate Key
				
				case 23000:
				
					// Get attribute name and set error

					if (preg_match ('/Duplicate entry \'(.*?)\' for key \'(.*?)\'/', $e->getMessage (), $match))
					{
						$name	= $this->attributeExists ($match[2]) && isset (static::$attributes[$match[2]]['name'])? static::$attributes[$match[2]]['name'] : $match[2];
						new Message ('error',  'Please choose another ' . ucwords ($name) . ' `' . $match[1] . '` already exists!');
						return FALSE;
					}

					// Else unrecognised message so throw the error again

					else
					{
						throw $e;
					}
					
					break;
					
					
				// Throw error by default
					
				default:
					throw $e;
				
			}
			
		}
		
	}
	
	
	/**
	 * Read an object from the database, populating the object attributes
	 * @param mixed $pkValue Primary key value
	 * @param \PDO $db Database connection to use, default to slave resource
	 * @return boolean
	 */
	
	public function read ($pkValue = FALSE, &$db = FALSE)
	{
		
		try
		{
			
			// If there is a key value passed set it

			if ($pkValue !== FALSE)
			{
				$this->iset (static::$pk, $pkValue);
			}
			
			// Get database slave for read

			if ($db === FALSE)
			{
				$db	=& $this->getDbSlave ();
			}

			// Prepare query

			$query = $db->prepare ('
			SELECT * FROM `' . static::$dbTable . '`
			WHERE ' . static::$pk . ' = :pk
			');

			// Bind paramater

			$query->bindValue (':pk',	$this->iget (static::$pk));

			// Execute

			$query->execute ();

			// Set row

			$row	= $query->fetch (\PDO::FETCH_ASSOC);

			// If no data was returned return FALSE

			if (!$row)
			{
				return FALSE;
			}

			// Set each attribute value

			foreach ($row as $name => $val)
			{

				if ($this->attributeExists ($name))
				{
					$this->iset ($name, $val, FALSE, TRUE);
				}

			}
			
		}
		
		// Set errors as framework messages

		catch (Resource\Parser\Exception $e)
		{
			new Message ('error', $e->getMessage ());
			return FALSE;
		}
		
		// Return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Read and set a single object attribute from the database
	 * @param string $name Attribute name
	 * @return void
	 */
	
	public function readAttribute ($name)
	{
		$this->iset ($name, $this->getValue (array (
			'select'	=> $name,
			'where'		=> array (array (static::$pk, $this->iget (static::$pk)))
		)));
	}
	
	
	/**
	 * Update an object in the database
	 * @param array $exclude Attributes not to update
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function update ($exclude = array (), &$db = FALSE)
	{
		
		// Get database master for write

		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// Changelog
		
		$old = FALSE;
		
		if ($this->changelog ('update'))
		{
			$old = static::_Read ($this->get (static::$pk), $db);
		}
		
		// Exclude the primary key
		
		$exclude[]	= static::$pk;
		
		// Set value variable

		$values		= NULL;
		
		// Loop through attributes
		
		foreach (static::$attributes as $name => $attribute)
		{
			
			// If we're excluding the attribute then move on

			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// If the attribute has derefresh enabled the refresh value to default for the update
			
			if (isset ($attribute['deupdate']) && $attribute['deupdate'])
			{
				$this->reset ($name);
			}
			
			// Else if the attribute is not set or is creation only
			
			else if (!$this->attributeIsset ($name) || 
				(isset ($attribute['creation']) && $attribute['creation']))
			{
				
				// Exclude it and move on
				
				$exclude[]	= $name;
				continue;
				
			}
			
			// Add the value
			
			$values	.= ',`' . $name . '` = ' . $this->transformValue ($name, $attribute, $exclude);
			
		}
		
		// Trim the first character (,) from the values
		
		$values		= substr ($values, 1);
		
		// Prepare query
		
		$query = $db->prepare ('
		UPDATE `' . static::$dbTable . '`
		SET ' . $values . '
		WHERE ' . static::$pk . ' = :pk
		');
		
		// Bind attributes
		
		$this->bindAttributes ($query, $exclude);
		
		// Bind pk
		
		$query->bindValue (':pk', $this->iget (static::$pk));
		
		// Execute
		
		if (!$this->executeCreateUpdateQuery ($query))
		{
			return FALSE;
		}
		
		// Changelog
		
		if ($old)
		{
			$log	=& $this->getResource ('changelog');
			$log->hookTransactions ($db);
			$log::_Log ('update', $old, $this);
		}

		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Set and update a single object attribute in the database
	 * @param string $name Attribute name
	 * @param string $value Attribute value
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function updateAttribute ($name, $value, &$db = FALSE)
	{
		
		// Get database master for write

		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// Changelog
		
		$old = FALSE;
		
		if ($this->changelog ('update'))
		{
			$old = static::_Read ($this->iget (static::$pk), $db);
		}
		
		$this->iset ($name, $value);

        // Prepare query

        $exclude = [];

        $query = $db->prepare ('
		UPDATE `' . static::$dbTable . '`
		SET ' . $name . ' = ' . $this->transformValue ($name, $value, $exclude) . '
		WHERE ' . static::$pk . ' = :pk
		');

        // Bind values

        if (!in_array($name, $exclude)) {
            $query->bindValue (':' . $name,	$this->iget ($name));
        }
        $query->bindValue (':pk',	$this->iget (static::$pk));

        // Execute

		if (!$query->execute ())
		{
			return FALSE;
		}
		
		// Changelog
		
		if ($old)
		{
			
			// Only update attribute thats changed
			
			$exclude = $this->toArray ();
			unset ($exclude[$name]);
			
			$log =& $this->getResource ('changelog');
			$log->hookTransactions ($db);
			$log::_Log ('update', $old, $this, array_keys ($exclude));
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Set and update a single object attribute in the database
	 * @param integer $pk Primary key
	 * @param string $name Attribute name
	 * @param string $value Attribute value
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public static function _setValue ($pk, $name, $value, &$db = FALSE)
	{
		
		// Get database master for write

		if ($db === FALSE)
		{
			$db	=& self::_getDbMaster ();
		}
		
		if (!($db instanceof \PDO))
		{
			throw new Exception ('Invalid or no database resource set');
		}
		
		// Prepare query
		
		$query = $db->prepare ('
		UPDATE `' . static::$dbTable . '`
		SET ' . $name . ' = :val 
		WHERE ' . static::$pk . ' = :pk
		');
		
		// Bind values
		
		$query->bindValue (':val',	$value);
		$query->bindValue (':pk',	$pk);
		
		// Execute

		return $query->execute ();
		
	}
	
	
	/**
	 * Delete an object in the database
	 * @param array|integer $params Primary key value or parameter array
	 * @param \PDO $db Database connection to use, default to master resource
	 * @return boolean
	 */
	
	public function delete ($params = FALSE, &$db = FALSE)
	{
		
		// Get database master for write

		if ($db === FALSE)
		{
			$db	=& $this->getDbMaster ();
		}
		
		// If there is no key value passed set to the object id
		
		if ($params === FALSE)
		{
			$params	= array (
				'where' => array (
					array (static::$pk, $this->iget (static::$pk))
				)
			);
		}
		
		// If the params are not an array assume the variable
		// is an object pk, and set the parameter array
		
		if (!is_array ($params))
		{
			$params	= array (
				'where' => array (
					array (static::$pk, $params)
				)
			);
		}
		
		// If there is no where clause
		
		if (!isset ($params['where']))
		{
			throw new Exception ('No where clause for ' . get_called_class () . '->delete');
		}
		
		// Changelog
		
		$arrRemove	= FALSE;
		
		// Get all objects being removed
		
		if ($this->changelog ('delete'))
		{
			$arrRemove	= static::_getObjects (array ('where' => $params['where']), FALSE, $db);
		}
		
		// Prepare query
		
		$query = $db->prepare ('
			DELETE FROM `' . static::$dbTable . '` 
			' . $db->genClause ('WHERE', $db->genWHERE ($params['where']))
		);
		
		// Bind values
		
		$db->genBindValues ($query, $params['where']);
		
		// Execute

		$query->execute ();
		
		// Changelog
		
		if ($arrRemove)
		{
			
			$log =& $this->getResource ('changelog');
			$log->hookTransactions ($db);
			
			// Log all removed entries
			
			foreach ($arrRemove as $obj)
			{
				$log::_Log ('delete', $obj);
			}
			
		}

		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Hook any database queries into another database transaction
	 * so that the queries are commited and rolled back at the same point
	 * @param \Sonic\Resource\Db $parent Parent database
	 *   This is the database resource that is hooked and passes transaction state to the child database
	 * @param \Sonic\Resource\Db $child Child database
	 *   This is the datababase resource that hooks onto the parent and has its transaction state copied from the parent
	 *   Defaults to object 'db' connection resource
	 */
	
	public function hookTransactions (Resource\Db &$parent, Resource\Db &$child = NULL)
	{
		
		// Use default object database resource if none is specified
		
		if (!$child)
		{
			$child	=& $this->getResource ('db');
		}
		
		/**
		 * We only want to create the hook and begin a transaction on the database
		 * if a transaction has already begun on the database we're hooking onto.
		 */
		
		if ($parent->getTransactionCount () > 0 && $parent->addTransactionHook ($child))
		{
			$child->beginTransaction ();
		}
		
	}
	
	
	/**
	 * Return a DOM tree with object attributes
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @return \DOMDocument|boolean
	 */
	
	public function toXML ($attributes = FALSE)
	{
		
		// Set class name for the elements
		// Remove the Sonic\Model prefix and convert namespace \ to _
		
		$class	= str_replace ('\\', '_', str_replace ('sonic\\model\\', '', strtolower (get_called_class ())));
		
		// Create DOMDocument

		$doc	= new \DOMDocument ('1.0', 'UTF-8');
		
		// Create root node
		
		$node	= $doc->createElement ($class);
		$doc->appendChild ($node);
		
		// Get array
		
		$arr	= $this->toArray ($attributes);
		
		// Set each attribute

		foreach ($arr as $name => $val)
		{
			$node->appendChild ($doc->createElement (strtolower ($name), htmlentities ($val)));
		}
		
		// Return doc
		
		return $doc;
		
	}
	
	
	/**
	 * Return a JSON encoded string with object attributes
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param boolean $addClass Whether to add the class name to each exported object
	 * @return object|boolean
	 */
	
	public function toJSON ($attributes = FALSE, $addClass = FALSE)
	{
		
		// Get array
		
		$arr	= $this->toArray ($attributes);
		
		// Add the class name if required
		
		if ($addClass)
		{
			$arr['class']	= str_replace ('\\', '_', str_replace ('sonic\\model\\', '', strtolower (get_called_class ())));
		}
		
		// Return json encoded
		
		return json_encode ($arr);
		
	}
	
	
	/**
	 * Return an array with object attributes
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param array $relations Array of related object attributes or tranformed method attributes to return
	 *   e.g. related value - 'key' => array ('\Sonic\Model\User\Group', 'name')
	 *   e.g. related object - 'key' => array ('related', '\Sonic\Model\User\Group', array (toArray params))
	 *   e.g. related children - 'key' => array ('children', '\Sonic\Model\User\Group', array (toArray params))
	 *   e.g. object tranformed value - 'key' => array ('$this', 'getStringValue', array (args))
	 *   e.g. class tranformed value - 'key' => array ('self', '_getStringValue', array (args))
	 *   e.g. static tranformed value - 'key' => array ('static', '_getStringValue', array (args))
	 *   e.g. parent tranformed value - 'key' => array ('parent', '_getStringValue', array (args))
	 * @param integer $recursive Output array recursively, so any $this->children also get output
	 * @return object|boolean
	 */
	
	public function toArray ($attributes = FALSE, $relations = array (), $recursive = FALSE)
	{
		
		// If no attributes are set to display, get all object attributes with get allowed
		
		if ($attributes === FALSE)
		{
			
			$attributes	= array ();
			
			foreach (array_keys (static::$attributes) as $name)
			{
				
				if ($this->attributeGet ($name))
				{
					$attributes[]	= $name;
				}
				
			}
			
		}
		
		// Set array
		
		$arr	= array ();

		// Set each attribute

		foreach ($attributes as $name)
		{
			
			try
			{
				$arr[$name]	= $this->get ($name);
			}
			catch (Exception $e)
			{
				$arr[$name]	= NULL;
			}
			
		}
		
		// Get any related attributes
		
		if ($relations)
		{
			
			foreach ($relations as $name => $relation)
			{
				
				// Remove first \ from class

				if ($relation[0][0] == '\\')
				{
					$relation[0]	= substr ($relation[0], 1);
				}
				
				// If the first value exists as a class name
				// Get object and attribute value
				
				if (class_exists ($relation[0]))
				{
					$obj		= $this->getRelated ($relation[0]);
					$arr[$name]	= $obj? $obj->get ($relation[1]) : '';
				}
				
				// Else switch first item of relation array
				
				else
				{

					switch ($relation[0])
					{

						// Object method

						case '$this':

							$args		= isset ($relation[2])? $relation[2] : array ();
							$arr[$name]	= call_user_func_array (array ($this, $relation[1]), $args);

							break;

						// Related object

						case 'related':

							$args		= isset ($relation[2])? $relation[2] : FALSE;
							$obj		= $this->getRelated ($relation[1]);
							$arr[$name]	= $obj? $obj->toArray ($args) : '';

							break;

						// Child objects

						case 'children':

							$args		= isset ($relation[2])? $relation[2] : FALSE;
							$obj		= isset ($relation[3])? $this->getChildren ($relation[1], FALSE, FALSE, $relation[3]) : $this->getChildren ($relation[1]);
							$arr[$name]	= $obj? $obj->toArray ($args) : '';

							break;

						// Anything else
						// Pass directly to call_user_func_array

						default:

							$args		= isset ($relation[2])? $relation[2] : array ();
							$arr[$name]	= call_user_func_array (array ($relation[0], $relation[1]), $args);

							break;

					}
					
				}
				
			}
			
		}
		
		// Output recursively
		
		if ($recursive)
		{
			
			if (isset ($this->children))
			{
				$arr['children']	= $this->children->toArray ($attributes, $relations, $recursive);
			}
			else
			{
				$arr['children']	= array ();
			}
			
		}
		
		// Return array
		
		return $arr;
		
	}

	
	/**
	 * Populate object attributes from a post array
	 * Attribute names need to be in the format ClassName_AttributeName in $_POST
	 * @param boolean $validate Validate attributes during set
	 * @param array $required Required attributes
	 * @param array $valid Valid attributes to set
	 * @return void
	 */
	
	public function fromPost ($validate = TRUE, $required = array (), $valid = array ())
	{
		
		// By default allow all attributes except pk
		
		if (!$valid)
		{
			$valid	= array_keys (static::$attributes);
			if (($key = array_search (static::$pk, $valid)) !== FALSE)
			{
				unset ($valid[$key]);
			}
		}
		
		$this->fromArray ($_POST, TRUE, $validate, $required, $valid);
		
	}
	
	
	/**
	 * Populate object attributes from an array
	 * @param array $attributes Attributes array
	 * @param boolean $removeClass Remove class prefix from the attribute name
	 * @param boolean $validate Validate attributes during set
	 * @param array $required Required attributes
	 * @param array $valid Valid attributes to set
	 * @return void
	 */
	
	public function fromArray ($attributes, $removeClass = FALSE, $validate = TRUE, $required = array (), $valid = array ())
	{
		
		// Remove class prefix
		
		if ($removeClass)
		{
			
			// Set attributes to set

			$arr	= array ();

			// Get class name

			$class	= strtolower ($this->getClass ());

			// Loop through attributes and add any that exist with the class name

			foreach (array_keys (static::$attributes) as $name)
			{

				if (isset ($attributes[$class . '_' . strtolower ($name)]))
				{
					$arr[$name]	= $attributes[$class . '_' . $name];
				}

			}
			
			// Set attributes
			
			$attributes	= $arr;
			
		}
		
		// If we have an array of valid attributes to set
		// Remove any that arent valid
		
		if ($valid)
		{
			
			foreach (array_keys ($attributes) as $name)
			{
				
				if (!in_array ($name, $valid, TRUE))
				{
					unset ($attributes[$name]);
				}
				
			}
			
		}
		
		// Set each attribute
		
		try
		{
			
			foreach ($attributes as $name => $val)
			{
				
				if ($this->attributeExists ($name) && $this->attributeSet ($name))
				{
					$this->set ($name, $val, $validate);
				}
				
				if (($key = array_search ($name, $required, TRUE)) !== FALSE)
				{
					unset ($required[$key]);
				}
				
			}
			
			// Error if there are required fields that have not been set
			
			if (count ($required) > 0)
			{
				
				foreach ($required as $name)
				{
					
					if ($this->attributeExists ($name) && isset (static::$attributes[$name]['name']))
					{
						$name	= static::$attributes[$name]['name'];
					}
					
					$word	= in_array (strtoupper ($name[0]), array ('A', 'E', 'I', 'O'))? 'an' : 'a';
					
					new Message ('error', 'You have not entered ' . $word . ' ' . ucwords ($name));
					
				}
				
			}
			
		}
		
		// Set errors as framework messages
		
		catch (Resource\Parser\Exception $e)
		{
			new Message ('error', $e->getMessage ());
		}
		
	}
	
	
	/**
	 * Return the specified object that is related to the current object
	 * e.g. the following would return a Sonic\Model\C object that is indirectly linked to the Sonic\Model\A class though Sonic\Model\B
	 *   $c = $a->getRelated ('Sonic\Model\C');
	 * @param string $class The object type to return
	 * @param array $fork Can be used to set which attribute to use when you have 
	 *   multiple attributes in the same object related to the same class.
	 *   e.g. If Sonic\Model\B had attributes c1 and c2, both related to Sonic\Model\C, the example above would use the first by default.
	 *   To specifically set c2 you would pass $fork = array ('Sonic\Model\B' => 'c2');
	 *   Multiple classes and attributes can be listed in fork to decide which attribute to use for each class.
	 * @param array $params Query parameter array
	 * @return object|boolean
	 */
	
	public function getRelated ($class, $fork = array (), $params = array ())
	{
		
		// Get the tree paths to the required class
		
		$paths	= self::_getRelationPaths ($class, $fork);
		
		// If there are no paths return FALSE
		
		if (!$paths)
		{
			return FALSE;
		}
		
		// Get the shortest path to the required class
		
		$path	= self::_getShortestPath ($paths);
		
		// Return the related object
		
		return self::_getRelation ($this, $path, $params);
		
	}
	
	
	/**
	 * Return child objects matching class type
	 * @param string $class Child class type
	 * @param boolean $recursive Whether to load childrens children.
	 *   This will create an object attribute called 'children' on all objects
	 * @param boolean $index Return indexed child array rather than object array
	 * @param string $key Attribute to use as array key
	 * @param array $params Query parameter array
	 * @return array|boolean 
	 */
	
	public function getChildren ($class, $recursive = FALSE, $index = FALSE, $key = FALSE, $params = array ())
	{
		
		// Get children
		
		$children	= self::_getChildren ($class, $this->iget (self::$pk), $recursive, $key, $params);
		
		// If we want the indexes
		
		if ($index)
		{
			$children	= static::_getChildrenIndex ($children);
		}
		
		return $children;
		
	}
	
	
	/**
	 * Return a single row
	 * @param array $params Parameter array
	 * @param int $fetchMode PDO fetch mode, default to assoc
	 * @param \PDO $db Database connection to use, default to slave resource
	 * @return mixed
	 */
	
	public function getValue ($params, $fetchMode = \PDO::FETCH_ASSOC, &$db = FALSE)
	{
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		// Set table

		$params['from']	= '`' . static::$dbTable . '`';
		
		// Return value

		return $db->getValue ($params, $fetchMode);

	}
	
	
	/**
	 * Return all rows
	 * @param array $params Parameter array
	 * @param \PDO $db Database connection to use, default to slave resource
	 * @return mixed
	 */
	
	public function getValues ($params, &$db = FALSE)
	{
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		// Set table

		$params['from']	= '`' . static::$dbTable . '`';
		
		// Return value

		return $db->getValues ($params);

	}
	
	
	/**
	 * Return database master
	 * @return \PDO
	 */
	
	public function &getDbMaster ()
	{
		
		// Get the default master
		
		$obj	=& $this->getResource ('db-master');
		
		// Failing that try to get a random master
		
		if (!$obj)
		{
			
			$obj	=& self::_getRandomDbResource ('db-master');
			
			// Default to database resource if no valid master
		
			if (!$obj)
			{
				$obj =& $this->getResource ('db');
			}
			
			// Set object master to use in the future

			$this->setResourceObj ('db-master', $obj);
			
		}
		
		// Return database connection
		
		return $obj;
		
	}
	
	
	/**
	 * Return database slave
	 * @return \PDO
	 */
	
	public function &getDbSlave ()
	{
		
		// Get the default slave
		
		$obj	=& $this->getResource ('db-slave');
		
		// Failing that try to get a random slave
		
		if (!$obj)
		{
			
			$obj	=& self::_getRandomDbResource ('db-slave');
			
			// Default to database resource if no valid slave
		
			if (!$obj)
			{
				$obj =& $this->getResource ('db');
			}
			
			// Set object slave to use in the future

			$this->setResourceObj ('db-slave', $obj);
			
		}
		
		// Return database connection
		
		return $obj;
		
	}
	
	
	/**
	 * Return a class resource reference
	 * @param string $name Resource name
	 * @return object|boolean
	 */
	
	public function &getResource ($name)
	{
		
		// If the resource is not set
		
		if (!isset ($this->resources[$name]))
		{
			
			// Return FALSE
			
			$bln = FALSE;
			return $bln;
			
		}
		
		// Return resource reference
		
		return $this->resources[$name];
		
	}
	
	
	/**
	 * Set an internal resource from a framework resource
	 * @param string $name Resource name
	 * @param string|array $resource Framework resource referece
	 * @return boolean
	 */
	
	public function setResource ($name, $resource)
	{
		
		// Get the resource
		
		$obj	=& Sonic::getResource ($resource);
		
		if (!$obj)
		{
			return FALSE;
		}
		
		// Set resource object
		
		$this->setResourceObj ($name, $obj);
		
		// Return
		
		return TRUE;
		
	}
	
	
	/**
	 * Set a class resource from the resource object
	 * @param string $name Resource name
	 * @param object $resource Resource object
	 * @return void
	 */
	
	public function setResourceObj ($name, &$resource)
	{
		
		// Set the resource
		
		$this->resources[$name]	=& $resource;
		
		// If the object variable exists 
		
		if (isset ($this->$name))
		{

			// Assign to object variable

			$this->$name	=& $this->resources[$name];
			
		}
		
	}
	
	
	/**
	 * Remove a class resource
	 * @param string $name Resource name
	 * @return void
	 */
	
	public function removeResource ($name)
	{
		
		if (isset ($this->resources[$name]))
		{
			
			unset ($this->resources[$name]);
			
			if (isset ($this->$name))
			{
				unset ($this->$name);
			}
				
		}
		
	}
	
	
	/**
	 * Remove all class resources
	 * @return void
	 */
	
	public function removeResources ()
	{

		foreach (array_keys ($this->resources) as $name)
		{
			if (isset ($this->$name))
			{
				unset ($this->$name);
			}
		}

		unset ($this->resources);
		
	}
	
	
	/**
	 * Return whether an attribute exists or not
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeExists ($name)
	{
		return isset (static::$attributes[$name]);
	}
	
	
	/**
	 * Return whether an attribute is allowed to be retrieved
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeGet ($name)
	{
		return isset (static::$attributes[$name]['get']) && static::$attributes[$name]['get'] === TRUE;
	}
	
	
	/**
	 * Return whether an attribute is allowed to be set
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeSet ($name)
	{
		return isset (static::$attributes[$name]['set']) && static::$attributes[$name]['set'] === TRUE;
	}
	
	
	/**
	 * Return whether an attribute is allowed to be reset
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeReset ($name)
	{
		return !isset (static::$attributes[$name]['reset']) || static::$attributes[$name]['reset'] === TRUE;
	}
	
	
	/**
	 * Return whether an attribute has a value or not (NULL value returns FALSE)
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeHasValue ($name)
	{
		return isset ($this->attributeValues[$name]);
	}
	
	
	/**
	 * Return whether an attribute value is set or not (NULL value returns TRUE)
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeIsset ($name)
	{
		return array_key_exists ($name, $this->attributeValues);
	}
	
	
	/**
	 * Return an attribute parameters array or FALSE if it doesnt exist
	 * Also pass option property array to return a single attribute property
	 * @param string $name Attribute name
	 * @param string $property Attribute property
	 * @return boolean
	 */
	
	public static function _attributeProperties ($name, $property = FALSE)
	{
		
		// If the attribute exists
		
		if (isset (static::$attributes[$name]))
		{
			
			// If a property is specified
			
			if ($property)
			{
				
				// If the property doesnt exist
				
				if (!isset (static::$attributes[$name][$property]))
				{
					return FALSE;
				}
				
				// Return property
				
				return static::$attributes[$name][$property];
				
			}
			
			// Return attribute
			
			return static::$attributes[$name];
			
		}
		
		// Return FALSE
		
		return FALSE;
		
	}
	
	
	/**
	 * Return the object primary key
	 * @return integer
	 */
	
	public function getPK ()
	{
		return $this->attributeIsset (static::$pk)? $this->attributeValues[static::$pk] : FALSE;
	}
	
	
	/**
	 * Return the object class namespace
	 * @return string
	 */
	
	public function getNamespace ()
	{
		return self::_getNamespace ();
	}
	
	
	/**
	 * Return the static class namespace
	 * @return string
	 */
	
	public static function _getNamespace ()
	{
		$arr	= Resource\Parser::_getNamespaceAndClass (get_called_class ());
		return $arr[0];
	}
	
	
	/**
	 * Return the object class name
	 * @return string
	 */
	
	public function getClass ()
	{
		return self::_getClass ();
	}
	
	
	/**
	 * Return the static class name
	 * @return string
	 */
	
	public static function _getClass ()
	{
		$arr	= Resource\Parser::_getNamespaceAndClass (get_called_class ());
		return $arr[1];
	}
	
	
	/**
	 * Return the complete namespace/class path of the static class being called
	 * @return string
	 */
	
	public function getCalledClass ()
	{
		return get_called_class ();
	}
	
	
	/**
	 * Create a new object instance and read it from the database, populating the object attributes
	 * @param mixed $params Object to read.
	 *   This can be an instance ID or a parameter array.
	 * @param \PDO $db Database connection to use
	 * @return \Sonic\Model
	 */
	
	public static function _read ($params, &$db = FALSE)
	{
		
		// Create the object

		$obj	= new static;
		
		// If the params are an array
		
		if (is_array ($params))
		{

			// Select all

			$params['select']	= '*';

			// Get data
			
			$row	= static::_getValue ($params, \PDO::FETCH_ASSOC, $db);

			// If no data was returned return FALSE

			if (!$row)
			{
				return FALSE;
			}

			// Set each attribute value

			foreach ($row as $name => $val)
			{

				if ($obj->attributeExists ($name))
				{
					$obj->iset ($name, $val);
				}

			}
			
		}
		
		// Else the params are not an array, so assume pk
		
		else
		{
			
			// Read the object
			
			if (!$obj->read ($params, $db))
			{
				return FALSE;
			}
			
		}
		
		// Return the object
		
		return $obj;
		
	}
	
	
	/**
	 * Delete an object in the database
	 * @param array|integer $params Primary key value or parameter array
	 * @param \PDO $db Database connection to use
	 * @return boolean
	 */
	
	public static function _delete ($params, &$db = FALSE)
	{
		
		// Create object
		
		$obj	= new static;
		
		// Delete
		
		return $obj->delete ($params, $db);
		
	}
	
	
	/**
	 * Return the number of objects in the database matching the parameters
	 * @param array $params Parameter array
	 * @param \PDO $db Database connection to use
	 * @return integer|boolean
	 */
	
	public static function _count ($params = array (), &$db = FALSE)
	{
		
		// Remove order

		if (isset ($params['orderby']))
		{
			unset ($params['orderby']);
		}
		
		// Remove limit

		if (isset ($params['limit']))
		{
			unset ($params['limit']);
		}

		// Select count

		$params['select'] = 'COUNT(*)';
		
		// Return
		
		return static::_getValue ($params, \PDO::FETCH_ASSOC, $db);

	}
	
	
	/**
	 * Check to see whether the object matching the parameters exists
	 * @param array $params Parameter array
	 * @param \PDO $db Database connection to use
	 * @return boolean
	 */
	
	public static function _exists ($params, &$db = FALSE)
	{
	
		if (!is_array ($params))
		{
			$params	= array (
				'where'	=> array (
					array (static::$pk, $params)
				)
			);
		}
		
		return self::_count ($params, $db) > 0;
		
	}
	
	
	/**
	 * Check to see whether an object with the match ID exists
	 * @param integer $id Primary key
	 * @param \PDO $db Database connection to use
	 * @return boolean
	 */
	
	public static function _IDexists ($id, &$db = FALSE)
	{
		return self::_exists (array (
			'where'	=> array (
				array (static::$pk, $id)
			)
		), $db);
	}
	
	
	/**
	 * Return a random row
	 * @param array $params Parameter Array
	 * @param \PDO $db Database connection to use
	 * @param boolean|\Sonic\Model
	 */
	
	public static function _random ($params = array (), &$db = FALSE)
	{
		
		// Set random parameter
		
		$params['orderby']	= 'RAND()';
		
		// Return random row
		
		return self::_Read ($params, $db);
		
	}
	
	
	/**
	 * Return a single row
	 * @param array $params Parameter array
	 * @param int $fetchMode PDO fetch mode, default to assoc
	 * @param \PDO $db Database connection to use, default to slave resource
	 * @return mixed
	 */
	
	public static function _getValue ($params, $fetchMode = \PDO::FETCH_ASSOC, &$db = FALSE)
	{
		
		// Set select

		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Set from

		if (!isset ($params['from']))
		{
			$params['from']		= '`' . static::$dbTable . '`';
		}
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		// Return value

		return $db->getValue ($params, $fetchMode);

	}
	
	
	/**
	 * Return all rows
	 * @param array $params Parameter array
	 * @param \PDO $db Database connection to use, default to slave resource
	 * @return mixed
	 */
	
	public static function _getValues ($params = array (), &$db = FALSE)
	{
		
		// Set select

		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Set from

		if (!isset ($params['from']))
		{
			$params['from']		= '`' . static::$dbTable . '`';
		}
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		// Return value

		return $db->getValues ($params);

	}
	
	
	/**
	 * Create and return an array of objects for query parameters
	 * @param array $params Parameter array
	 * @param string $key Attribute value to use as the array index, default to 0-indexed
	 * @param \PDO $db Database connection to use
	 * @return \Sonic\Resource\Model\Collection
	 */
	
	public static function _getObjects ($params = array (), $key = FALSE, &$db = FALSE)
	{
		
		// Select all attributes if none are set
		
		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Get data
		
		$rows	= static::_getValues ($params, $db);

		// If no data was returned return FALSE

		if ($rows === FALSE)
		{
			return FALSE;
		}
		
		// Return objects
		
		return self::_arrayToObjects ($rows, $key);
		
	}
	
	
	/**
	 * Execute a PDOStatement query and convert the results into objects
	 * @param \PDOStatement Query to execute 
	 * @param string $key Attribute value to use as the array index, default to 0-indexed
	 * @return Resource\Model\Collection
	 */
	
	public static function _queryToObjects ($query, $key = FALSE)
	{
		$query->execute ();
		return static::_arrayToObjects ($query->fetchAll (\PDO::FETCH_ASSOC), $key);
	}
	
	
	/**
	 * Convert an array into objects
	 * @param array $arr Array to convert
	 * @param string $key Attribute value to use as the array index, default to 0-indexed
	 * @return Resource\Model\Collection
	 */
	
	public static function _arrayToObjects ($arr, $key = FALSE)
	{
		
		// Set object array
		
		$objs	=  new Resource\Model\Collection;
		
		// If no data

		if (!$arr)
		{
			return $objs;
		}
		
		// For each row
		
		foreach ($arr as $row)
		{

			// Create the object

			$obj	= new static;

			// Set each attribute value

			foreach ($row as $name => $val)
			{

				if ($obj->attributeExists ($name))
				{
					$obj->iset ($name, $val, FALSE, TRUE);
				}

			}
			
			// Add to the array
			
			if ($key && isset ($row[$key]))
			{
				$objs[$row[$key]]	= $obj;
			}
			else
			{
				$objs[]	= $obj;
			}

		}
		
		// Return the objects
		
		return $objs;
		
	}
	
	
	/**
	 * Generate a query and return the PDOStatement object
	 * @param array $params Query parameters
	 * @param \Sonic\Resource\Db $db Database resource, default to class slave
	 * @return \PDOStatement
	 * @throws Exception
	 */
	
	public static function _genQuery ($params, &$db = FALSE)
	{
		
		// Set select

		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Set from

		if (!isset ($params['from']))
		{
			$params['from']		= '`' . static::$dbTable . '`';
		}
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		if (!($db instanceof \PDO))
		{
			throw new Exception ('Invalid or no database resource set');
		}
		
		// Return value

		return $db->genQuery ($params);
		
	}
	
	
	/**
	 * Generate the SQL for a query on the model
	 * @param array $params Parameter array
	 * @param \PDO $db Database connection to use, default db
	 * @return string
	 */
	
	public static function _genSQL ($params = array (), &$db = FALSE)
	{
		
		// Set select

		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Set from

		if (!isset ($params['from']))
		{
			$params['from']		= '`' . static::$dbTable . '`';
		}
		
		// Get database slave for read

		if ($db === FALSE)
		{
			$db	=& self::_getDbSlave ();
		}
		
		if (!($db instanceof \PDO))
		{
			throw new Exception ('Invalid or no database resource set');
		}
		
		// Return value

		return $db->genSQL ($params);

	}
	
	
	/**
	 * Return a DOM tree with objects for given query parameters 
	 * @param array $params Parameter array
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param \PDO $db Database connection to use
	 * @return \DOMDocument|boolean
	 */
	
	public static function _toXML ($params = array (), $attributes = FALSE, &$db = FALSE)
	{
		
		// Set class name for the elements
		// Remove the Sonic\Model prefix and convert namespace \ to _
		
		$class	= str_replace ('\\', '_', str_replace ('sonic\\model\\', '', strtolower (get_called_class ())));
		
		// Create DOMDocument

		$doc	= new \DOMDocument ('1.0', 'UTF-8');
		
		// Create root node
		
		$xml	= $doc->createElement ('elements');
		$doc->appendChild ($xml);
		
		// Get objects
		
		$rows	= static::_toArray ($params, $attributes, $db);
		
		// For each row
		
		foreach ($rows as $row)
		{

			// Create the node

			$node	= $doc->createElement ($class);

			// Set each attribute

			foreach ($row as $name => $val)
			{
				$node->appendChild ($doc->createElement (strtolower ($name), htmlentities ($val)));
			}
			
			// Add node

			$xml->appendChild ($node);

		}
		
		// Return doc
		
		return $doc;
		
	}
	
	
	/**
	 * Return a JSON encoded string with objects for given query parameters 
	 * @param array $params Parameter array
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param boolean $addClass Whether to add the class name to each exported object
	 * @param \PDO $db Database connection to use
	 * @return object|boolean
	 */
	
	public static function _toJSON ($params = array (), $attributes = FALSE, $addClass = FALSE, &$db = FALSE)
	{
		
		// Get objects
		
		$rows	= static::_toArray ($params, $attributes, $db);
		
		// Add the class name if required
		
		if ($addClass)
		{
					
			// Set class name for the elements
			// Remove the Sonic\Model prefix and convert namespace \ to _

			$class	= str_replace ('\\', '_', str_replace ('sonic\\model\\', '', strtolower (get_called_class ())));
			
			foreach ($rows as &$row)
			{
				$row['class']	= $class;
			}
			
		}
		
		// Return json encoded
		
		return json_encode ($rows);
		
	}
	
	
	/**
	 * Return an array with object attributes for given query parameters 
	 * @param array $params Parameter array
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param \PDO $db Database connection to use
	 * @return object|boolean
	 */
	
	public static function _toArray ($params = array (), $attributes = FALSE, &$db = FALSE)
	{
		
		// If no attributes are set to display, get all class attributes with get allowed
		
		if ($attributes === FALSE)
		{
			
			$attributes	= array ();
			$obj		= new static;
			
			foreach (array_keys (static::$attributes) as $name)
			{
				
				if ($obj->attributeGet ($name))
				{
					$attributes[]	= $name;
				}
				
			}
			
		}
		
		// Select all attributes from the database if none are set
		
		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Get data
		
		$rows	= static::_getValues ($params, $db);
		
		// If no data was returned return FALSE

		if ($rows === FALSE)
		{
			return FALSE;
		}
		
		// Set array
		
		$arr	= array ();
		
		// For each row
		
		foreach ($rows as $row)
		{

			// Create the sub array
			
			$obj	= array ();

			// Set each attribute

			foreach ($attributes as $name)
			{
				$obj[$name]	= isset ($row[$name])? $row[$name] : NULL;
			}
			
			// Add to main array
			
			$arr[]	= $obj;

		}
		
		// Return array
		
		return $arr;
		
	}
	
	
	/**
	 * Return an array of available paths to a related class
	 * @param string $class Destination class
	 * @param array $fork Used to determine which fork to use when there are
	 *   multiple attributes related to the same class (see getRelated comments)
	 * @param array $paths Available paths, set recursively
	 * @param array $processed Already processed classes, set recursively
	 * @param integer $depth Path depth, set recursively
	 * @return array
	 */
	
	public static function _getRelationPaths ($endClass, $fork = array (), $paths = array (), $processed = array (), $depth = 0)
	{
		
		// Remove first \ from end class
		
		if ($endClass[0] == '\\')
		{
			$endClass	= substr ($endClass, 1);
		}
		
		// Make sure fork is an array
		
		if ($fork === FALSE)
		{
			$fork	= array ();
		}
		
		// Set variables
		
		$class			= get_called_class ();
		$processed[]	= strtolower ($class);
		$parent			= $paths;
		
		$depth++;
		
		// Find paths to the end class
		// Loop through attributes
		
		foreach (static::$attributes as $name => $attribute)
		{
			
			// No attribute relation or class for the relation
			
			if (!isset ($attribute['relation']) || !class_exists ($attribute['relation']) || 
				(isset ($fork[$class]) && $fork[$class] != $name))
			{
				continue;
			}
			
			// Attribute relation matches the end class so add to paths
			
			else if ($attribute['relation'] == $endClass)
			{
				$paths[$name]	= $depth;
				continue;
			}
			
			// The attribute relation has already been processed
			
			else if (in_array (strtolower ($attribute['relation']), $processed))
			{
				continue;
			}
			
			// Recursively look at relation attributes
			
			else
			{
				$subPaths	= $attribute['relation']::_getRelationPaths ($endClass, $fork, $parent, $processed, $depth);
				
				if ($subPaths)
				{
					$paths[$name]	= $subPaths;
				}
				
			}
			
		}
		
		return $paths;
		
	}
	
	
	/**
	 * Return the shortest path from an array of paths (from _getRelationPaths)
	 * @param array $paths Available paths
	 * @param array $path Current path, set recursively
	 * @param array $shortestPath The current shortest path, set recursively
	 * @param integer $shortestDepth The current shortest path depth, set recursively
	 * @return array
	 */
	
	public static function _getShortestPath ($paths, $path = array (), &$shortestPath = array (), &$shortestDepth = FALSE)
	{
		
		$class	= get_called_class ();
		
		foreach ($paths as $name => $child)
		{
			
			$currentPath			= $path;
			$currentPath[$class]	= $name;
			
			if (is_array ($child))
			{
				$childClass	= static::$attributes[$name]['relation'];
				$childClass::_getShortestPath ($child, $currentPath, $shortestPath, $shortestDepth);
			}
			else if ($shortestDepth === FALSE || (int)$child < $shortestDepth)
			{
				$shortestPath	= $currentPath;
				$shortestDepth	= $child;
			}
			
		}
		
		return $shortestPath;
		
	}
	
	
	/**
	 * Return a related object for a given object and path
	 * @param Model $obj Starting object
	 * @param array $path Path to the end object
	 * @param array $params Query parameter array
	 * @return \Sonic\Model|boolean
	 */
	
	public static function _getRelation ($obj, $path, $params = array ())
	{
		
		foreach ($path as $class => $name)
		{
			
			$class		= get_class ($obj);
			$childClass	= $class::$attributes[$name]['relation'];
			
			if ($obj->iget ($name))
			{
				$params['where'][]	= array ($childClass::$pk, $obj->iget ($name));
				$obj	= $childClass::_Read ($params);
			}
			else
			{
				return FALSE;
			}
			
		}
		
		return $obj;
		
	}
	
	
	/**
	 * Return child objects with an attribute matching the current class and specified ID
	 * @param string $class Child class
	 * @param integer $id Parent ID
	 * @param boolean $recursive Whether to load childrens children.
	 *   This will create an object attribute called 'children' on all objects
	 * @param string $key Attribute to use as array key
	 * @param array $params Query parameter array
	 * @return array|boolean
	 */
	
	public static function _getChildren ($class, $id, $recursive = FALSE, $key = FALSE, $params = array ())
	{
		
		// Remove first \ from class
		
		if ($class[0] == '\\')
		{
			$class	= substr ($class, 1);
		}
		
		// Get current (parent) class
		
		$parent	= get_called_class ();
		
		// Find the child variable pointing to the parent
		
		$var	= FALSE;
		
		foreach ($class::$attributes as $name => $attribute)
		{
			
			if (isset ($attribute['relation']) &&
				class_exists ($attribute['relation']) && 
				$attribute['relation'] == $parent)
			{
				$var = $name;
				break;
			}
			
		}
		
		// If no argument
		
		if ($var === FALSE)
		{
			return array ();
		}
		
		// Get children
		
		$qParams	= $params;
		
		if (is_null ($id))
		{
			$qParams['where'][]	= array ($var, 'NULL', 'IS');
		}
		else
		{
			$qParams['where'][]	= array ($var, $id);
		}
		
		$children	= $class::_getObjects ($qParams, $key);
		
		// Get recursively
		
		if ($recursive)
		{
			
			foreach ($children as &$child)
			{
				$child->children	= $child->getChildren ($class, $recursive, FALSE, $key, $params);
			}
			
		}
		
		// Return children
		
		return $children;
		
	}
	
	
	/**
	 * Return an array of child ids from an array of children returned from self::_getChildren
	 * @param array $arr Array of children
	 * @return array
	 */
	
	public static function _getChildrenIndex ($arr)
	{
		
		$return	= array ();

		foreach ($arr as $child)
		{
			$return[] = $child->get ('id');
			$return = array_merge ($return, self::_getChildrenIndex ($child->children));
		}

		return $return;
		
	}
	
	
	/**
	 * Return related objects from a many-to-many pivot table
	 * @param \Sonic\Model $target Target objects to return
	 * @param \Sonic\Model $pivot Pivot object
	 * @return boolean|Model\Collection
	 */
	
	public function getFromPivot (\Sonic\Model $target, \Sonic\Model $pivot, $key = FALSE, $params = [])
	{
		
		// Find the pivot attribute pointing to the source
		
		$sourceClass	= get_called_class ();
		$sourceRef		= FALSE;
		
		foreach ($pivot::$attributes as $name => $attribute)
		{
			if (isset ($attribute['relation']) &&
				$attribute['relation'] == $sourceClass)
			{
				$sourceRef = $name;
				break;
			}
		}
		
		if (!$sourceRef)
		{
			return FALSE;
		}
		
		// Find the pivot attribute pointing to the target
		
		$targetClass	= get_class ($target);
		$targetRef		= FALSE;
		
		foreach ($pivot::$attributes as $name => $attribute)
		{
			if (isset ($attribute['relation']) &&
				$attribute['relation'] == $targetClass)
			{
				$targetRef = $name;
				break;
			}
		}
		
		if (!$targetRef)
		{
			return FALSE;
		}
		
		// Query parameters
		
		if (!isset ($params['select']))
		{
			$params['select']	= 'DISTINCT t.*';
		}
		
		if (isset ($params['from']) && !is_array ($params['from']))
		{
			$params['from']	= [$params['from']];
		}
		
		$params['from'][]	= $target::$dbTable . ' as t';
		$params['from'][]	= $pivot::$dbTable . ' as p';
		
		$params['where'][]	= ['p.' . $sourceRef, $this->getPK ()];
		$params['where'][]	= 'p.' . $targetRef . ' = t.' . $target::$pk;
		
		// Get related objects
		
		return $target::_getObjects ($params, $key);
		
	}
	
	
	/**
	 * Return an array of items with total result count
	 * @param array $params Array of query parameters - MUST BE ESCAPED!
	 * @param array $relations Array of related object attributes or tranformed method attributes to return
	 *   e.g. related value - 'query_name' => array ('\Sonic\Model\User\Group', 'name')
	 *   e.g. tranformed value - 'permission_value' => array ('$this', 'getStringValue')
	 * @param \PDO $db Database connection to use
	 * @return array|boolean
	 */

	public static function _getGrid ($params = array (), $relations = array (), &$db = FALSE)
	{
		
		// If no limit has been set

		if (!$params || !isset ($params['limit']))
		{

			// Set default query limit
			
			$params['limit'] = array (0, 50);

		}
		
		// Get data
		
		if ($relations)
		{
			
			$objs	= static::_getObjects ($params, $db);
			$data	= array ();
			
			foreach ($objs as $obj)
			{
				
				$attributes	= is_array ($params['select'])? $params['select'] : explode (',', $params['select']);
				
				foreach ($attributes as &$val)
				{
					$val = trim ($val);
				}
				
				$data[] = $obj->toArray ($attributes, $relations);
				
			}
			
		}
		else
		{
			$data	= static::_getValues ($params, $db);
		}
		
		// Get count
		
		$count	= self::_count ($params, $db);

		// If there was a problem return FALSE

		if ($count === FALSE || $data === FALSE)
		{
			return FALSE;
		}
		
		// Add class (for API XML response)
		
		$class	= self::_getClass ();
		
		foreach ($data as &$row)
		{
			$row['class']	= $class;
		}
		
		// Return grid
		
		return array (
			'total'	=> $count,
			'rows'	=> $data
		);

	}
	
	
	/**
	 * Return a class resource
	 *  This will either be the default as defined for the class or the global framework resource
	 * @param string|array $name Resource name
	 * @return object|boolean
	 */
	
	public static function &_getResource ($name)
	{
		
		if (is_array ($name))
		{
			return Sonic::getResource ($name);
		}
		else if (isset (static::$defaultResources[$name]))
		{
			return Sonic::getResource (static::$defaultResources[$name]);
		}
		else
		{
			return Sonic::getSelectedResource ($name);
		}
		
	}
	
	
	/**
	 * Return random database resource object
	 * @param string $group Group name
	 * @return boolean|\Sonic\Model\PDO
	 */
	
	public static function &_getRandomDbResource ($group)
	{
		
		$obj	= FALSE;
		
		while (Sonic::countResourceGroup ($group) > 0)
		{

			$name	= Sonic::selectRandomResource ($group);
			$obj	=& Sonic::getResource (array ($group, $name));
			
			// If a PDO object

			if ($obj instanceof \PDO)
			{

				// Attempt to connect to the resource
				// This will throw an exception if it fails or break the loop if it succeeds

				if ($obj instanceof Resource\Db)
				{
					try
					{
						
						$obj->Connect ();
						
						// Set as default group object for persistence
						
						Sonic::setSelectedResource ($group, $name);
						
						break;
						
					}
					catch (\PDOException $e)
					{
						// Do nothing
					}
				}

				// Else not a framework database objects so break the loop to use it

				else
				{
					break;
				}

			}

			// Remove resource from the framework as its not valid
			// then continue to the next object

			Sonic::removeResource ($group, $name);
			$obj = FALSE;

		}
		
		return $obj;
		
	}
	
	
	/**
	 * Return database master
	 * @return \Sonic\Resource\Db
	 */
	
	public static function &_getDb ()
	{
		return self::_getDbMaster ();		
	}
	
	
	/**
	 * Return database master
	 * @return \Sonic\Resource\Db
	 */
	
	public static function &_getDbMaster ()
	{
		
		// Get the default master
		
		$obj	=& self::_getResource ('db-master');
		
		// Failing that try to get a random master
		
		if (!$obj)
		{
			
			$obj	=& self::_getRandomDbResource ('db-master');
			
			// Default to database resource if no valid master
		
			if (!$obj)
			{
				$obj =& self::_getResource ('db');
			}
			
		}
		
		// Return database connection
		
		return $obj;
		
	}
	
	
	/**
	 * Return database slave
	 * @return \Sonic\Resource\Db
	 */
	
	public static function &_getDbSlave ()
	{
		
		// Get the default slave
		
		$obj	=& self::_getResource ('db-slave');
		
		// Failing that try to get a random slave
		
		if (!$obj)
		{
			
			$obj	=& self::_getRandomDbResource ('db-slave');
			
			// Default to database resource if no valid slave
		
			if (!$obj)
			{
				$obj =& self::_getResource ('db');
			}
			
		}
		
		// Return database connection
		
		return $obj;
		
	}
	
	
	/**
	 * Print a variable - very useful for debugging
	 * @param integer $mode Which display mode to use
	 *   0 - print_r, default
	 *   1 - var_dump
	 * @param boolean $return Return string rather than output
	 * @param boolean $pre Whether to wrap output in pre tags
	 * @return void|string
	 */
	
	public static function pre ($var, $mode = 0, $return = FALSE, $pre = TRUE)
	{
		Resource\Parser::pre ($var, $mode, $return, $pre);
	}
	
	
	/**
	 * Whether to write to the changelog
	 * @param string $type Change type (create, update, delete)
	 * @return boolean
	 */
	
	private function changelog ($type)
	{
		
		// If changelog or type is disabled for the class return FALSE
		
		if (isset (static::$changelogIgnore))
		{
			
			if (static::$changelogIgnore === TRUE || 
				is_array (static::$changelogIgnore) && in_array ($type, static::$changelogIgnore))
			{
				return FALSE;
			}
			
		}
		
		// If there is no changelog resource defined or we're dealing with a changelog object return FALSE
		
		if (!($this->getResource ('changelog') instanceof Resource\Change\Log) || 
			$this instanceof Resource\Change\Log || $this instanceof Resource\Change\Log\Column)
		{
			return FALSE;
		}
		
		// Default return TRUE
		
		return TRUE;
		
	}
	
	
}

// End Model Class