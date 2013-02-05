<?php

// Define namespace

namespace Sonic\Model\Tools;

// Start Db Class

class Db extends \Sonic\Model
{
	
	/**
	 * Databases
	 * @var array
	 */
	
	protected $databases		= FALSE;
	
	/**
	 * Database tables
	 * @var array
	 */
	
	protected $tables			= FALSE;
	
	/**
	 * Table columns
	 * @var array
	 */
	
	protected $columns			= FALSE;
	
	/**
	 * Table column relationships
	 * @var array
	 */
	
	protected $relations		= FALSE;
	
	// Set the column map
	// Data type, minimum value, maximum value

	protected static $columnMap	= array
	(
		'bigint'		=> array ('self::TYPE_INT',			'self::BIGINT_MIN',				'self::BIGINT_MAX'),
		'binary'		=> array ('self::TYPE_BINARY',		'self::TINYINT_MIN_UNSIGNED',	'self::TINYINT_MAX_UNSIGNED'),
		'blob'			=> array ('self::TYPE_BINARY',		'self::SMALLINT_MIN_UNSIGNED',	'self::SMALLINT_MAX_UNSIGNED'),
		'bool'			=> array ('self::TYPE_BOOL',		0,								1),
		'char'			=> array ('self::TYPE_STRING',		'self::TINYINT_MIN_UNSIGNED',	'self::TINYINT_MAX_UNSIGNED'),
		'date'			=> array ('self::TYPE_DATE',		0,								10),
		'datetime'		=> array ('self::TYPE_DATETIME',	0,								19),
		'decimal'		=> array ('self::TYPE_DECIMAL'),
		'double'		=> array ('self::TYPE_DECIMAL'),
		'enum'			=> array ('self::TYPE_ENUM'),
		'float'			=> array ('self::TYPE_DECIMAL'),
		'int'			=> array ('self::TYPE_INT',			'self::INT_MIN',				'self::INT_MAX'),
		'longblob'		=> array ('self::TYPE_BINARY',		'self::INT_MIN_UNSIGNED',		'self::INT_MAX_UNSIGNED'),
		'longtext'		=> array ('self::TYPE_STRING',		'self::INT_MIN_UNSIGNED',		'self::INT_MAX_UNSIGNED'),
		'mediumblob'	=> array ('self::TYPE_BINARY',		'self::MEDIUMINT_MIN_UNSIGNED',	'self::MEDIUMINT_MAX_UNSIGNED'),
		'mediumint'		=> array ('self::TYPE_INT',			'self::MEDIUMINT_MIN',			'self::MEDIUMINT_MAX'),
		'mediumtext'	=> array ('self::TYPE_STRING',		'self::MEDIUMINT_MIN_UNSIGNED',	'self::MEDIUMINT_MAX_UNSIGNED'),
		'smallint'		=> array ('self::TYPE_INT',			'self::SMALLINT_MIN',			'self::SMALLINT_MAX'),
		'text'			=> array ('self::TYPE_STRING',		'self::SMALLINT_MIN_UNSIGNED',	'self::SMALLINT_MAX_UNSIGNED'),
		'time'			=> array ('self::TYPE_STRING',		0,								10),
		'timestamp'		=> array ('self::TYPE_STRING',		0,								19),
		'tinyblob'		=> array ('self::TYPE_BOOL',		'self::TINYINT_MIN_UNSIGNED',	'self::TINYINT_MAX_UNSIGNED'),
		'tinyint'		=> array ('self::TYPE_INT',			'self::TINYINT_MIN',			'self::TINYINT_MAX'),
		'tinytext'		=> array ('self::TYPE_STRING',		'self::TINYINT_MIN_UNSIGNED',	'self::TINYINT_MAX_UNSIGNED'),
		'varchar'		=> array ('self::TYPE_STRING',		'self::SMALLINT_MIN_UNSIGNED',	'self::SMALLINT_MAX_UNSIGNED'),
		'year'			=> array ('self::TYPE_INT',			0,								4)
	);
	
	
	/**
	 * Return an array of databases
	 * @return array
	 */
	
	public function getDatabases ()
	{
		
		// Get tables
		
		if ($this->databases === FALSE)
		{
			
			if (isset (\Sonic\Sonic::$resources['db']))
			{
				foreach (\Sonic\Sonic::$resources['db'] as $key => $db)
				{
					$this->databases[$key]	= $db->getDatabaseName ();
				}
			}
			
		}
		
		// Return tables
		
		return $this->databases;
		
	}
	
	
	/**
	 * Return an array of database tables
	 * @param string $db Database Name
	 * @return array
	 */
	
	public function getTables ($db)
	{
		
		// Get tables
		
		if (!isset ($this->tables[$db]))
		{
			
			$query	= $this->db->query ('SHOW TABLES FROM ' . $db);
			$query->setFetchMode (\PDO::FETCH_COLUMN, 0);
			
			$this->tables[$db]	= $query->fetchAll ();
			
		}
		
		// Return tables
		
		return $this->tables[$db];
		
	}
	
	
	/**
	 * Return an array of table columns
	 * @param string $db Database Name
	 * @param string $table Table Name
	 * @return array
	 */
	
	public function getColumns ($db, $table)
	{
		
		// Get columns
		
		if (!isset ($this->columns[$db][$table]))
		{
			$this->columns[$db][$table]	= $this->db->query ('SHOW COLUMNS FROM ' . $db . '.' . $table)->fetchAll (\PDO::FETCH_NUM);
		}
		
		// Return columns
		
		return $this->columns[$db][$table];
		
	}
	
	
	/**
	 * Return a table primary key attribute name
	 * * @param string $db Database Name
	 * @param string $table Table Name
	 * @return array
	 */
	
	public function getPk ($db, $table)
	{
		
		// Get pk name
		
		return $this->db->query ('SHOW COLUMNS FROM ' . $db . '.' . $table . ' WHERE `Key` = \'PRI\'')->fetch (\PDO::FETCH_ASSOC);
		
	}
	
	
	
	/**
	 * Return an array of foreign key constraints columns
	 * @param string $db Database Name
	 * @param string $table Table Name
	 * @return array
	 */
	
	public function getRelations ($db, $table)
	{
		
		// If there are no table constraints
		
		if (!isset ($this->relations[$db][$table]))
		{
			
			// Set blank array
			
			$this->relations[$db][$table]	= array ();
			
			// Query database
			
			$query	= $this->db->query ('SHOW CREATE TABLE ' . $db . '.' . $table);
			
			// get SQL
			
			$sql	= $query->fetchColumn (1);
			
			// For each line
			
			foreach (explode ("\n", $sql) as $line)
			{
				
				// Trim
				
				$line	= trim ($line);
				
				// If the line starts with CONSTRAINT
				
				if (strpos ($line, 'CONSTRAINT') === 0)
				{
					
					// Reset pattern
					
					$pattern	= array ();
					
					// Get necessary information out of the constraint
					
					preg_match ('/FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)/', 
						$line, $pattern);
					
					// Add to relations array
					
					$this->relations[$db][$table][]	= array (
						'attribute'		=> $pattern[1], 
						'table'			=> $pattern[2], 
						'column'		=> $pattern[3]
					);
					
				}
				
			}
			
		}
		
		// Return relations
		
		return $this->relations[$db][$table];
		
	}
	
	
	/**
	 * Return an attribute array for a column
	 * @param array $column Column
	 * @return array
	 */
	
	public function parseColumn ($column)
	{
		
		// Set attribute
		
		$attribute	=	array ();
		
		// If the datatype can be parsed and the datatype is in the column map
		
		if (preg_match ('/^(\w*)(\((.*?)\))*(.*)$/', $column[1], $dataType) && 
		isset (self::$columnMap[$dataType[1]]))
		{
			
			// Set column map
			
			$columnMap				= self::$columnMap[$dataType[1]];
			
			// Set type
			
			$attribute['type']		= $columnMap[0];
			
			// Charset based on the type
			
			$charsetName			= $dataType[1];
			
			if (strpos ($dataType[4], 'unsigned') !== FALSE)
			{
				
				if ($this->parser->charsetExists ($charsetName . '_unsigned'))
				{
					$charsetName	.= '_unsigned';
				}
			}
			
			if ($this->parser->charsetExists ($charsetName))
			{
				$attribute['charset']	= $charsetName;
			}
			
			// No charset from the column data type so type the attribute type
			
			if (!isset ($attribute['charset']))
			{
				
				$charsetName			= str_replace ('self::TYPE_', '', $columnMap[0]);

				if (strpos ($dataType[4], 'unsigned') !== FALSE)
				{

					if ($this->parser->charsetExists ($charsetName . '_unsigned'))
					{
						$charsetName	.= '_unsigned';
					}
				}

				if ($this->parser->charsetExists ($charsetName))
				{
					$attribute['charset']	= strtolower ($charsetName);
				}
				
			}
			
			// Switch type
			
			switch ($attribute['type'])
			{
				
				case 'self::TYPE_INT':
					
					// Set column min and max
					
					if (isset ($columnMap[1]) && isset ($columnMap[2]))
					{
						
						// If the column is unsigned

						if (strpos ($dataType[4], 'unsigned') !== FALSE)
						{

							// Set min and max to unsigned

							$attribute['min']	= $columnMap[1] . '_UNSIGNED';
							$attribute['max']	= $columnMap[2] . '_UNSIGNED';

						}

						// Else the column is signed

						else
						{

							// Set min and max

							$attribute['min']	= $columnMap[1];
							$attribute['max']	= $columnMap[2];

						}
						
					}
					
					// Set default
					
					$attribute['default']	= $column[4]?: 0;
					
					// Break
					
					break;
				
				
				case 'self::TYPE_STRING':
					
					// Default charset
					
					$attribute['charset']	= 'default';
					
					// Set column min and max
					
					if (isset ($columnMap[1]) && isset ($columnMap[2]))
					{

						$attribute['min']	= $columnMap[1];
						$attribute['max']	= $columnMap[2];
						
					}
					
					// If value constaints have been set
					
					if ($dataType[3])
					{
						
						// Set max
						
						$attribute['max']	= (int)$dataType[3];
						
					}
					
					// Set default
					
					$attribute['default']	= '\'' . ($column[4]?: '') . '\'';
					
					// Break
					
					break;
				
				
				case 'self::TYPE_DECIMAL':

					// Set column min and max
					
					if (isset ($columnMap[1]) && isset ($columnMap[2]))
					{
						
						// If the column is unsigned

						if (strpos ($dataType[4], 'unsigned') !== FALSE)
						{

							// Set min and max to unsigned

							$attribute['min']	= $columnMap[1] . '_UNSIGNED';
							$attribute['max']	= $columnMap[2] . '_UNSIGNED';

						}

						// Else the column is signed

						else
						{

							// Set min and max

							$attribute['min']	= $columnMap[1];
							$attribute['max']	= $columnMap[2];

						}
						
					}
					
					// If value constaints have been set
					
					if ($dataType[3])
					{
						
						// Break constraint

						$constraint	= explode (',', $dataType[3]);

						// If the column is unsigned

						if (strpos ($dataType[4], 'unsigned') !== FALSE)
						{

							// Set max

							$attribute['max']	= (float)('1' . str_repeat ('0', ($constraint[0] - $constraint[1])));
							
						}
						
						// Else signed value
						
						else
						{
							
							// Set min and max
							
							$attribute['min']	= (float)('-1' . str_repeat ('0', ($constraint[0] - $constraint[1])));
							$attribute['max']	= (float)('1' . str_repeat ('0', ($constraint[0] - $constraint[1])));
							
						}
						
					}
					
					// Set default
					
					$attribute['default']	= $column[4]?: 0;
					
					// Break
					
					break;
				
				
				case 'self::TYPE_ENUM':
					
					// Remove '' from values
					
//					$values	= substr (str_replace ('\',\'', ',', $dataType[3]), 1, -1);
					
					// Set values
					
					$attribute['values']	= $dataType[3];
					
					// Set column min and max
					
					if (isset ($columnMap[1]) && isset ($columnMap[2]))
					{

						$attribute['min']	= $columnMap[1];
						$attribute['max']	= $columnMap[2];
						
					}
					
					// Set default
					
					$attribute['default']	= '\'' . (isset ($column[4])? $column[4] : '') . '\'';
					
					// Break
					
					break;
				
				
				default:
					
					// Set column min and max
					
					if (isset ($columnMap[1]) && isset ($columnMap[2]))
					{

						$attribute['min']	= $columnMap[1];
						$attribute['max']	= $columnMap[2];
						
					}
					
					// Set default
					
					$attribute['default']	= '\'' . ($column[4]?: '') . '\'';
					
					// Break
					
					break;
				
				
			}

			// If the column is the primary key
			
			if ($column[3] == 'PRI')
			{

				// Set min to 1 (required)

				$attribute['min']		= 1;

			}
			
			// Set NULL

			if ($column[2] == 'YES')
			{
				
				$attribute['null']		= 'TRUE';

				if (!isset ($attribute['default']) || !$attribute['default'] || $attribute['default'] == '\'\'')
				{
					$attribute['default']	= 'NULL';
				}

			}
			
		}
		
		// Return attribute array
		
		return $attribute;
		
	}
	
	
}

// End Db Class