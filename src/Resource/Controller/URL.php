<?php

// Define namespace

namespace Sonic\Resource\Controller;

// Start URL Class

abstract class URL
{
	
	public $controller	= '';
	public $action		= FALSE;
	
	abstract public function Process ();
	abstract public function findController ();
	abstract public function createController ();
	
}