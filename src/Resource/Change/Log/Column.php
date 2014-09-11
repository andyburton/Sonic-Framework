<?php

// Define namespace

namespace Sonic\Resource\Change\Log;

// Start Column Class

class Column extends \Sonic\Model
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
			'max'		=> self::INT_MAX_UNSIGNED,
			'default'	=> 0
		),
		'change_log_id'	=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_INT,
			'charset'	=> 'int_unsigned',
			'min'		=> self::INT_MIN_UNSIGNED,
			'max'		=> self::INT_MAX_UNSIGNED,
			'default'	=> 0,
			'relation'	=> 'Sonic\Model\Change\Log'
		),
		'name'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'charset'	=> 'default',
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> ''
		),
		'old_value'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
			'min'		=> self::SMALLINT_MIN_UNSIGNED,
			'max'		=> 1000,
			'default'	=> NULL,
			'null'		=> TRUE
		),
		'new_value'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'type'		=> self::TYPE_STRING,
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

// End Column Class
