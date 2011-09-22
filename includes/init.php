<?php

// Define namespace

namespace Sonic;

// Require settings

require_once ('www.php');

// Require framework

require_once ('Sonic/Sonic.php');

// Set autoloader

Sonic::autoload ();

// Set framework resources

Sonic::newResource ('parser',		new Resource\Parser);

// Require database

require_once ('db.php');