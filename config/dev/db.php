<?php

// Define namespace

namespace Sonic;

// Define database settings directory

@define ('CONFIG_DB_DIR',	 CONFIG_DIR . 'db' . DS);

// Load settings

require_once (CONFIG_DB_DIR . 'default.php');

// Set database resources

Sonic::newResource ('db',	new Resource\Db\DefaultConnection);