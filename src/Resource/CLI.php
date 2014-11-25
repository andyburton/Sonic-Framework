<?php

// Define namespace

namespace Sonic\Resource;

// Start CLI Class

class CLI
{
	
	
	/**
	 * Output a log entry
	 * @param string $status Log status e.g. error, success, debug
	 * @param string $msg Message to output
	 * @param boolean $nl Add newline to the output
	 * @return void
	 */
	
	public static function Output ($status, $msg, $nl = TRUE)
	{
		echo '[' . date ('Y-m-d H:i:s') . '][' . strtoupper ($status) . '] ' . $msg . ($nl? "\n" : NULL);
	}
	
	
}

// End CLI Class