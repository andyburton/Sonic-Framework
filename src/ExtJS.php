<?php

/**
 * Useful methods to return data formatted for the ExtJS front-end.
 */

// Define namespace

namespace Sonic;

// Start ExtJS Class

class ExtJS
{
	
	/**
	 * Return an array for use in an Ext grid in the format:
	 * array(
	 * 	'success'	=> true/false,
	 *	'total'		=> total number of rows,
	 *	'rows'		=> array of results
	 *	'msg'		=> error message if success is false
	 * )
	 *
	 * @param string $class Model class name of data to return
	 * @param array $params Array of query parameters - MUST BE ESCAPED! 
	 * @return array
	 */

	public static function _getGrid ($class, $params = array ())
	{
		
		// If the class doesn't exist
		
		if (!class_exists ($class))
		{
			throw new Exception ('Class does not exist: ' . $class);
		}
		
		// If the class doesn't extend the model
		
		if (!array_key_exists ('Sonic\Model', class_parents ($class)))
		{
			throw new Exception ('Class ' . $class . ' must extend \Sonic\Model');
		}
		
		// If no limit has been set

		if (!$params || !isset ($params['limit']))
		{

			// Set default query limit
			
			$params['limit'] = array (0, 50);

		}
		
		// Get data
		
		$data	= $class::_getValues ($params);
		
		// Get count
		
		$count	= $class::_Count ($params);

		// Set result array

		if (FALSE !== $count && FALSE !== $data)
		{
			
			// Set result

			$result = array (
				'success'	=> TRUE,
				'total'		=> $count,
				'rows'		=> $data
			);

		}
		else
		{

			// Set error JSON

			$result = array (
				'success'	=> FALSE,
				'total'		=> '0',
				'rows'		=> array (),
				'msg'		=> Message::getString ('error')
			);

		}

		// Return result
		
		return $result;

	}

	
}