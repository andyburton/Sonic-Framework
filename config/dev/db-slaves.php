<?php

// Define namespace

namespace Sonic;

// Load settings

require_once (CONFIG_DB_DIR . 'default.php');

// Set database resources

Sonic::newResources ('db-slave', array (
	new Resource\Db\MySQL\Slave (DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME),
	new Resource\Db\MySQL\Slave (DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME)
), FALSE);