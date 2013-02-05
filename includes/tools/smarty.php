<?php

// Define namespace

namespace Sonic;

// Require tool settings

require_once ('tools.php');

// Require smarty class

require_once (ABS_PARENT . 'smarty-repo/libs/Smarty.class.php');

// Set template resource

Sonic::newResource ('tpl',	new \Smarty);

// Get template resource

$tpl	= Sonic::getResource ('tpl');

// Set smarty config

$tpl->setTemplateDir (ABS_TOOLS_SMARTY . 'templates');
$tpl->setCacheDir (ABS_TOOLS_SMARTY . 'cached');
$tpl->setCompileDir (ABS_TOOLS_SMARTY . 'compiled');
$tpl->setConfigDir (ABS_TOOLS_SMARTY . 'config');
$tpl->addPluginsDir (ABS_TOOLS_SMARTY . 'plugins');

// Disable caching

$tpl->caching	= FALSE;