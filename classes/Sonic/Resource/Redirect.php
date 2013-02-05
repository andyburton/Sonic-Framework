<?php

// Define namespace

namespace Sonic\Resource;

// Start Redirect Class

class Redirect
{
	
	
	/**
	 * Redirect user with optional URL arguments
	 * @param string $url URL to redirect to
	 * @param array $args Arguments
	 * @return void
	 */
	
	public function __construct ($url, $args = FALSE)
	{
		
		// Check arguments are in array
		
		if ($args && !is_array ($args))
		{
			
			// Exit with error message
			
			exit ('Cannot redirect to ' . $url . ': You must pass your arguments as an array!');
			
		}
		
		// If location isnt empty
		
		if (!empty ($url))
		{
			
			// Set argument variables
			
			$urlArgs	= NULL;
			
			// If arguments
			
			if ($args)
			{
				
				// Generate arguments
				
				foreach ($args as $name => $val)
				{
					
					$urlArgs .= $name . '=' . urlencode ($val) . '&';
					
				}
				
			}
			
			// If there are any arguments, add the ? to the start of the argument string
			
			if ($urlArgs)
			{
				$urlArgs	= '?' . $urlArgs;
			}
			
			// Redirect using headers
			
			header ('Location: ' . $url . $urlArgs);
			
			// exit script, as we're moving on!
			
			exit();
			
		}
		else
		{
			
			// Exit, as there is no location to go to
			
			exit ('Cannot Redirect - Invalid Location: '  . $url);
			
		}
		
	}
	
	
}

// End Redirect Class