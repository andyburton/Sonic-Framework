<?php

// Define namespace

namespace Sonic;

// Require paths

require_once ('paths.php');

// Check for smarty config

if (file_exists (CONFIG_DIR . 'smarty.php'))
{
	require_once (CONFIG_DIR . 'smarty.php');
}
else
{
	exit ('`' . CONFIG . '` smarty config does not exist');
}