<?php

// Define namespace

namespace Sonic\Resource\Db\MySQL;

// Define class

class Slave extends \Sonic\Resource\Db\MySQL
{
	
	public function __construct ($host, $user, $pass, $db, $options = array ())
	{
		parent::__construct ($host, $user, $pass, $db, $options + array (
			\PDO::ATTR_ERRMODE					=> \PDO::ERRMODE_EXCEPTION,
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> TRUE,
			\PDO::ATTR_TIMEOUT					=> 1
		));
	}

}