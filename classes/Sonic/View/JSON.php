<?php

// Define namespace

namespace Sonic\View;

// Start JSON Class

class JSON
{
	
	
	/**
	 * Response to be returned
	 * @var array
	 */
	
	public $response	= array ();
	
	
	/**
	 * Display a template
	 * @param boolean $output Whether to output the rendered template
	 * @return string
	 */
	
	public function display ($output = TRUE)
	{
		
		// JSON encode
		
		$response	= json_encode ($this->response);
		
		// Output
		
		if ($output)
		{
			
			// Output headers

			header ('Cache-Control: no-cache, must-revalidate');
			header ('Content-type: application/json; charset=utf-8');
			
			// Output response
			
			echo $response;
			
		}
		
		// Return
		
		return $response;
		
	}
	
	
}