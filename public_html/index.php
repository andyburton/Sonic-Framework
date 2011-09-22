<?php

// Set namespace

namespace Sonic;

// Sonic Framework

require_once ('../includes/init.php');

// Create object

//$params	= array (
//	'where'	=> array (
//		array ('email', 'andy@andyburton.co.uk')
//	)
//);

//$obj	= Model\User::_read ($params);
//Model::pre ($obj);

//$var = $obj->getResource ('var');

//Model::pre (Model\User::_toArray (FALSE, array ('test'))); exit;

//$xml = Model\User::_toXML (array (), array ('id', 'email'));
//$xml	= $obj->toXML ();
//header ('Content-Type: application/xml');
//echo $xml->saveXML (); exit;

//header ('Content-Type: application/json');
//echo Model\User::_toJSON (array (), array ('email'), TRUE);

//$obj	= new Model\User;
//
//$obj->set ('email', 'test', FALSE);

//$paths		= Model\A::_getRelationPaths ('Sonic\Model\C');
//$shortest	= Model\A::_getShortestPath ($paths);
//
//Model::pre ($paths);
//Model::pre ($shortest);
//exit;

//$a	= Model\A::_read (1);
//$c	= $a->getRelated ('Sonic\Model\C', array ('Sonic\Model\B' => 'c2'));
//Model::pre ($c);