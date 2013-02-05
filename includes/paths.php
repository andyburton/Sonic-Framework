<?php

// Absolute paths

@define ('ABS_ROOT',				realpath (__DIR__ . DS . '..') . DS);
@define ('ABS_PARENT',				realpath (ABS_ROOT . DS . '..') . DS);
@define ('ABS_INCLUDES',			__DIR__ . DS);
@define ('ABS_CONFIG',				ABS_ROOT . 'config' . DS);

// Add config directory to the php include path

set_include_path (implode (PATH_SEPARATOR, array (
	ABS_INCLUDES,
	get_include_path ()
)));