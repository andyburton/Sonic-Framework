<?php

// Define namespace

namespace Sonic\Resource;

// Start Db Class

class Db extends \PDO
{
	
	
	/**
	 * Valid query comparison operators
	 * @var array 
	 */
	
	private static $validComparison	= array ('=', '>=', '>', '<=', '<', '<>', '!=', 'LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT', '&', '|', 'BETWEEN');
	
	/**
	 * Valid query clause seperators
	 * @var array
	 */
	
	private static $validSplit		= array ('AND', 'OR', '||', '&&');
	
	/**
	 * Valid query clause seperators
	 * @var array
	 */
	
	private static $validOrder		= array ('ASC','DESC');
	
	/**
	 * Keeps track of the number of transactions
	 * @var int
	 */

	private $transactionCount	= 0;
	
	/**
	 * Connection status
	 * @var boolean
	 */
	
	private $connected	= FALSE;
	
	/**
	 * Data Source Name (DSN)
	 * @var string
	 */
	
	private $_dsn		= FALSE;
	
	/**
	 * Database Host
	 * @var string
	 */
	
	private $_host	= '127.0.0.1';
	
	/**
	 * Database Username
	 * @var string
	 */
	
	private $_user	= FALSE;
	
	/**
	 * Database Password
	 * @var string
	 */
	
	private $_pass	= FALSE;
	
	/**
	 * Database Name
	 * @var string
	 */
	
	private $_db_name	= FALSE;
	
	/**
	 * Database PDO Options
	 * @var array
	 */
	
	private $_options	= array ();
	
	/**
	 * Other databases that are commited and rolled back at the same time
	 * @var type 
	 */
	
	private $_transaction_hooks	= array ();
	
	
	/**
	 * Instantiate class
	 * @param string $dsn Data Source Name (DSN)
	 * @param string $user Username
	 * @param string $pass Password
	 * @param array $options Connection Options
	 * @return  @return \Sonic\Resource\Db
	 */

	public function __construct ($dsn, $user, $pass, $options)
	{
		$this->Connect ($dsn, $user, $pass, $options);
	}
	
	
	/**
	 * Connect to the database if not already done
	 * Will throw a PDOException is connection fails
	 * @param string $dsn Data Source Name (DSN)
	 * @param string $user Username
	 * @param string $pass Password
	 * @param array $options Connection Options
	 * @return void
	 */
	
	public function Connect ($dsn = FALSE, $user = FALSE, $pass = FALSE, $options = FALSE)
	{
		
		if (!$this->connected)
		{
			
			$this->_dsn		= $dsn?: $this->_dsn;
			$this->_user	= $user?: $this->_user;
			$this->_pass	= $pass?: $this->_pass;
			$this->_options	= $options?: $this->_options;
			
			parent::__construct ($this->_dsn, $this->_user, $this->_pass, $this->_options);
			
			$this->connected	= TRUE;
			
		}
		
	}
	
	
	/**
	 * Set connection details to the database
	 * Will throw a PDOException is connection fails
	 * @param string $dsn Data Source Name (DSN)
	 * @param string $user Username
	 * @param string $pass Password
	 * @param array $options Connection Options
	 * @return void
	 */
	
	public function setConnection ($dsn = FALSE, $user = FALSE, $pass = FALSE, $options = FALSE)
	{
		
		$this->_dsn		= $dsn?: $this->_dsn;
		$this->_user	= $user?: $this->_user;
		$this->_pass	= $pass?: $this->_pass;
		$this->_options	= $options?: $this->_options;
		
	}
	
	
	/**
	 * Return database Data Source Name (DSN)
	 * @return type string
	 */
	
	public function getDSN ()
	{
		return $this->_dsn;
	}
	
	
	/**
	 * Generate a where clause from an array
	 * @param array $arrWHERE Where clause array
	 * @return string
	 */
	
	public static function genWHERE ($arrWHERE)
	{

		// Set counter variables

		$intCount	= count ($arrWHERE);
		$intNum		= 1;

		// Set where clause

		$strWHERE	= '';

		// If the where condition is not an array

		if (!is_array ($arrWHERE))
		{

			// Set it as a single array item

			$arrWHERE	= array ($arrWHERE);

		}

		// For each where clause

		foreach ($arrWHERE as $key => $arrClause)
		{

			// If the first clause is an array

			if (is_array ($arrClause[0]))
			{

				// Generate clause where

				$strWHERE	.= '(' . self::genWHERE ($arrClause) . ')';

				// If not the last clause

				if ($intNum != $intCount)
				{

					// Get split

					if (isset ($arrClause[count($arrClause)-1][3]))
					{

						$strSplit	= $arrClause[count($arrClause)-1][3];

					}
					else
					{

						$strSplit	= 'AND';

					}

					// If the last split is valid

					if (in_array (strtoupper ($strSplit), self::$validSplit))
					{

						// Set it to the comparison used

						$strWHERE	.= strtoupper ($strSplit) . ' ';

					}

					// Else use default

					else
					{

						// Append clause

						$strWHERE	.= 'AND ';

					}

				}

			}

			// Else the first clause is not an array

			else
			{

				// If the clause is an array

				if (is_array ($arrClause))
				{

					// If there is a comparison type and it is valid

					if (isset ($arrClause[2]) && in_array ($arrClause[2], self::$validComparison))
					{

						// Set it to the comparison used

						$strComparison	= $arrClause[2];

					}

					// Else set to default

					else
					{

						// Set default comparison

						$strComparison	= '=';

					}

					// Append clause

					$strWHERE	.= $arrClause[0] . " " . $strComparison . " ";

					// If the comparison is IN or NOT IN

					if ($strComparison == 'IN' || $strComparison == 'NOT IN')
					{

						// Append
						
						if (is_array ($arrClause[1]))
						{
							$strWHERE	.= '(' . implode (',', $arrClause[1]) . ') ';
						}
						else
						{
							$strWHERE	.= '(' . $arrClause[1] . ') ';
						}

					}

					// Else if the comparison is IS or IS NOT

					else if ($strComparison == 'IS' || $strComparison == 'IS NOT')
					{

						// Append

						$strWHERE	.= $arrClause[1] . ' ';

					}

					// Else if the comparison is BETWEEN

					else if ($strComparison == 'BETWEEN')
					{

						// If the value is a string add marks around comparison

						if (is_string ($arrClause[1][0]))
						{

							$strWHERE	.= '\'' . $arrClause[1][0] . '\' AND \'' . $arrClause[1][1] . '\' ';

						}

						// Else not a string, just compare as is

						else
						{

							$strWHERE	.= $arrClause[1][0] . ' AND ' . $arrClause[1][1] . ' ';

						}

					}

					// Else the comparison is not IN or NOT IN

					else
					{

						// Append

						$strWHERE	.= ':' . md5 ($arrClause[0]) . '_' . md5 ($arrClause[1]) . ' ';

					}

				}

				// Clause is not an array

				else
				{

					// Add clause as it is

					$strWHERE		.= $arrClause . ' ';

				}

				// If not the last clause

				if ($intNum != $intCount)
				{

					// If there is a split type and it is valid

					if (isset ($arrClause[3]) && in_array (strtoupper ($arrClause[3]), self::$validSplit))
					{

						// Set it to the comparison used

						$strWHERE	.= strtoupper ($arrClause[3]) . ' ';

					}

					// Else use default

					else
					{

						// Append clause

						$strWHERE	.= 'AND ';

					}

				}

			}

			// Increment the count

			$intNum++;

		}

		// Return WHERE

		return $strWHERE;

	}

	
	/**
	 * Bind query values from where array
	 * @param resource $query PDO query
	 * @param array $arrWHERE Where clause array
	 * @return void
	 */

	public static function genBindValues (&$query, $arrWHERE)
	{

		// Foreach where clause

		foreach ($arrWHERE as $arrClause)
		{

			// If the first clause is an array

			if (is_array ($arrClause[0]))
			{

				// Bind params

				self::genBindValues ($query, $arrClause);

			}

			// Else the first clause is not an array

			else
			{

				// If clause is an array

				if (is_array ($arrClause))
				{

					// If there no comparison or there is one, its valid and not IN, NOT IN, IS, IS NOT or BETWEEN

					if (!isset ($arrClause[2]) || (
						!in_array ($arrClause[2], array ('IN', 'NOT IN', 'IS', 'IS NOT', 'BETWEEN')) && 
						in_array ($arrClause[2], self::$validComparison)
						))
					{

						// Bind value

						$query->bindValue (':' . md5 ($arrClause[0]) . '_' . md5 ($arrClause[1]), $arrClause[1]);

					}

				}

			}

		}

	}

	
	/**
	 * Generate an SQL statement from an array of clauses
	 * 
	 * $arrParams can have the following keys:
	 *   select 	- array|string of fields to SELECT (defaults to '*')
	 *   from		- array|string of tables to select FROM (defaults to db_table of class)
	 *   where		- array of WHERE clauses that get's passed to self::genWHERE
	 *   groupby	- array|string of GROUP BY clauses
	 *   having		- array of HAVING clauses that gets passed to self::genWHERE and can only be used if 'groupby' is set
	 *   orderby	- array|string of ORDER BY clause or array of clauses array (column, ASC|DESC)
	 *   limit		- array|integer with the return limit number or array (start, limit)
	 *
	 * @param array $arrParams	Array of clauses to add to the SQL
	 * @return string
	 */
	
	public function genSQL ($arrParams = array ())
	{

		// SELECT clause

		if (!isset ($arrParams['select']))
		{
			$arrParams['select']	= NULL;
		}

		$strSELECT	= $this->genClause ('SELECT', $arrParams['select'], '*');

		// If there is no from clause

		if (!isset ($arrParams['from']))
		{

			// Error
			
			throw new Exception ('You must specify a from clause!');
		}

		// FROM clause
		
		$strFROM	= $this->genClause ('FROM', $arrParams['from'], FALSE, ' ');

		// WHERE clause

		if (isset ($arrParams['where']) && is_array ($arrParams['where']))
		{

			// Generate WHERE clause
			
			$strWHERE	= $this->genClause ('WHERE', self::genWHERE ($arrParams['where']));

		}
		else
		{

			$strWHERE = NULL;

		}

		// GROUP BY clause

		if (!isset ($arrParams['groupby']))
		{
			$arrParams['groupby']	= NULL;
		}

		$strGROUPBY	= $this->genClause ('GROUP BY', $arrParams['groupby']);

		// If there is a group by clause and a having clause

		$strHAVING	= NULL;

		if (NULL !== $strGROUPBY && isset ($arrParams['having']))
		{

			// Generate HAVING clause
			
			$strHAVING	= $this->genClause ('HAVING', self::genWHERE ($arrParams['having']));

		}

		// ORDER BY clause

		if (!isset ($arrParams['orderby']))
		{
			$arrParams['orderby']	= NULL;
		}	
		else if (is_array ($arrParams['orderby']))
		{
			
			// If just a single clause add into correct structure for validation
			
			if (count ($arrParams['orderby']) == 2 && 
				!is_array ($arrParams['orderby'][0]) && 
				!is_array ($arrParams['orderby'][1]))
			{
				$arrParams['orderby']	= array ($arrParams['orderby']);
			}
			
			// Make sure the clauses are safe
			
			foreach ($arrParams['orderby'] as &$val)
			{
				
				if (is_array ($val))
				{
					
					foreach ($val as &$clause)
					{
						$clause = preg_replace ('/[^\w]/', '', $clause);
					}
					
					$val = implode (' ', $val);
					
				}
				else
				{
					
					$arr	= explode (' ', $val);

					if (count ($arr) == 2)
					{

						$arr[0]	= preg_replace ('/[^\w]/', '', $arr[0]);

						if (!in_array ($arr[1], self::$validOrder))
						{
							unset ($arr[1]);
						}

						$val	= implode (' ', $arr);

					}
					else
					{
						$val	= preg_replace ('/[^\w]/', '', $val);
					}
					
				}
				
			}
			
		}

		$strORDERBY	= $this->genClause ('ORDER BY', $arrParams['orderby']);

		// LIMIT clause
		
		if (!isset ($arrParams['limit']))
		{
			$arrParams['limit']	= NULL;
		}
		else if (is_array ($arrParams['limit']))
		{
			foreach ($arrParams['limit'] as &$val)
			{
				$val = intval ($val);
			}
		}
		else
		{
			$arrParams['limit']	= intval ($arrParams['limit']);
		}
		
		$strLIMIT	= $this->genClause ('LIMIT', $arrParams['limit']);
		
		// Put query together
		
		$strSQL		= $strSELECT . $strFROM . $strWHERE . $strGROUPBY . $strHAVING . $strORDERBY . $strLIMIT;
		
		// Return query
		
		return $strSQL;

	}

	
	/**
	 * Generates a clause in an SQL statement
	 * @param string $strName			The name of the clause
	 * @param string|array $mixOptions	Options to add to the clause
	 * @param string $strDefault		Default option to use if $mixOptions is not set
	 * @param string $strSeparator		Separator to use between options
	 * @return string
	 */
	
	public function genClause ($strName, $mixOptions, $strDefault = FALSE, $strSeparator = ', ')
	{

		// Set clause
		
		$strClause		= NULL;

		// If there are options
		
		if (isset ($mixOptions) && $mixOptions)
		{

			// Start the param
			
			$strClause	= $strName . ' ';

			// If array
			
			if (is_array ($mixOptions) && count ($mixOptions) > 0)
			{

				// join all order by clauses together
				
				$strClause .= implode ($strSeparator, $mixOptions);

			}
			
			// Else not array
			
			else
			{

				// Append clause
				
				$strClause	.= $mixOptions;

			}

		}
		
		// No options set but there is a default
		
		else if (FALSE !== $strDefault)
		{

			$strClause	= $strName . ' ' . $strDefault;

		}

		// If there is a clause
		
		if (NULL !== $strClause)
		{
			
			// Add space to the end of the clause
			
			$strClause .= ' ';
			
		}
		
		// Return clause
		
		return $strClause;

	}

	
	/**
	 * Returns a PDO query resource ready for execution
	 * @param array $arrParams	Array of clauses to add to the SQL
	 * @return object
	 */
	
	public function genQuery ($arrParams = array ())
	{

		// Generate sql query
		
		$strSQL	= $this->genSQL ($arrParams);

		// prepare database query
		
		$query = $this->prepare ($strSQL);

		// If there are some WHERE clauses
		
		if (isset ($arrParams['where']) && is_array ($arrParams['where']))
		{

			// Bind WHERE clauses
			
			$this->genBindValues ($query, $arrParams['where']);

		}

		// If there are some HAVING clauses
		
		if (isset ($arrParams['having']) && is_array ($arrParams['having']))
		{

			// Bind HAVING clauses
			
			$this->genBindValues ($query, $arrParams['having']);

		}

		// Return query
		
		return $query;

	}



	/**
	* Returns a PDO query resource ready for execution for an SQL union
	* @param array $arrParam SQL Parameter Array
	* @param array $arrParam SQL Parameter Array
	* ... etc
	* @return object
	*/

	public function genUnionQuery  ()
	{

		// Set SQL string

		$strSQL	= '';

		// Foreach argument

		foreach (func_get_args () as $intKey => $arrArg)
		{

			// If not the first argument add UNION

			if ($intKey > 0)
			{

				$strSQL	.= ' UNION ';

			}

			// Generate SQL and append to query

			$strSQL	.= $this->genSQL ($arrArg);

		}

		// Prepare database query

		$objQuery	= $this->prepare ($strSQL);

		// Foreach argument

		foreach (func_get_args () as $intKey => $arrArg)
		{

			// If there are WHERE clauses

			if (isset ($arrArg['where']) && is_array ($arrArg['where']))
			{

				// Bind

				$this->genBindValues ($objQuery, $arrArg['where']);

			}

			// If there are HAVING clauses

			if (isset ($arrArg['having']) && is_array ($arrArg['having']))
			{

				// Bind

				$this->genBindValues ($objQuery, $arrArg['having']);

			}

		}

		// Return query

		return $objQuery;

	}

	
	/**
	 * Return a single value or row
	 * @param array $arrParams Query Parameters
	 * @param int $fetchMode PDO fetch mode, default to both
	 * @return mixed
	 */
	
	public function getValue ($arrParams, $fetchMode = \PDO::FETCH_BOTH)
	{

		// If the params are not valid return FALSE

		if (!$this->validateParams ($arrParams))
		{
			return FALSE;
		}
		
		// Limit to 1 result

		$arrParams['limit']	= '1';

		// Query
		
		$query = $this->genQuery ($arrParams);
		
		// Execute

		$query->execute ();

		// Fetch the first set of results

		$arrResult	= $query->fetch ($fetchMode);
		
		// If there is only 1 column

		if (is_array ($arrResult) && count ($arrResult) === 1)
		{

			// Return the first column only
			
			return array_shift ($arrResult);

		}

		// else

		else
		{

			// Return result

			return $arrResult;

		}

	}

	
	/**
	 * Return all rows or array of single values
	 * @param array $arrParams Query Parameters
	 * @return mixed
	 */
	
	public function getValues ($arrParams)
	{
	
		// If the params are not valid return FALSE

		if (!$this->validateParams ($arrParams))
		{
			return FALSE;
		}

		// Query
		
		$query = $this->genQuery ($arrParams);
		
		// Execute

		$query->execute ();
		
		// If there is only 1 select param and it is not *
		
		$arrSelect	= is_array ($arrParams['select'])? $arrParams['select'] : explode (',', $arrParams['select']);
		
		if (count ($arrSelect) === 1 && $arrSelect[0] !== '*')
		{

			// Set fetch mode to return only the first column of each row
			
			$query->setFetchMode (\PDO::FETCH_COLUMN, 0);

		}
		else
		{

			// Set fetch mode to return all rows
			
			$query->setFetchMode (\PDO::FETCH_ASSOC);

		}

		// Fetch the first set of results

		$arrResult	= $query->fetchAll ();
		
		// Return result

		return $arrResult;

	}

	
	/**
	 * Validates the SQL generation clauses
	 * @param array $arrParams	Array of SQL clauses
	 * @return boolean
	 */
	
	private function validateParams ($arrParams)
	{

		// Validate

		if (!isset ($arrParams['select']) || !isset ($arrParams['from']))
		{

			// Return FALSE

			return FALSE;

		}

		// Return TRUE
		
		return TRUE;

	}


	/**
	 * Executes an SQL statement
	 * @param string $strSQL The SQL statement
	 * @param array $arrBindParams Array of parameters to bind
	 * @return boolean
	 */
	
	public function executeQuery ($strSQL, $arrBindParams = array ())
	{
		
		// Prepare query
		
		$query	= $this->prepare ($strSQL);
		
		// Bind parameters
		
		foreach ($arrBindParams as $key => $value)
		{
			
			if (is_array ($value))
			{
				$query->bindValue ($key, $value[0], $value[1]);
			}
			else
			{
				$query->bindValue ($key, $value);
			}
			
		}

		// Return query execution status
		
		return $query->execute ();

	}
        
        
	/**
	 * Get the query associate resultset with sql and bind params
	 * @param type $strSQL
	 * @param array $arrBindParams Query Parameters
	 * @return mixed
	 */

	public function fetchAssocBySql ($strSQL, $arrBindParams = array ())
	{

		// Prepare query

		$query = $this->prepare ($strSQL);

		// Bind parameters

		foreach ($arrBindParams as $key => $value)
		{

			if (is_array ($value))
			{
				$query->bindValue ($key, $value[0], $value[1]);
			}
			else
			{
				$query->bindValue ($key, $value);
			}

		}

		// query execution status

		$success = $query->execute ();

		if (!$success)
		{
			return FALSE;
		}

		return $query->fetchAll (2);

	}
		

	/**
	 * Execute an SQL statement
	 * @param string $strSQL The SQL statement
	 * @return int|boolean
	 */
	
	public function exec ($strSQL)
	{
		
		// Make sure we're connected
		
		$this->Connect ();
		
		// Execute statement
		
		return parent::exec ($strSQL);

	}
		

	/**
	 * Execute an SQL statement returning the PDOStatement object
	 * See PDO::query documentation for more details on the fetch modes
	 * @param string $strSQL The SQL statement
	 * @param integer $intMode Fetch mode
	 * @param mixed $v1 Fetch variable 1
	 * @param mixed $v2 Fetch variable 2
	 * @return PDOStatement|boolean
	 */
	
	public function query ($strSQL, $intMode = FALSE, $v1 = FALSE, $v2 = FALSE)
	{
		
		// Make sure we're connected
		
		$this->Connect ();
		
		// Execute statement
		
		switch ($intMode)
		{
			
			case \PDO::FETCH_COLUMN:
			case \PDO::FETCH_INTO:
				$stmt	= parent::query ($strSQL, $intMode, $v1);
				break;
			
			case \PDO::FETCH_CLASS:
				$stmt	= parent::query ($strSQL, $intMode, $v1, $v2);
				break;
			
			default:
				$stmt	= parent::query ($strSQL);
				break;
			
		}
		
		// Return statement
		
		return $stmt;

	}

	
	/**
	 * Prepare an SQL statement
	 * @param string $strSQL The SQL statement
	 * @param array $arrOptions Statement Options
	 * @return PDOStatement
	 */
	
	public function prepare ($strSQL, $arrOptions = array ())
	{
		
		// Make sure we're connected
		
		$this->Connect ();
		
		// Return prepared statement
		
		return parent::prepare ($strSQL, $arrOptions);

	}

	
	/**
	 * Add a transaction hook to commit and rollback another database at the same time as this database
	 * @param \Sonic\Resource\Db $db
	 */
	
	public function addTransactionHook (\Sonic\Resource\Db &$db)
	{
		
		// Get argument database DSN

		$dsn	= $db->getDSN ();
		
		// Set if the databases are not the same and the the database isn't already hooked
		
		if ($dsn != $this->getDSN () && !isset ($this->_transaction_hooks[$dsn]))
		{
			$this->_transaction_hooks[$dsn]	=& $db;
			return TRUE;
		}
		
		return FALSE;
		
	}
	
	
	/**
	 * Begins a transaction only if there is not already a transaction in progress
	 * @return boolean
	 */
	
	public function beginTransaction ()
	{

		// Make sure we're connected
		
		$this->Connect ();
		
		// Increment transaction count and begin if necessary
		
		if ($this->transactionCount++ === 0)
		{
			return parent::beginTransaction ();
		}
		else
		{
			return TRUE;
		}

	}

	
	/**
	 * Commits a transaction if the passed parameter is true, otherwise rolls it back
	 * @param boolean $status
	 * @return boolean
	 */
	
	public function commitIf ($status)
	{

		if ($status)
		{
			$this->commit ();
		}
		else
		{
			$this->rollBack ();
		}
		
		return $status;

	}

	
	/**
	 * Commits a transaction as long as there are no other active transactions
	 * @return boolean
	 */
	
	public function commit ()
	{

		if (--$this->transactionCount === 0)
		{
			
			$status	= parent::commit ();
			
			// Commit any hooked databases
			
			if ($this->_transaction_hooks)
			{
				
				if ($status)
				{
					foreach ($this->_transaction_hooks as $dsn => &$db)
					{
						$db->commit ();
					}
				}
				
				$this->_transaction_hooks	= array ();
				
			}
			
			return $status;
			
		}
		else
		{
			return FALSE;
		}

	}

	
	/**
	 * Rolls back a transaction and allows new transactions to begin
	 * @return boolean
	 */
	
	public function rollBack ()
	{

		if (--$this->transactionCount === 0)
		{
			
			$status	= parent::rollBack ();
			
			// Rollback any hooked databases
			
			if ($this->_transaction_hooks)
			{
				
				foreach ($this->_transaction_hooks as &$db)
				{
					$db->rollBack ();
				}
				
				$this->_transaction_hooks	= array ();
				
			}
			
			return $status;
			
		}
		else
		{
			return FALSE;
		}

	}
	
	
	/**
	 * Return the currently selected database
	 * @return string
	 */
	
	public function getDatabaseName ()
	{
		$query = $this->query ('SELECT database()');
		return $query->fetchColumn ();
	}
	
	
	/**
	 * Return depth of transactions
	 * @return integer 
	 */
	
	public function getTransactionCount ()
	{
		return $this->transactionCount;
	}

	
}

// End Db Class