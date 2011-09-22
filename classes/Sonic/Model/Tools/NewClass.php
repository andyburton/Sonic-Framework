<?php

// Define namespace

namespace Sonic\Model\Tools;

// Start NewClass Class

class NewClass extends \Sonic\Model
{
	
	
	/**
	 * Class output
	 * @var string
	 */
	
	protected $class			= '';
	
	/**
	 * Tab indent count
	 * @var integer
	 */
	
	protected $tabCount			= 0;
	
	/**
	 * Tab indent value
	 * @var string
	 */
	
	protected $tabValue			= '';
	
	/**
	 * Whether to automatically add spaces during class generation
	 * @var boolean
	 */
	
	protected $autoSpaces		= TRUE;
	
	
	protected $minGap			= 22; // chars
	protected $classGap			= 8; // tabs
	
	/**
	 * Class attribute cache
	 * @var array
	 */
	
	protected $attributeCache	= FALSE;
	
	/**
	 * Column relationship cache
	 * @var array
	 */
	
	protected $relationCache	= FALSE;
	
	/**
	 * Class attributes
	 * @var array 
	 */
	
	protected static $attributes	= array (
		'namespace'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> ''
		),
		'name'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> ''
		),
		'pk'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE
		),
		'description'	=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> ''
		),
		'date_created'	=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> ''
		),
		'author'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> 'Andy Burton'
		),
		'email'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> 'andy@buddi.co.uk'
		),
		'link'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> 'http://www.buddi.co.uk'
		),
		'copyright'		=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> 'buddi'
		),
		'table'			=> array (
			'get'		=> TRUE,
			'set'		=> TRUE,
			'default'	=> ''
		)
	);
	
	
	
	public function __construct ()
	{
		
		parent::__construct ();
		
		$this->set ('date_created', date ('d/m/Y'));
		
	}
	
	
	/**
	 * Generate the class
	 * @return type string
	 */
	
	public function Generate ()
	{
		
		// If there is a table but no namespace or name
		
		if ($this->get ('table') && (!$this->get ('namespace') || !$this->get ('name')))
		{
			
			// Get namespace and name details from the table name
			
			list ($namespace, $class)	= $this->parser->convertToNamespaceAndClass ($this->get ('table'));
			
			// Set namespace and name
			
			if (!$this->get ('namespace'))
			{
				$this->set ('namespace', $namespace);
			}
			
			if (!$this->get ('name'))
			{
				$this->set ('name', $class);
			}
			
		}
		
		// Start script and define namespace
		
		$this->addLine ('<?php');
		$this->addLine ();
		$this->lineComment ('Define namespace');
		$this->addLine ();
		$this->addLine ('namespace ' . $this->get ('namespace') . ';');
		$this->addLine ();
		$this->addLine ();
		
		// Add class comment
		
		$comment	= 
			($this->get ('description')?	$this->get ('description') . "\n\n" : NULL) . 
			($this->get ('author')?			$this->tabVals ('@author',		$this->get ('author'), 2, '') . "\n" : NULL) . 
			($this->get ('email')?			$this->tabVals ('@email',		$this->get ('email'), 2, '') . "\n" : NULL) . 
			($this->get ('link')?			$this->tabVals ('@link',		$this->get ('link'), 2, '') . "\n" : NULL) . 
			($this->get ('copyright')?		$this->tabVals ('@copyright ',	$this->get ('copyright'), 2, '') . "\n" : NULL) . 
			($this->get ('date_created')?	$this->tabVals ('@datecreated',	$this->get ('date_created'), 2, '') . "\n" : NULL);
		
		if (substr ($comment, -1) == "\n")
		{
			$comment	= substr ($comment, 0, -1);
		}
		
		$this->phpdocComment ($comment);
		$this->addLine ();
		
		// Start class
		
//		$this->lineComment ('Start ' . $this->get ('name') . ' Class');
//		$this->addLine ();
		$this->addLine ('class ' . $this->get ('name') . ' extends \\Sonic\\Model');
		$this->addLine ('{');
		$this->tabUp ();
		$this->addLine ();
		
		// Database table

		if ($this->get ('table'))
		{
			$this->phpdocComment ("Database table\n@var string");
			$this->addLine ();
			$this->addLine ('public static $dbTable	= \'' . $this->get ('table') . '\';');
			$this->addLine ();
		}
		
		// Get primary key
		
		$pk	= $this->getPk ();
		
		// If there is a primary key which is different to the default
		
		if (is_array ($pk) && $pk['Field'] !== self::$pk)
		{
			
			// Set it
			
			$this->phpdocComment ("Primary key field\n@var string");
			$this->addLine ();
			$this->addLine ('public static $pk	= \'' . $pk['Field'] . '\';');
			$this->addLine ();
			
		}
		
		// Attributes
		
		$this->phpdocComment ("Class attributes\n@var array");
		$this->addLine ();
		$this->addLine ('protected static $attributes = array (');
		$this->tabUp (FALSE);
		
		// Get attributes and relationships
		
		$attributes	= $this->getAttributes ();
		
		// Add each attribute
		
		foreach ($attributes as $name => $attribute)
		{
			
			$this->addLine ($this->tabVals ('\'' . $name . '\'', 'array (', 4, '=> '));
			$this->tabUp (FALSE);
			
			foreach ($attribute as $key => $val)
			{
				
				switch ($key)
				{
					
					case 'charset':
					case 'relation':
						
						$val	= '\'' . $val . '\'';
						break;
					
					case 'valid':
					case 'values':
						
						$val	= 'array (' . $val . ')';
						break;
					
				}
				
				$this->addLine ($this->tabVals ('\'' . $key . '\'', $val . ',', 3, '=> '));
				
			}
			
			// Remove last ,

			if (substr ($this->class, -2) == ",\n")
			{
				$this->class	= substr ($this->class, 0, -2) . "\n";
			}
			
			$this->tabDown (FALSE);
			$this->addLine ('),');
			
		}
		
		// Remove last ,

		if (substr ($this->class, -2) == ",\n")
		{
			$this->class	= substr ($this->class, 0, -2) . "\n";
		}
		
		// End attributes
		
		$this->tabDown (FALSE);
		$this->addLine (');');
		$this->addLine ();
		
		// End class
		
		$this->tabDown ();
		$this->addLine ('}');
		$this->addLine ();
		$this->lineComment ('End ' . $this->get ('name') . ' Class');
		
		// Return class
		
		return $this->class;
		
	}
	
	
	/**
	 * Return class primary key
	 * @return array|boolean
	 */
	
	public function getPk ()
	{
		
		// If there is no database table return FALSE

		if (!$this->get ('table'))
		{
			return FALSE;
		}

		// Create new database tool

		$db	= new Db;

		// Get primary key field

		return $db->getPk ($this->get ('table'));
		
	}
	
	
	/**
	 * Return class attributes
	 * @return array
	 */
	
	public function getAttributes ()
	{
		
		// If there are no attributes
		
		if ($this->attributeCache === FALSE)
		{
				
			// Set default attributes
			
			$this->attributeCache	= array ();
			
			// If there is a database table
			
			if ($this->get ('table'))
			{
				
				// Create new database tool
				
				$db	= new Db;
				
				// For each column
				
				foreach ($db->getColumns ($this->get ('table')) as $column)
				{
					
					// Set attribute
					
					$attribute	= array (
						'get'	=> 'TRUE',
						'set'	=> 'TRUE'
					);
					
					$this->attributeCache[$column[0]]	= array_merge ($attribute, $db->parseColumn ($column));
					
				}
				
				// Get relationships
				
				$this->getRelations ();
				
			}
			
		}
		
		// Return attributes
		
		return $this->attributeCache;
		
	}
	
	
	/**
	 * Add attribute class relationships
	 * @return void
	 */
	
	public function getRelations ()
	{
		
		// Create new database tool

		$db	= new Db;
		
		$relations	= $db->getRelations ($this->get ('table'));

		// Loop through constraints

		foreach ($relations as $constraint)
		{
			
			// If the attribute exists
			
			if (isset ($this->attributeCache[$constraint['attribute']]))
			{
				
				// Add relationship
				
				$this->attributeCache[$constraint['attribute']]['relation']	= $this->parser->convertToClassName ($constraint['table']);
				
			}

		}
		
	}
	
	
	/**
	 * Add a new line to the class
	 * @param type $strLine Class line
	 * @return void
	 */
	
	public function addLine ($strLine = '')
	{
		
		// Add line to class
		
		$this->class	.= $this->getTabs () . $strLine . "\n";
		
	}
	
	
	/**
	 * Add a line comment to the class
	 * @param string $strComment The comment
	 * @return void
	 */
	
	public function lineComment ($strComment)
	{
		
		// Add comment
		
		$this->class	.= Comment::lineComment ($strComment, $this->tabCount);
		
		// If auto spaces
		
		if ($this->autoSpaces)
		{
			
			// Add blank line
			
			$this->addLine ();
			
		}
		
	}
	
	
	/**
	 * Add a star comment to the class
	 * @param string $strComment The comment
	 * @return void
	 */
	
	public function starComment ($strComment)
	{
		
		// Add comment
		
		$this->class	.= Comment::starComment ($strComment, $this->tabCount);
		
		// If auto spaces
		
		if ($this->autoSpaces)
		{
			
			// Add blank line
			
			$this->addLine ();
			
		}
		
	}
	
	
	/**
	 * Add a PHPDoc comment to the class
	 * @param string $strComment The comment
	 * @return void
	 */
	
	public function phpdocComment ($strComment)
	{
		
		// Add comment
		
		$this->class	.= Comment::phpdocComment ($strComment, $this->tabCount);
		
		// If auto spaces
		
		if ($this->autoSpaces)
		{
			
			// Add blank line
			
			$this->addLine ();
			
		}
		
	}
	
	
	/**
	 * Get the tab value
	 * @return string
	 */
	
	public function getTabs ()
	{
		
		// Return tabs
		
		return $this->tabValue;
		
	}
	
	
	/**
	 * Set the tab value
	 * @return void
	 */
	
	public function setTabString ()
	{
		
		// Set tabs
		
		$this->tabValue	= str_repeat ("\t", $this->tabCount);
		
	}
	
	
	/**
	 * Set tab count and generate value
	 * @param type $intTabs Tab Count
	 * @return void
	 */
	
	public function setTabs ($intTabs)
	{
		
		// Set tabs
		
		$this->tabCount	= $intTabs;
		
		// Set tab string
		
		$this->setTabString ();
		
	}
	
	
	/**
	 * Increment the tab count
	 * @param boolean $blnLine Line overide
	 * @return void
	 */
	
	public function tabUp ($blnLine = TRUE)
	{
		
		// Increment tabs
		
		$this->tabCount++;
		
		// Set tab string
		
		$this->setTabString ();
		
		// If line and auto spaces
		
		if ($blnLine && $this->autoSpaces)
		{
			
			// Add blank line
			
			$this->addLine ();
			
		}
		
	}
	
	
	/**
	 * Decrement the tab count
	 * @param boolean $blnLine Line overide
	 * @return void
	 */
	
	public function tabDown ($blnLine = TRUE)
	{
		
		// If line and auto spaces
		
		if ($blnLine && $this->autoSpaces)
		{
			
			// Add blank line
			
			$this->addLine ();
			
		}
		
		// Decrement tabs
		
		$this->tabCount--;
		
		// Set tab string
		
		$this->setTabString ();
		
	}
	
	
	/**
	 * Return A and B with tabs between them
	 * @param string $strA Value A
	 * @param string $strB Value B
	 * @param type $intTabs Tab Count
	 * @param string $strSeparator Value separator
	 * @return string
	 */
	
	public function tabVals ($strA, $strB, $intTabs = FALSE, $strSeparator = '= ')
	{
		
		// If no tabs
		
		if (!$intTabs)
		{
			
			// Default to class gap
			
			$intTabs	= $this->classGap;
			
		}
		
		// Set char length from tabs
		
		$intGapLength		= $intTabs * 4;
		
		// Work out additional space required
		
		$intGapAdditional	= $intGapLength - strlen ($strA);
		
		// Work out gap in tabs
		
		$intGapTabs			= ceil ($intGapAdditional / 4);
		
		// If the gaps are less than 1
		
		if ($intGapTabs < 1)
		{
			
			// Set to 1
			
			$intGapTabs	= 1;
			
		}
		
		// Return string
		
		return $strA . str_repeat ("\t", $intGapTabs) . $strSeparator . $strB;
		
	}
	
	
}

// End NewClass Class