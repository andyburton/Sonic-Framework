<?php

// Define namespace

namespace Sonic\Resource;

// Start Parser Class

class Parser
{
	
	
	/**
	 * Charsets for validation
	 */
	
	const CHARSET_ALPHA  		= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -';
	const CHARSET_ALPHABETIC	= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const CHARSET_BOOL			= '10';
	const CHARSET_DATE			= '1234567890-/';
	const CHARSET_DATETIME		= '1234567890-:/ ';
	const CHARSET_DBTABLE		= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789';
	const CHARSET_DECIMAL		= '-.,1234567890';
	const CHARSET_DEFAULT		= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!\"�\$%^&*()_+-=[]{};'#:@~,./<>?`�\| \r\n\t";
	const CHARSET_EMAIL			= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.@_- \'';
	const CHARSET_FILENAME		= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._- ';
	const CHARSET_FILEPATH		= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ:/\\._- ';
	const CHARSET_INT			= '-1234567890';
	const CHARSET_INT_UNSIGNED	= '1234567890';
	const CHARSET_PHONE		    = '1234567890 ';
	const CHARSET_POSTCODE		= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ';
	const CHARSET_PUNCTUATION	= '!"�$%^&*()_+-=[]{};\'#:@~,./<>?`�\| ';
	const CHARSET_SMS    		= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -,.$£?';
	const CHARSET_WHITESPACE	= " \r\n\t";
	const CHARSET_URL			= '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ:/.?=&-%_+ ';
	const CHARSET_NONE			= FALSE;
	
	
	/**
	 * Return a charset
	 * @param string $name Charset name
	 * @return string|boolean
	 */
	
	public static function _getCharset ($name)
	{
		
		if (defined ('self::CHARSET_' . strtoupper ($name)))
		{
			return constant ('self::CHARSET_' . strtoupper ($name));
		}
		else
		{
			return FALSE;
		}
		
	}
	
	
	/**
	 * Return a charset
	 * @param string $name Charset name
	 * @return string|boolean
	 */
	
	public function charsetExists ($name)
	{
		return defined ('self::CHARSET_' . strtoupper ($name));
	}
	
	
	/**
	 * Validate an attribute
	 * @param string $name Attribute name
	 * @param array $criteria The validation criteria
	 *   The criteria can have the following keys:
	 *   type - The value type (from the model TYPE constants), used by several other criteria
	 *   charset - Only allows characters from the given charset (passed to getCharset () e.g. 'default' = CHARSET_DEFAULT). Will cast value to a string.
	 *   min - The minimum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   max - The maximum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   valid - Single value or array of function names.
	 *   null - Whether the value can be NULL.
	 *   values - Single value or array of values that are only allowed for the value.
	 * @param mixed $val The value to validate
	 * @throws Parser\Exception
	 * @return void
	 */
	
	public function Validate ($name, $criteria, $val)
	{
		self::_Validate ($name, $criteria, $val);
	}
	
	
	/**
	 * Validate an attribute
	 * @param string $name Attribute name
	 * @param array $criteria The validation criteria
	 *   The criteria can have the following keys:
	 *   type - The value type (from the model TYPE constants), used by several other criteria
	 *   charset - Only allows characters from the given charset (passed to getCharset () e.g. 'default' = CHARSET_DEFAULT). Will cast value to a string.
	 *   min - The minimum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   max - The maximum value, this is length if type = TYPE_STRING or value if type = TYPE_INT or TYPE_DECIMAL
	 *   valid - Single value or array of function names.
	 *   null - Whether the value can be NULL.
	 *   values - Single value or array of values that are only allowed for the value.
	 * @param mixed $val The value to validate
	 * @throws Parser\Exception
	 * @return void
	 */
	
	public static function _Validate ($name, $criteria, $val)
	{
		
		$name	= ucwords ($name);
		
		// If the value is null
		
		if (is_null ($val))
		{
			
			// Return whether the null value is allowed
			
			if (isset ($criteria['null']) && $criteria['null'])
			{
				return;
			}
			else
			{
				throw new Parser\Exception ('Invalid NULL value for ' . $name);
			}
			
		}
		
		// If we have specific values to use
		
		if (isset ($criteria['values']))
		{
			
			// If the values is single convert to array
			
			if (!is_array ($criteria['values']))
			{
				$criteria['values']	= array ($criteria['values']);	
			}
			
			// If the value is not allowed
			
			if (!in_array ($val, $criteria['values']))
			{
				throw new Parser\Exception ('Invalid value `' . $val . '` for ' . $name);
			}
			
		}
		
		// If there is a charset to validate against
		
		if (isset ($criteria['charset']))
		{
			
			// If the charset is not an array set as one
			
			if (!is_array ($criteria['charset']))
			{
				$criteria['charset']	= array ($criteria['charset']);
			}
			
			// Set charset
			
			$charset	= NULL;
			
			// Loop through charsets and add together
			
			foreach ($criteria['charset'] as $charsetName)
			{
				
				$newCharset	= self::_getCharset ($charsetName);
				
				if ($newCharset === FALSE && !$charset)
				{
					$charset	= FALSE;
				}
				else
				{
					$charset	.= $newCharset;
				}
				
			}
			
			// If there is no charset then ignore
			
			if ($charset !== FALSE)
			{

				// Get value length

				$length		= strlen ($val);

				// Cast to string

				settype ($val, 'string');

				// For each character in the value

				for ($i = 0; $i < $length; $i++)
				{

					// If the character is not in the charset return FALSE

					if (strpos ($charset, $val[$i]) === FALSE)
					{
						throw new Parser\Exception ('Invalid character `' . $val[$i] . '` in ' . $name);
					}

				}
				
			}
			
		}
		
		// If we have a minimum value
		
		if (isset ($criteria['min']))
		{
			
			// If there is no type set to false
			
			if (!isset ($criteria['type']))
			{
				$criteria['type']	= FALSE;
			}
			
			// Switch the type
			
			switch ($criteria['type'])
			{
				
				// Numeric values, validate actual value
				
				case \Sonic\Model::TYPE_INT:
				case \Sonic\Model::TYPE_DECIMAL:
					
					// Cast to float

					settype ($val, 'float');
					
					// If not valid return FALSE
					
					if ($val < (float)$criteria['min'])
					{
						throw new Parser\Exception ($name . ' must be at least ' . (float)$criteria['min']);
					}
					
					break;
				
				// String values, validate string length
				
				case \Sonic\Model::TYPE_STRING:
				case \Sonic\Model::TYPE_DATE:
				case \Sonic\Model::TYPE_DATETIME:
				case \Sonic\Model::TYPE_ENUM:
					
					// Cast to string

					settype ($val, 'string');
					
					// If not valid return FALSE
					
					if (strlen ($val) < (int)$criteria['min'])
					{
						
						if ((int)$criteria['min'] == 1)
						{
							$word	= in_array (strtoupper ($name[0]), array ('A', 'E', 'I', 'O'))? 'an' : 'a';
							throw new Parser\Exception ('You must enter ' . $word . ' ' . $name);
						}
						else
						{
							throw new Parser\Exception ($name . ' must be at least ' . (int)$criteria['min'] . ' character' . ((int)$criteria['min'] == 1? '' : 's'));
						}
						
					}
					
					break;
				
			}
			
		}

		// If we have a maximum value
		
		if (isset ($criteria['max']))
		{
			
			// If there is no type set to false
			
			if (!isset ($criteria['type']))
			{
				$criteria['type']	= FALSE;
			}
			
			// Switch the type
			
			switch ($criteria['type'])
			{
				
				// Numeric values, validate actual value
				
				case \Sonic\Model::TYPE_INT:
				case \Sonic\Model::TYPE_DECIMAL:
					
					// Cast to float

					settype ($val, 'float');
					
					// If not valid return FALSE
					
					if ($val > (float)$criteria['max'])
					{
						throw new Parser\Exception ($name . ' must be no more than ' . (float)$criteria['max']);
					}
					
					break;
				
				// String values, validate string length
				
				case \Sonic\Model::TYPE_STRING:
				case \Sonic\Model::TYPE_DATE:
				case \Sonic\Model::TYPE_DATETIME:
				case \Sonic\Model::TYPE_ENUM:
					
					// Cast to string

					settype ($val, 'string');
					
					// If not valid return FALSE
					
					if (strlen ($val) > (int)$criteria['max'])
					{
						throw new Parser\Exception ($name . ' must be no more than ' . (int)$criteria['max'] . ' character' . ((int)$criteria['max'] == 1? '' : 's'));
					}
					
					break;
				
			}
			
		}
		
		// If we have fucntions to validate against
		
		if (isset ($criteria['valid']))
		{
			
			// If the function is single convert to array
			
			if (!is_array ($criteria['valid']))
			{
				$criteria['valid']	= array ($criteria['valid']);	
			}
			
			// For each validation function
			
			foreach ($criteria['valid'] as $function)
			{
				
				// Set function name
				
				$function	= '_validate' . ucfirst ($function);
				
				// Check the method exists
				
				if (!method_exists (get_class (), $function))
				{
					throw new Parser\Exception ('Invalid validation function `' . $function . '` for ' . $name);
				}
				
				// Call the method
				
				call_user_func (array (get_class (), $function), $val);
				
			}
			
		}
		
	}
	
	
	/**
	 * Convert a table name class name
	 * @param string $table Table name
	 * @param string $namespace Namespace
	 * @return string
	 */
	
	public function convertToClassName ($table, $namespace = 'Sonic\\Model\\')
	{
		
		$arr		= explode ('_', $table);
		
		foreach ($arr as &$val)
		{
			$val = ucfirst (strtolower ($val));
		}
		
		return $namespace . implode ('\\', $arr);
		
	}
	
	
	/**
	 * Convert a table name to a namespace and class name
	 * @param string $table Table name
	 * @return array
	 */
	
	public function convertToNamespaceAndClass ($table)
	{
		
		$arr		= explode ('_', $table);
		
		foreach ($arr as &$val)
		{
			$val = ucfirst (strtolower ($val));
		}
		
		array_unshift ($arr, 'Sonic', 'Model');
		
		$class		= array_pop ($arr);
		$namespace	= implode ('\\', $arr);
		
		return array ($namespace, $class);
		
	}
	
	
	/**
	 * Return a namespace and class from a full class name (get_class or get_called_class)
	 * @param string $class Full class name
	 * @return array (namespace, class)
	 */
	
	public static function _getNamespaceAndClass ($class)
	{
		
		$arr		= explode ('\\', $class);
		
		$class		= array_pop ($arr);
		$namespace	= implode ('\\', $arr);
		
		return array ($namespace, $class);
		
	}
	
	
	/**
	 * Convert a date from UK format (DD-MM-YYYY) to MySQL format (YYYY-MM-DD)
	 * @param string $str Date to convert
	 * @return string
	 */
	
	public static function _convertUKDate ($str)
	{
		return preg_replace ('/^(\d{2})([^\d]{1})(\d{2})([^\d]{1})(\d{2,4})$/', '$5-$3-$1', $str);
	}
	
	
	/**
	 * Convert a date or datetime into a unix timestamp
	 * @param string $str Date to convert
	 * @return type 
	 */
	
	public static function _convertToUnixtime ($str)
	{
		
		// Set unixtime
		
		$unixtime	= 0;
		
		// If in a yyyy-mm-dd hh:ii:ss (MySQL Date Time) format
		
		if (preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $str, $matches))
		{
			$unixtime	= mktime ($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
		}
		
		// Else if in yyyy-mm-dd
		
		else if (preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2})$/', $str, $matches))
		{
			$unixtime	= mktime (0, 0, 0, $matches[2], $matches[3], $matches[1]);
		}
		
		// Else if dd-mm-yyyy hh:ii:ss (UK Format)
		
		else if (preg_match ('/^(\d{2})\D{1}(\d{2})\D{1}(\d{4}) (\d{2}):(\d{2}):(\d{2})$/', $str, $matches))
		{
			$unixtime	= mktime ($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		
		// Else if in dd-mm-yyyy
		
		else if (preg_match ('/^(\d{2})\D{1}(\d{2})\D{1}(\d{4})$/', $str, $matches))
		{
			$unixtime	= mktime (0, 0, 0, $matches[2], $matches[1], $matches[3]);
		}
		
		// else if a timestamp
		
		else if (preg_match('/^\d+$/', $str))
		{
			$unixtime = $str;
		}
		
		// Return unixtime
		
		return $unixtime;
		
	}
	
	
	/**
	 * Convert to ISO-8859-1 encoding
	 * @param string $str String to convert
	 * @return string
	 */
	
	public static function _toISO ($str)
	{
		return mb_convert_encoding ($str, 'ISO-8859-1', mb_detect_encoding ($str, 'UTF-8, ISO-8859-1, ISO-8859-15', TRUE));
	}
	
	
	/**
	 * Return a UTC date
	 * @param string $format Date format to return
	 * @param integer $time Unix time to get date for
	 * @return string
	 */
	
	public static function _utcDate ($format = 'Y-m-d H:i:s', $time = FALSE)
	{
		return $time? gmdate ($format, $time) : gmdate ($format);
	}
	
	
	/**
	 * Return a UTC date
	 * @param string $format Date format to return
	 * @param integer $time Unix time to get date for
	 * @return string
	 */
	
	public function utcDate ($format = 'Y-m-d H:i:s', $time = FALSE)
	{
		return self::_utcDate ($format, $time);
	}
	
	
	/**
	 * Convert a date between timezones
	 * @param string $date Date time string
	 * @param string $to Timezone to convert to
	 * @param string $format Format to output, default to Y-m-d H:i:s
	 * @param string $from Timezone to convert from, default UTC
	 * @return string
	 */
	
	public static function _convertTZ ($date, $to, $format = 'Y-m-d H:i:s', $from = 'UTC')
	{
		
		$date = new \DateTime ($date, new \DateTimeZone ($from));
		$date->setTimezone (new \DateTimeZone ($to));
		
		return $date->format ($format);
		
	}
	
	
	/**
	 * Returns the value of the key of the passed array if it exists or null if not
	 * This saves having to explictly declare each array key to check that they exist
	 * @param array $arr The array to get the key from
	 * @param string $key The name of the key or an array of keys if checking a nested array
	 * @param mixed $fallback The fallback value to return if the array key does not exist
	 * @return mixed
	 */
	
	public static function _ak ($arr, $key, $fallback = NULL)
	{
		return self::ak ($arr, $key, $fallback);
	}
	
	public static function ak ($arr, $key, $fallback = NULL)
	{
		
		if (is_array ($arr))
		{
			
			if (is_array ($key))
			{
				
				// Store $arr in a new var
				
				$prev = $arr;
				
				// Loop through each entry in $key
				
				for ($i = 0; $i < count ($key); $i++)
				{
					
					// If we are not at the last entry
					
					if ($i < count ($key)-1)
					{
						
						// If the current entry is an array
						// update $prev to the current entry
						
						if (is_array (self::_ak ($prev, $key[$i])))
						{
							
							$prev = $prev[$key[$i]];
							
						}
						
						// Current entry is not an array and we are not at the last entry either so the key doesn't exist
						
						else
						{
							break;
						}
						
					}
					
					// We are at the last entry
					
					else
					{
						
						// If $prev is an array and the key exists return it
						
						if (is_array ($prev) && isset ($prev[$key[$i]]))
						{
							return $prev[$key[$i]];
						}
						
					}
					
				}
				
			}
			
			// $key is not an array
			
			else
			{
				
				// If the key exists in the array return it
				
				if (isset ($arr[$key]))
				{
					return $arr[$key];
				}
				
			}
			
		}
		
		// Return the default return value as the key was not found in the array
		
		return $fallback;
		
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
		
		// If the variable is an object of type \Sonic\Model remove resources
		
		if (is_object ($var) && $var instanceof \Sonic\Model)
		{
			$var = clone $var;
			$var->removeResources ();
		}
		
		// Else if the variable is an array
		
		else if (is_array ($var))
		{
			
			// Foreach entry that is an object of type \Sonic\Model remove resources
			
			foreach ($var as &$val)
			{
				if (is_object ($val) && $val instanceof \Sonic\Model)
				{
					$val = clone $val;
					$val->removeResources ();
				}
			}
			
		}

		// Generate message
		
		$msg	= NULL;
		
		switch ($mode)
		{
			
			case 0:
				$msg = print_r ($var, TRUE);
				break;
			
			case 1:
				$msg = var_dump ($var);
				break;
			
		}
		
		// Pre tags
		
		if ($pre)
		{
			$msg = '<pre>' . $msg . '</pre>';
		}
		
		// Return or output
		
		if ($return)
		{
			return $msg;
		}
		else
		{
			echo $msg;
		}
		
	}
	
	
	/**
	 * Custom functions
	 * Should all be static and start with '_validate' followed by the function name
	 * e.g. 'email' would be '_validateEmail'
	 */
	
	
	/**
	 * Check an email address is valid
	 * @param string $val Value to validate
	 * @throws Parser\Exception
	 * @return void
	 */
	
	public static function _validateEmail ($val)
	{
		
		if (!self::_isEmail ($val))
		{
			throw new Parser\Exception ('Invalid email address: ' . $val);
		}
		
	}
	
	
	/**
	 * Check a date is a valid date YYYY-MM-DD
	 * @param string $val Value to validate
	 * @throws Parser\Exception
	 * @return void 
	 */
	
	public static function _validateDate ($val)
	{
		
		if (!self::_isDate ($val))
		{
			throw new Parser\Exception ('Invalid date: ' . $val);
		}
		
	}
	
	
	/**
	 * Check a date is a valid date YYYY-MM-DD HH:II:SS
	 * @param string $val Value to validate
	 * @throws Parser\Exception
	 * @return void 
	 */
	
	public static function _validateDateTime ($val)
	{
		
		if (!self::_isDateTime ($val))
		{
			throw new Parser\Exception ('Invalid date time: ' . $val);
		}
		
	}
	
	
	
	/**
	 * Check an email address is valid
	 * @param string $val Value to validate
	 * @return boolean
	 */
	
	public static function _isEmail ($val)
	{
		
		if (!preg_match ('/^[a-zA-Z0-9_\.\-\']+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+[^\.]$/', $val))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
		
	}
	
	
	/**
	 * Check a date is a valid date YYYY-MM-DD
	 * @param string $val Value to validate
	 * @return boolean 
	 */
	
	public static function _isDate ($val)
	{
		
		if (!preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2})$/', $val))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
		
	}
	
	
	/**
	 * Check a date is a valid date YYYY-MM-DD HH:II:SS
	 * @param string $val Value to validate
	 * @return boolean 
	 */
	
	public static function _isDateTime ($val)
	{
		
		if (!preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $val))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
		
	}
	
	
	/**
	 * Check a date is in UK format (DD-MM-YYYY)
	 * @param string $val Value to validate
	 * @return boolean 
	 */
	
	public static function _isUKDate ($val)
	{
		
		if (!preg_match ('/^(\d{2})([^\d]{1})(\d{2})([^\d]{1})(\d{2,4})$/', $val))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
		
	}
	
	
	/**
	 * Return a postcode area from a UK postcode
	 * e.g. MK18 3DS = MK18
	 * @param string $postcode UK Postcode
	 * @return string
	 */
	
	public static function _postcodeArea ($postcode)
	{
		
		$area	= strtoupper (str_replace (' ', '', trim ($postcode)));
		$length	= strlen ($area);

		if ($length == 5)
		{
			$area	= substr ($area, 0, 2);
		}
		else if ($length == 6)
		{
			$area	= substr ($area, 0, 3);
		}
		else if ($length >= 7)
		{
			$area	= substr ($area, 0, 4);
		}
		
		return $area;
		
	}
	
	
}

// End Parser Class