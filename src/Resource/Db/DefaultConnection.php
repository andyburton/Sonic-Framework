<?php

// Define namespace

namespace Sonic\Resource\Db;

// Define DefaultConnection class

class DefaultConnection extends MySQL
{
	
	public function __construct ()
	{
		parent::__construct (DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, array (
			\PDO::ATTR_ERRMODE					=> \PDO::ERRMODE_EXCEPTION,
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> TRUE
		));
	}

}