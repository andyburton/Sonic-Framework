<?php

// Define namespace

namespace Sonic;

// Database Settings

class MySQL extends Resource\Db\MySQL
{
	
	protected $_host	= '127.0.0.1';
	protected $_user	= '';
	protected $_pass	= '';
	protected $_db_name	= '';
	
	protected $_options	= array (
		\PDO::ATTR_ERRMODE					=> \PDO::ERRMODE_EXCEPTION,
		\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> TRUE
	);

}

// Set database

Sonic::newResource ('db',	new MySQL);