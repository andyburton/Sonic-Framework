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
	
	public function getCharset ($name)
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
	
	public function Validate ($criteria, $val)
	{
		
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
				throw new Parser\Exception ('Invalid NULL value');
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
				throw new Parser\Exception ('Invalid value: ' . $val);
			}
			
		}
		
		// If there is a charset to validate against
		
		if (isset ($criteria['charset']))
		{
			
			// Get charset
			
			$charset	= $this->getCharset($criteria['charset']);
			
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
					throw new Parser\Exception ('Invalid character: ' . $val[$i]);
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
						throw new Parser\Exception ('Minimum value is ' . (float)$criteria['min']);
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
						throw new Parser\Exception ('Minimum length is ' . (int)$criteria['min']);
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
				case \Sonic\Model::TYPE_INT_UNSIGNED:
				case \Sonic\Model::TYPE_DECIMAL:
					
					// Cast to float

					settype ($val, 'float');
					
					// If not valid return FALSE
					
					if ($val > (float)$criteria['max'])
					{
						throw new Parser\Exception ('Maximum value is ' . (float)$criteria['max']);
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
						throw new Parser\Exception ('Maximum length is ' . (int)$criteria['max']);
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
				
				$function	= 'validate' . ucfirst ($function);
				
				// Check the method exists
				
				if (!method_exists ($this, $function))
				{
					throw new Parser\Exception ('Invalid validation function: ' . $function);
				}
				
				// Call the method
				
				call_user_func (array ($this, $function), $val);
				
			}
			
		}
		
	}
	
	
	/**
	 * Convert a table name class name
	 * @param string $table Table name
	 * @return string
	 */
	
	public function convertToClassName ($table)
	{
		
		$arr		= explode ('_', $table);
		
		foreach ($arr as &$val)
		{
			$val = ucfirst (strtolower ($val));
		}
		
		return 'Sonic\\Model\\' . implode ('\\', $arr);
		
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
	 * Convert a date from UK format (DD-MM-YYYY) to MySQL format (YYYY-MM-DD)
	 * @param string $str Date to convert
	 * @return string
	 */
	
	public function convertUKDate ($str)
	{
		return preg_replace ('/^(\d{2})([^\d]{1})(\d{2})([^\d]{1})(\d{2,4})$/', '$5-$3-$1', $str);
	}
	
	
	/**
	 * Convert a date or datetime into a unix timestamp
	 * @param string $str Date to convert
	 * @return type 
	 */
	
	public function convertToUnixtime ($str)
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
	
	public function toISO ($str)
	{
		
		return mb_convert_encoding ($str, 'ISO-8859-1', mb_detect_encoding ($str, 'UTF-8, ISO-8859-1, ISO-8859-15', TRUE));
		
	}
	
	
	
	/**
	 * Custom functions
	 * Should all start with 'validate' followed by the function name
	 * e.g. 'email' would be 'validateEmail'
	 */
	
	
	/**
	 * Check an email address is valid
	 * @param string $val Value to validate
	 * @throws Parser\Exception
	 * @return void
	 */
	
	public function validateEmail ($val)
	{
		
		if (!preg_match ('/^[a-zA-Z0-9_\.\-\']+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+[^\.]$/', $val))
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
	
	public function validateDate ($val)
	{
		
		if (!preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2})$/', $val))
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
	
	public function validateDateTime ($val)
	{
		
		if (!preg_match ('/^(\d{4})\D{1}(\d{2})\D{1}(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $val))
		{
			throw new Parser\Exception ('Invalid date time: ' . $val);
		}
		
	}
	
	
}

// End Parser Class