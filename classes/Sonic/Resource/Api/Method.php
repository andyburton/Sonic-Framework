<?php

// Define namespace

namespace Sonic\Resource\Api;

// Start Method Class

class Method extends \Sonic\Model
{
	
	
	/**
	 * Create the object
	 * @param array $exclude Attributes not to set
	 * @return boolean
	 */
	
	public function Create ($exclude = array ())
	{
		
		// Get the next value if there is none
		
		if (!$this->iget ('value'))
		{
			
			$this->iset ('value', 1 + (int)$this->getValue (array (
				'select'	=> 'MAX(value)',
				'where'		=> array (
					array ('module_id', $this->iget ('module_id'))
				)
			)));
			
		}
		
		// Call parent
		
		return parent::Create ($exclude);
		
	}
	
	
}

// End Method Class