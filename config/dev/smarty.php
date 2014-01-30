<?php

// Require smarty class

require_once (ABS_PARENT . 'smarty-repo/Smarty.class.php');

// Define smarty template path

@define ('ABS_SMARTY',	ABS_ROOT . 'smarty' . DS);

// Define smarty directories

@define ('SONIC_SMARTY_TEMPLATE_DIR',		ABS_SMARTY . 'templates');
@define ('SONIC_SMARTY_CACHE_DIR',			ABS_SMARTY . 'cached');
@define ('SONIC_SMARTY_COMPILE_DIR',		ABS_SMARTY . 'compiled');
@define ('SONIC_SMARTY_CONFIG_DIR',			ABS_SMARTY . 'config');
@define ('SONIC_SMARTY_PLUGINS_DIR',		ABS_SMARTY . 'plugins');
@define ('SONIC_SMARTY_ERROR_REPORTING',	E_ALL & ~E_NOTICE);