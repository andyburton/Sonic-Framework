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
	 * Model db resource
	 * @var PDO
	 */

	protected $db					= FALSE;

	/**
	* Model parser resource
	* @var Parser
	*/

	protected $parser				= FALSE;
	
	
	/**
	 * Instantiate class
	 */
	
	public function __construct ()
	{
		
		// Set default resources

		$this->resources	= Sonic::getSelectedResources ();
		
		// For each resource

		foreach (array_keys ($this->resources) as $name)
		{
			
			// If the object variable exists and isnt set

			if (isset ($this->$name) && $this->$name === FALSE)
			{

				// Assign to object variable
				
				$this->$name	=& $this->resources[$name];

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
	 * Return an attribute value
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
		
		// If no attribute value is not set
		
		if (!$this->attributeHasValue ($name))
		{
			
			// If there is a default set to value
			
			if (isset (static::$attributes[$name]['default']))
			{
				$this->attributeValues[$name]	= static::$attributes[$name]['default'];
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
	 * Set an attribute value
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
			
			// Validate it using the parser method
			
			$this->parser->Validate (static::$attributes[$name], $val);
			
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
		
		if (!isset (static::$attributes[$name]['default']))
		{
			unset ($this->attributeValues[$name]);
		}
		
		// Else there is a default attribute value so set it
		
		else
		{
			$this->attributeValues[$name]	= static::$attributes[$name]['default'];
		}
		
	}
	
	
	/**
	 * Create object in the database
	 * @param array $exclude Attributes not to set
	 * @return boolean
	 */
	
	public function create ($exclude = array ())
	{
		
		// If there is no primary key value exclude it (i.e assume auto increment)
		
		if (!isset ($this->attributeValues[static::$pk]))
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
			
			// If there is no attribute value
			
			if (!isset ($this->attributeValues[$name]))
			{
				
				// If there is a default set it
				
				if (isset ($attribute['default']))
				{
					$this->set ($name, $attribute['default']);
				}
				
				// Else if the attribute can be set to NULL then do so
				
				else if (isset ($attribute['null']) && $attribute['null'])
				{
					$this->set ($name, NULL);
				}
				
				// Else set a blank value
				
				else
				{
					$this->set ($name, '');
				}
				
			}
			
			// Add the column and values
			
			$columns	.= ',' . $name;
			$values		.= ',' . is_null ($this->attributeValues[$name])? 'NULL' : ':' . $this->attributeValues[$name];
			
		}
		
		// Trim the first character (,) from the column and values
		
		$columns	= substr ($columns, 1);
		$values		= substr ($values, 1);
		
		// Prepare query

		$query	= $this->db->prepare ('
		INSERT INTO ' . static::$dbTable . ' (' . $columns . ')
		VALUES ( ' . $values . ')
		');
		
		// Loop through attributes
		
		foreach (array_keys (static::$attributes) as $name)
		{
			
			// If we're excluding the attribute then move on
			
			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// Bind paramater

			$query->bindValue (':' . $name, $this->attributeValues[$name]);
			
		}
		
		// Execute

		$query->execute ();

		// Set the pk

		$this->set (static::$pk, $this->db->lastInsertID ());

		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Read an object from the database, populating the object attributes
	 * @param mixed $pkValue Primary key value
	 * @return boolean
	 */
	
	public function read ($pkValue = FALSE)
	{
		
		// If there is a key value passed set it
		
		if ($pkValue !== FALSE)
		{
			$this->set (static::$pk, $pkValue);
		}
		
		// Prepare query

		$query = $this->db->prepare ('
		SELECT * FROM ' . static::$dbTable . '
		WHERE ' . static::$pk . ' = :pk
		');

		// Bind paramater

		$query->bindValue (':pk',	$this->get (static::$pk));
		
		// Execute
		
		$query->execute ();

		// Set row

		$row	= $query->fetch (\PDO::FETCH_ASSOC);

		// If no data was returned return FALSE

		if ($row === FALSE)
		{
			return FALSE;
		}

		// Set each attribute value

		foreach ($row as $name => $val)
		{
			
			if ($this->attributeExists ($name))
			{
				$this->attributeValues[$name]	= $val;
			}
			
		}
		
		// Return TRUE
		
		return TRUE;
		
	}
	
	
	/**
	 * Update an object in the database
	 * @param array $xclude Attributes not to update
	 * @return boolean
	 */
	
	public function update ($exclude = array ())
	{

		// Exclude the primary key
		
		$exclude[]	= static::$pk;
		
		// Set value variable

		$values		= NULL;
		
		// Loop through attributes
		
		foreach (array_keys ($this->attributes) as $name)
		{
			
			// If we're excluding the attribute then move on

			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// If there is no attribute value
			
			if (!isset ($this->attributeValues[$name]))
			{
				
				// Exclude it and move on
				
				$exclude[]	= $name;
				continue;
				
			}
			
			// Add the value
			
			$values	.= ',' . $name . ' = ' . is_null ($this->attributeValues[$name])? 'NULL' : ':' . $this->attributeValues[$name];
			
		}
		
		// Trim the first character (,) from the values
		
		$values		= substr ($values, 1);
		
		// Prepare query
		
		$query = $this->db->prepare ('
		UPDATE ' . static::$dbTable . '
		SET ' . $values . '
		WHERE ' . static::$pk . ' = :pk
		');
		
		// Loop through attributes
		
		foreach (array_keys (static::$attributes) as $name)
		{
			
			// If we're excluding the attribute then move on
			
			if (in_array ($name, $exclude))
			{
				continue;
			}
			
			// Bind paramater

			$query->bindValue (':' . $name, $this->attributeValues[$name]);
			
		}
		
		// Bind pk
		
		$query->bindValue (':pk', $this->get (static::$pk));
		
		// Execute

		$query->execute ();

		// return TRUE

		return TRUE;
		
	}
	
	
	/**
	 * Delete an object in the database
	 * @param mixed $pkValue Primary key value
	 * @return boolean
	 */
	
	public function delete ($pkValue = FALSE)
	{
		
		// If there is no key value passed set it
		
		if ($pkValue === FALSE)
		{
			$pkValue	= $this->get (static::$pk);
		}
		
		// Prepare query

		$query = $this->db->prepare ('
		DELETE FROM ' . static::$dbTable . '
		WHERE ' . static::$pk . ' = :pk
		');
		
		// Bind pk
		
		$query->bindValue (':pk', $pkValue);
		
		// Execute

		$query->execute ();

		// return TRUE

		return TRUE;
		
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
	 * @return object|boolean
	 */
	
	public function toArray ($attributes = FALSE)
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
		
		// Return array
		
		return $arr;
		
	}

	
	/**
	 * Populate object attributes from a post array
	 * Attribute names need to be in the format ClassName_AttributeName in $_POST
	 * @param boolean $validate Validate attributes during set
	 * @return void
	 */
	
	public function fromPost ($validate = TRUE)
	{
		
		// Set attributes to set
		
		$arr	= array ();
		
		// Get class name
		
		$class	= explode ('\\', get_called_class ());
		$class	= strtolower ($class[count ($class) -1]);
		
		// Loop through class attributes and add any that exist in post
		
		foreach (array_keys (static::$attributes) as $name)
		{
			
			if (isset ($_POST[$class . '_' . strtolower ($name)]))
			{
				$arr[$name]	= $_POST[$class . '_' . $name];
			}
			
		}
		
		// Add attributes
		
		$this->fromArray ($arr, $validate);
		
	}
	
	
	/**
	 * Populate object attributes from an array
	 * @param array $attributes Attributes
	 * @param boolean $validate Validate attributes during set
	 * @return void
	 */
	
	public function fromArray ($attributes, $validate = TRUE)
	{

		// Set each attribute

		foreach ($attributes as $name => $val)
		{
			$this->set ($name, $val, $validate);
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
	 * @return object|boolean
	 */
	
	public function getRelated ($class, $fork = array ())
	{
		
		// Get the tree paths to the required class
		
		$paths	= static::_getRelationPaths ($class, $fork);
		
		// If there are no paths return FALSE
		
		if (!$paths)
		{
			return FALSE;
		}
		
		// Get the shortest path to the required class
		
		$path	= static::_getShortestPath ($paths);
		
		// Return the related object
		
		return static::_getRelation ($this, $path);
		
	}
	
	
	/**
	 * Return a single row
	 * @param array $params Parameter array
	 * @param int $fetchMode PDO fetch mode, default to assoc
	 * @return mixed
	 */
	
	public function getValue ($params, $fetchMode = \PDO::FETCH_ASSOC)
	{
		
		// Set table

		$params['from']	= static::$dbTable;
		
		// Return value

		return $this->db->getValue ($params, $fetchMode);

	}
	
	
	/**
	 * Return all rows
	 * @param array $params Parameter array
	 * @return mixed
	 */
	
	public function getValues ($params)
	{
		
		// Set table

		$params['from']	= static::$dbTable;
		
		// Return value

		return $this->db->getValues ($params);

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
	 * Set a class resource
	 * @param string $name Resource name
	 * @param object $resource Resource object
	 * @return void
	 */
	
	public function setResource ($name, $resource)
	{
		
		// Set the resource
		
		$this->resources[$name]	= $resource;
		
		// If the object variable exists 
		
		if (isset ($this->$name))
		{

			// Assign to object variable

			$this->$name	=& $this->resources[$name];

		}
		
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
	 * Return whether an attribute has a value or not
	 * @param string $name Attribute name
	 * @return boolean
	 */
	
	public function attributeHasValue ($name)
	{
		return isset ($this->attributeValues[$name]);
	}
	
	
	/**
	 * Create a new object instance and read it from the database, populating the object attributes
	 * @param mixed $params Object to read.
	 *   This can be an instance ID or a parameter array.
	 * @return boolean|object
	 */
	
	public static function _read ($params)
	{
		
		// Create the object

		$obj	= new static;
		
		// If the params are an array
		
		if (is_array ($params))
		{

			// Select all

			$params['select']	= '*';

			// Get data
			
			$row	= static::_getValue ($params);

			// If no data was returned return FALSE

			if ($row === FALSE)
			{
				return FALSE;
			}

			// Set each attribute value

			foreach ($row as $name => $val)
			{

				if ($obj->attributeExists ($name))
				{
					$obj->attributeValues[$name]	= $val;
				}

			}
			
		}
		
		// Else the params are not an array, so assume pk
		
		else
		{
			
			// Read the object
			
			if (!$obj->read ($params))
			{
				return FALSE;
			}
			
		}
		
		// Return the object
		
		return $obj;
		
	}
	
	
	/**
	 * Delete an object in the database
	 * @param mixed $pkValue Primary key value
	 * @return boolean
	 */
	
	public static function _delete ($pkValue)
	{
		
		// Create object
		
		$obj	= new static;
		
		// Delete
		
		return $obj->delete ($pkValue);
		
	}
	
	
	/**
	 * Return the number of objects in the database matching the parameters
	 * @param array $params Parameter array
	 * @return integer|boolean
	 */
	
	public static function _count ($params = array ())
	{
		
		// Remove order

		if (isset ($params['orderby']))
		{
			unset ($params['orderby']);
		}

		// Select count

		$params['select'] = 'COUNT(*)';
		
		// Return

		return static::_getValue ($params);

	}
	
	
	/**
	 * Return a single row
	 * @param array $params Parameter array
	 * @param int $fetchMode PDO fetch mode, default to assoc
	 * @return mixed
	 */
	
	public static function _getValue ($params, $fetchMode = \PDO::FETCH_ASSOC)
	{
		
		// Set table

		if (!isset ($params['from']))
		{
			$params['from']	= static::$dbTable;
		}
		
		// Get database
		
		$db	= Sonic::getSelectedResource ('db');
		
		if (!($db instanceof \PDO))
		{
			throw new Exception ('Invalid or no database resource set');
		}
		
		// Return value

		return $db->getValue ($params, $fetchMode);

	}
	
	
	/**
	 * Return all rows
	 * @param array $params Parameter array
	 * @return mixed
	 */
	
	public static function _getValues ($params)
	{
		
		// Set table

		if (!isset ($params['from']))
		{
			$params['from']	= static::$dbTable;
		}
		
		// Get database
		
		$db	= Sonic::getSelectedResource ('db');
		
		if (!($db instanceof \PDO))
		{
			throw new Exception ('Invalid or no database resource set');
		}
		
		// Return value

		return $db->getValues ($params);

	}
	
	
	/**
	 * Create and return an array of objects for query parameters
	 * @param array $params Parameter array
	 * @param boolean $usePk Whether to use the primary key as the array index, default to false
	 * @return array
	 */
	
	public static function _getObjects ($params, $usePk = FALSE)
	{
		
		// Set object array
		
		$arr	= array ();
		
		// Select all attributes if none are set
		
		if (!isset ($params['select']))
		{
			$params['select']	= '*';
		}
		
		// Get data
		
		$rows	= static::_getValues ($params);
		
		// If no data was returned return FALSE

		if ($rows === FALSE)
		{
			return FALSE;
		}
		
		// For each row
		
		foreach ($rows as $row)
		{

			// Create the object

			$obj	= new static;

			// Set each attribute value

			foreach ($row as $name => $val)
			{

				if ($obj->attributeExists ($name))
				{
					$obj->attributeValues[$name]	= $val;
				}

			}
			
			// Add to the array
			
			if ($usePk)
			{
				$arr[$obj->get (static::$pk)]	= $obj;
			}
			else
			{
				$arr[]	= $obj;
			}

		}
		
		// Return the object array
		
		return $arr;
		
	}
	
	
	/**
	 * Return a DOM tree with objects for given query parameters 
	 * @param array $params Parameter array
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @return \DOMDocument|boolean
	 */
	
	public static function _toXML ($params = array (), $attributes = FALSE)
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
		
		$rows	= static::_toArray ($params, $attributes);
		
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
	 * @return object|boolean
	 */
	
	public static function _toJSON ($params = array (), $attributes = FALSE, $addClass = FALSE)
	{
		
		// Get objects
		
		$rows	= static::_toArray ($params, $attributes);
		
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
	 * @return object|boolean
	 */
	
	public static function _toArray ($params = array (), $attributes = FALSE)
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
		
		$rows	= static::_getValues ($params);
		
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
		
		$class			= get_called_class ();
		$processed[]	= $class;
		$parent			= $paths;
		
		$depth++;
		
		foreach (static::$attributes as $name => $attribute)
		{
			
			if (!isset ($attribute['relation']) || !class_exists ($attribute['relation']) || 
				(isset ($fork[$class]) && $fork[$class] != $name))
			{
				continue;
			}
			else if ($attribute['relation'] == $endClass)
			{
				$paths[$name]	= $depth;
				continue;
			}
			else if (in_array ($attribute['relation'], $processed))
			{
				continue;
			}
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
	 * @return Model
	 */
	
	public static function _getRelation ($obj, $path)
	{
		
		foreach ($path as $class => $name)
		{
			$class		= get_class ($obj);
			$childClass	= $class::$attributes[$name]['relation'];
			$obj		= $childClass::_read ($obj->attributeValues[$name]);
		}
		
		return $obj;
		
	}
	
	
	/**
	 * Print a variable - very useful for debugging
	 * @param integer $mode Which display mode to use
	 *   0 - print_r, default
	 *   1 - var_dump
	 * @return void
	 */
	
	public static function pre ($var, $mode = 0)
	{
		
		switch ($mode)
		{
			
			case 0:
				echo '<pre>' . print_r ($var, TRUE) . '</pre>';
				break;
			
			case 1:
				echo '<pre>' . var_dump ($var) . '</pre>';
				break;
			
		}
		
		
	}
	
	
}

// End Model Class