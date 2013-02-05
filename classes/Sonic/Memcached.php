<?php

// Define namespace

namespace Sonic;

// Start Model Class

class Memcached extends \Sonic\Model
{
	
	/**
	 * Model memcached resource
	 * @var Memcached
	 */

	public $memcached			= FALSE;
	
	
	/**
	 * Create object in the database and set in memcached
	 * @param array $exclude Attributes not to set
	 * @param integer|boolean $cache Number of seconds to cache the object for
	 *   0 = cache never expires, FALSE = do not cache
	 * @return boolean
	 */
	
	public function create ($exclude = array (), $cache = FALSE)
	{
		
		// If we're not caching or there is no memcached object just call parent
		
		if ($cache === FALSE || !($this->memcached instanceof \Memcached))
		{
			return parent::create ($exclude);
		}
		
		// Call parent method
		
		$parent	= parent::create ($exclude);
		
		if ($parent === FALSE)
		{
			return FALSE;
		}
		
		// Cache
		
		$this->memcached->set ($this->getMemcachedID (), $this->getCacheAttributes (), $cache);
		
		// Return
		
		return $parent;
		
	}
	
	
	/**
	 * Read an object from memcached if it exists, 
	 * otherwise load the object from the database and then cache it
	 * @param mixed $pkValue Primary key value
	 * @param integer|boolean $cache Number of seconds to cache the object for
	 *   0 = cache never expires, FALSE = do not cache
	 * @return boolean
	 */
	
	public function read ($pkValue = FALSE, $cache = FALSE)
	{
		
		// If we're not caching or there is no memcached object just call parent
		
		if ($cache === FALSE || !($this->memcached instanceof \Memcached))
		{
			return parent::read ($pkValue);
		}
		
		// If there is a key value passed set it
		
		if ($pkValue !== FALSE)
		{
			$this->iset (static::$pk, $pkValue);
		}
		
		// Load memcached object attributres
		
		$obj	= $this->memcached->get ($this->getMemcachedID ());
		
		// If the object is not cached, load and cache it
		
		if ($obj === FALSE)
		{
			
			// Read object
			
			if (parent::read ($pkValue) === FALSE)
			{
				return FALSE;
			}
			
			// Cache object attributes
			
			$this->memcached->set ($this->getMemcachedID (), $this->getCacheAttributes (), $cache);
			
		}
		
		// Set object attributes from the cache
		
		else
		{
			$this->setCacheAttributes ($obj);
		}
		
		// Return
		
		return TRUE;
		
	}
	
	
	/**
	 * Update an object in the database and set in memcached
	 * @param array $exclude Attributes not to update
	 * @param integer|boolean $cache Number of seconds to cache the object for
	 *   0 = cache never expires, FALSE = do not cache
	 * @return boolean
	 */
	
	public function update ($exclude = array (), $cache = FALSE)
	{
		
		// If we're not caching or there is no memcached object just call parent
		
		if ($cache === FALSE || !($this->memcached instanceof \Memcached))
		{
			return parent::update ($exclude);
		}
		
		// Call parent method
		
		$parent	= parent::update ($exclude);
		
		if ($parent === FALSE)
		{
			return FALSE;
		}
		
		// Cache
		
		$this->memcached->set ($this->getMemcachedID (), $this->getCacheAttributes (), $cache);
		
		// Return
		
		return $parent;
		
	}
	
	
	
	/**
	 * Delete an object in the database and memcached
	 * @param mixed $pkValue Primary key value
	 * @return boolean
	 */
	
	public function delete ($pkValue = FALSE)
	{
		
		// If there is no key value passed set it
		
		if ($pkValue === FALSE)
		{
			$pkValue	= $this->iget (static::$pk);
		}
		
		// Call parent method
		
		$parent	= parent::delete ($pkValue);
		
		if ($parent === FALSE)
		{
			return FALSE;
		}
		
		// If there is a memcached object delete from the cache

		if ($this->memcached instanceof \Memcached)
		{
			$this->memcached->delete (static::_getMemcachedID ($pkValue));
		}
		
		// Return
		
		return $parent;
		
	}
	
	
	/**
	 * Generate an object specific memcached ID for the object
	 * @return string
	 */
	
	public function getMemcachedID ()
	{
		return get_called_class () . '-' . $this->get (self::$pk);
	}
	
	
	/**
	 * Generate an memcached ID for the object given an object ID
	 * @return string
	 */
	
	public static function _getMemcachedID ($id)
	{
		return get_called_class () . '-' . $id;
	}
	
	
	/**
	 * Return object attributes to cache
	 * @return array
	 */
	
	private function getCacheAttributes ()
	{
		
		$attributes	= array ();

		foreach (array_keys (self::$attributes) as $name)
		{
			$attributes[$name]	= $this->iget ($name);
		}
		
		return $attributes;
		
	}
	
	
	/**
	 * Set object attributes from cache
	 * @return array
	 */
	
	private function setCacheAttributes ($arr)
	{

		foreach ($arr as $name => $val)
		{
			$this->iset ($name, $val, FALSE);
		}
		
	}
	
	
}