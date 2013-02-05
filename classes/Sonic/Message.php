<?php

// Define namespace

namespace Sonic;

// Start Model Class

class Message
{
	
	
	/**
	 * Messages indexed by status
	 * @var array
	 */
	
	public static $messages	= array ();
	
	
	/**
	 * Set a new message
	 * @param mixed $status Message status
	 * @param string $msg Message string
	 */
	
	public function __construct ($status, $msg)
	{
		self::$messages[$status][]	= $msg;	
	}
	
	
	/**
	 * Return messages
	 * @param mixed $status Message status to return
	 * @return array
	 */
	
	public static function get ($status = FALSE)
	{
		
		// If no status return all messages
		
		if ($status === FALSE)
		{
			return self::$messages;
		}
		
		// Return messages for a specific status
		
		if (isset (self::$messages[$status]))
		{
			return self::$messages[$status];
		}
		else
		{
			return array ();
		}
		
	}
	
	
	/**
	 * Return messages as a string
	 * @param mixed $status Message status to return
	 * @param string $tagStart Opening tag for each message
	 * @param string $tagEnd Closing tag for each message
	 * @return string
	 */
	
	public static function getString ($status = FALSE, $tagStart = NULL, $tagEnd = "\n")
	{
		
		// Get messages
		
		$arr	= self::get ($status);
		
		// Set message string
		
		$str	= '';
		
		foreach ($arr as $msg)
		{
			$str	.= $tagStart . $msg . $tagEnd;
		}
		
		// Return message
		
		return $str;
		
	}
	
	
	/**
	 * Reset messages
	 * @param mixed $status Message status to reset
	 * @return void
	 */
	
	public static function reset ($status = FALSE)
	{
		
		// If no status reset all messages
		
		if ($status === FALSE)
		{
			self::$messages	= array ();
		}
		
		// Reset messages for a specific status
		
		if (isset (self::$messages[$status]))
		{
			self::$messages[$status]	= array ();
		}
		
	}

	
	/**
	 * Return number of messages
	 * @param mixed $status Message status to count
	 * @return integer
	 */
	
	public static function count ($status = FALSE)
	{
		
		// If no status return all messages
		
		if ($status === FALSE)
		{
			return count (self::$messages);
		}
		
		// Return messages for a specific status
		
		if (isset (self::$messages[$status]))
		{
			return count (self::$messages[$status]);
		}
		else
		{
			return 0;
		}
		
	}
	
	
}

// End Message Class