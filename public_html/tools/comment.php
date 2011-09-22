<?php

// Set namespace

namespace Sonic;

// Initialise framework

require_once ('../../includes/init.php');

// Load smarty

require_once ('tools/smarty.php');

// Set comment variables

$comment		= '';
$commentClean	= '';

// If the form is submitted

if (isset ($_POST['create_comment']))
{
	
	// Set clean comment
	
	$commentClean	= Model\Tools\Comment::cleanComment ($_POST['comment']);
	
	// Switch the type
	
	switch ($_POST['comment_type'])
	{
		
		// Line comment
		
		case 'line':
			
			$comment	= Model\Tools\Comment::lineComment ($_POST['comment']);
			
			break;
			
		
		// PHPDoc comment
		
		case 'phpdoc':
			
			$comment	= Model\Tools\Comment::phpdocComment ($_POST['comment']);
			
			break;
		
		
		// Star comment
		
		case 'star':
		default:
			
			$comment	= Model\Tools\Comment::starComment ($_POST['comment']);
			
			break;
			
	}
	
}

// Set comment

$tpl->assign ('comment',		$comment);
$tpl->assign ('clean_comment',	$commentClean);

// Display

$tpl->display ('comment.tpl');
