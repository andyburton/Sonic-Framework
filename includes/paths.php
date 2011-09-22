<?php

// Define namespace

namespace Sonic;

// Absolute paths

@define ('ABS_ROOT',				realpath (__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
@define ('ABS_INCLUDES',			__DIR__ . DIRECTORY_SEPARATOR);
@define ('ABS_CLASSES',				ABS_ROOT . 'classes' . DIRECTORY_SEPARATOR);

// Add includes and classes directories to the php include path

set_include_path (implode (PATH_SEPARATOR, array (
	ABS_INCLUDES,
	ABS_CLASSES,
	get_include_path ()
)));