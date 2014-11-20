<?php

/**
 * Collection class for group of \Sonic\Model objects
 */

// Define namespace

namespace Sonic\Resource\Model;

// Start Collection Class

class Collection extends \ArrayObject
{
	
	
	/**
	 * Magic call method to catch undefined methods
	 * Used to allow global array functions on collection
	 * @param string $function Function name
	 * @param array $args Function arguments
	 * @return mixed
	 * @throws \BadMethodCallException 
	 */
	
	public function __call ($function, $args)
	{
		
		// Callable array function
		
		if (is_callable ($function, TRUE) && substr ($function, 0, 6) == 'array_')
		{
			return call_user_func_array ($function, array_merge (array ($this->getArrayCopy ()), $args));
		}
		
		// Model method
		
		elseif (is_callable (array ('Sonic\Model', $function)))
		{
			
			$arr	= array ();
			$it		= $this->getIterator ();
			$new	= TRUE;

			while ($it->valid ())
			{
				
				$result = call_user_func_array (array ($it->current (), $function), $args);
				
				if ($new && $result instanceof \Sonic\Model)
				{
					$arr = new self;
				}
				
				$new = FALSE;
				
				$arr[$it->key ()]	= $result;
				$it->next ();
				
			}

			return $arr;
			
		}
		
		// Bad method
		
		else
		{
			throw new \BadMethodCallException (__CLASS__ . '->' . $function);
		}
		
	}
	
	
	/**
	 * Return random collection item
	 * @return \Sonic\Model|FALSE
	 */
	
	public function random ()
	{
		$arr	= $this->getArrayCopy ();
		$rand	= array_rand ($arr);
		return isset ($arr[$rand])? $arr[$rand] : FALSE;
	}
	
	
	/**
	 * Return a multidimensional array with objects and their attributes
	 * @param array|boolean $attributes Attributes to include, default to false i.e all attributes
	 * @param array $relations Array of related object attributes or tranformed method attributes to return
	 *   e.g. related value - 'query_name' => array ('\Sonic\Model\User\Group', 'name')
	 *   e.g. object tranformed value - 'permission_value' => array ('$this', 'getStringValue')
	 *   e.g. static tranformed value - 'permission_value' => array ('self', '_getStringValue')
	 * @param integer $recursive Output array recursively, so any $this->children also get output
	 * @return object|boolean
	 */
	
	public function toArray ($attributes = FALSE, $relations = array (), $recursive = FALSE)
	{
		
		$arr	= array ();
		$it		= $this->getIterator ();
		
		while ($it->valid ())
		{
			$arr[$it->key ()] = $it->current ()->toArray ($attributes, $relations, $recursive);
			$it->next ();
		}
		
		return $arr;
		
	}
	
	
}