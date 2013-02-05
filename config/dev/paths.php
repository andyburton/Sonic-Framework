<?php

/**
 * Global paths from includes/paths.php should already have been loaded
 */

// Absolute path to classes

@define ('ABS_CLASSES',				ABS_ROOT . 'classes' . DS);
@define ('ABS_SONIC',				ABS_PARENT . 'sonic_framework' . DS . 'classes' . DS);
@define ('ABS_LOG',					ABS_ROOT . 'log' . DS);

// Add class directories and config dir to the php include path

set_include_path (implode (PATH_SEPARATOR, array (
	ABS_CLASSES,
	ABS_SONIC,
	get_include_path ()
)));