<?php

// Define namespace

namespace Sonic;

// Require paths

require_once ('paths.php');

// Define smarty template path

@define ('ABS_SMARTY',	ABS_PARENT . 'smarty-repo' . DS);

// Require smarty class

require_once (ABS_SMARTY . 'libs' . DS . 'Smarty.class.php');

// Set template resource

Sonic::newResource ('tpl',	new \Smarty);

// Get template resource

$tpl	= Sonic::getResource ('tpl');

// Set smarty config

$tpl->setTemplateDir (ABS_SMARTY . 'templates');
$tpl->setCacheDir (ABS_SMARTY . 'cached');
$tpl->setCompileDir (ABS_SMARTY . 'compiled');
$tpl->setConfigDir (ABS_SMARTY . 'config');
$tpl->addPluginsDir (ABS_SMARTY . 'plugins');

// Disable caching

$tpl->caching	= FALSE;