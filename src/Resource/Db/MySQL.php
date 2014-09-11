<?php

// Define namespace

namespace Sonic\Resource\Db;

// Start MySQL Class

class MySQL extends \Sonic\Resource\Db
{

	
	/**
	 * Instantiate class
	 * @param string $host Databse Host
	 * @param string $user Datbase Username
	 * @param string $pass Database Password
	 * @param string $db_name Database Name
	 * @param array $options Database Connection Options
	 */

	public function __construct ($host = FALSE, $user = FALSE, $pass = FALSE, $db_name = FALSE, $options = FALSE)
	{
		$this->setConnection ('mysql:host=' . $host . ';dbname=' . $db_name, $user, $pass, $options);
	}
	
	
}

// End MySQL Class