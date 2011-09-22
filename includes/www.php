<?php

// Define namespace

namespace Sonic;

// Require setttings

require_once ('paths.php');

// Absolute paths

@define ('ABS_WWW',					ABS_ROOT . 'public_html' . DIRECTORY_SEPARATOR);

// URLs

@define ('URL_ROOT',				'http://' . $_SERVER['HTTP_HOST'] . '/');
@define ('URL_ROOT_SSL',			'https://' . $_SERVER['HTTP_HOST'] . '/');