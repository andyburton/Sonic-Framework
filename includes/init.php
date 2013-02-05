<?php

// Define namespace

namespace Sonic;

// Directory seperator

if (!defined ('DS'))
{
	@define ('DS',	defined ('DIRECTORY_SEPARATOR')? DIRECTORY_SEPARATOR : '/');
}

// Require global paths

require_once ('paths.php');

// Setup config

require_once ('config.php');

// Require framework

require_once ('Sonic/Sonic.php');

// Set autoloader

Sonic::autoload ();

// Set framework resources

Sonic::newResource ('parser',		new Resource\Parser);

// Load config

require_once (CONFIG_DIR . 'config.php');