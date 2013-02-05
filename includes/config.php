<?php

// Load config from environmental variable

@define ('CONFIG',		isset ($_SERVER['SONIC_CONFIG'])? $_SERVER['SONIC_CONFIG'] : FALSE);
@define ('CONFIG_DIR',	ABS_CONFIG . CONFIG . DS);

// Check config isset

if (!CONFIG)
{
	exit ('no config specified');
}

// Check core config files exists

if (!file_exists (CONFIG_DIR . 'config.php'))
{
	exit ('`' . CONFIG . '` config file does not exist');
}

if (!file_exists (CONFIG_DIR . 'paths.php'))
{
	exit ('`' . CONFIG . '` config paths file does not exist');
}

// Load config paths

require_once (CONFIG_DIR . 'paths.php');