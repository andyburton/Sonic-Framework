<?php

// Define namespace

namespace Sonic\Resource\Audit\Log;

// Start Result Class

class Result extends \Sonic\Model
{
	
	/**
	 * Class attributes
	 * @var array
	 */	
	
	protected static $attributes = array (
		'id'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> 1,
			'max'		=> self::SMALLINT_MAX_UNSIGNED,
			'default'	=> 0
		),
		'name'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> NULL,
			'null'		=> TRUE
		)
	);
	
	/**
	 * Dont write changelog
	 * @var boolean 
	 */
	
	protected static $changelogIgnore	= TRUE;
	
}

// End Result Class
