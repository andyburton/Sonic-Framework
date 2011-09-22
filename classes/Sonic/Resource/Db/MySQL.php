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
		
		$this->_host	= $host?: $this->_host;
		$this->_user	= $user?: $this->_user;
		$this->_pass	= $pass?: $this->_pass;
		$this->_db_name	= $db_name?: $this->_db_name;
		$this->_options	= $options?: $this->_options;
		
		$this->_dsn		= 'mysql:host=' . $this->_host . ';dbname=' . $this->_db_name;
		
	}
	
	
}

// End MySQL Class