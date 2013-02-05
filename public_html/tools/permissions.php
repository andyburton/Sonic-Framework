<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set modules

$tpl->assign ('modules', Model\Api\Module::_getObjects (array ('orderby' => 'name')));

// Display

$tpl->display ('permissions.tpl');
