<?php namespace CodeIgniter\Database;

/**
 * Class BaseBuilder
 *
 * Provides the core Query Builder methods.
 * Database-specific Builders might need to override
 * certain methods to make them work.
 *
 * @package CodeIgniter\Database
 */
class BaseBuilder
{
	/**
	 * Return DELETE SQL flag
	 *
	 * @var    bool
	 */
	protected $returnDeleteSQL = false;

	/**
	 * Reset DELETE data flag
	 *
	 * @var    bool
	 */
	protected $resetDeleteData = false;

	/**
	 * QB SELECT data
	 *
	 * @var    array
	 */
	protected $QBSelect = [];

	/**
	 * QB DISTINCT flag
	 *
	 * @var    bool
	 */
	protected $QBDistinct = false;

	/**
	 * QB FROM data
	 *
	 * @var    array
	 */
	protected $QBFrom = [];

	/**
	 * QB JOIN data
	 *
	 * @var    array
	 */
	protected $QBJoin = [];

	/**
	 * QB WHERE data
	 *
	 * @var    array
	 */
	protected $QBWhere = [];

	/**
	 * QB GROUP BY data
	 *
	 * @var    array
	 */
	protected $QBGroupBy = [];

	/**
	 * QB HAVING data
	 *
	 * @var    array
	 */
	protected $QBHaving = [];

	/**
	 * QB keys
	 *
	 * @var    array
	 */
	protected $QBKeys = [];

	/**
	 * QB LIMIT data
	 *
	 * @var    int
	 */
	protected $QBLimit = false;

	/**
	 * QB OFFSET data
	 *
	 * @var    int
	 */
	protected $QBOffset = false;

	/**
	 * QB ORDER BY data
	 *
	 * @var    array
	 */
	protected $QBOrderBy = [];

	/**
	 * QB data sets
	 *
	 * @var    array
	 */
	protected $QBSet = [];

	/**
	 * QB aliased tables list
	 *
	 * @var    array
	 */
	protected $QBAliasedTables = [];

	/**
	 * QB WHERE group started flag
	 *
	 * @var    bool
	 */
	protected $QBWhereGroupStarted = false;

	/**
	 * QB WHERE group count
	 *
	 * @var    int
	 */
	protected $QBWhereGroupCount = 0;

	// Query Builder Caching variables

	/**
	 * QB Caching flag
	 *
	 * @var    bool
	 */
	protected $QBCaching = false;

	/**
	 * QB Cache exists list
	 *
	 * @var    array
	 */
	protected $QBCacheExists = [];

	/**
	 * QB Cache SELECT data
	 *
	 * @var    array
	 */
	protected $QBCacheSelect = [];

	/**
	 * QB Cache FROM data
	 *
	 * @var    array
	 */
	protected $QBCacheFrom = [];

	/**
	 * QB Cache JOIN data
	 *
	 * @var    array
	 */
	protected $QBCacheJoin = [];

	/**
	 * QB Cache WHERE data
	 *
	 * @var    array
	 */
	protected $QBCacheWhere = [];

	/**
	 * QB Cache GROUP BY data
	 *
	 * @var    array
	 */
	protected $QBCacheGroup = [];

	/**
	 * QB Cache HAVING data
	 *
	 * @var    array
	 */
	protected $QBCacheHaving = [];

	/**
	 * QB Cache ORDER BY data
	 *
	 * @var    array
	 */
	protected $QBCacheOrderBy = [];

	/**
	 * QB Cache data sets
	 *
	 * @var    array
	 */
	protected $QBCacheSet = [];

	/**
	 * QB No Escape data
	 *
	 * @var    array
	 */
	protected $QBNoEscape = [];

	/**
	 * QB Cache No Escape data
	 *
	 * @var    array
	 */
	protected $QBCacheNoEscape = [];

	/**
	 * A reference to the database connection.
	 * 
	 * @var ConnectionInterface
	 */
	protected $db;

	//--------------------------------------------------------------------

	public function __construct(string $tableName, ConnectionInterface &$db)
	{
		$this->trackAliases($tableName);
		$this->from($tableName);

		$this->db = $db;
	}

	//--------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Generates the SELECT portion of the query
	 *
	 * @param    string
	 * @param    mixed
	 *
	 * @return    CI_DB_query_builder
	 */
	public function select($select = '*', $escape = null)
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}

		// If the escape value was not set, we will base it on the global setting
		is_bool($escape) OR $escape = $this->_protect_identifiers;

		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val !== '')
			{
				$this->QBSelect[]   = $val;
				$this->QBNoEscape[] = $escape;

				if ($this->QBCaching === true)
				{
					$this->QBCacheSelect[]   = $val;
					$this->QBCacheExists[]   = 'select';
					$this->QBCacheNoEscape[] = $escape;
				}
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Select Max
	 *
	 * Generates a SELECT MAX(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    CI_DB_query_builder
	 */
	public function selectMax($select = '', $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'MAX');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Min
	 *
	 * Generates a SELECT MIN(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    CI_DB_query_builder
	 */
	public function selectMin($select = '', $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'MIN');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Average
	 *
	 * Generates a SELECT AVG(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    CI_DB_query_builder
	 */
	public function selectAvg($select = '', $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'AVG');
	}

	//--------------------------------------------------------------------

	/**
	 * Select Sum
	 *
	 * Generates a SELECT SUM(field) portion of a query
	 *
	 * @param    string    the field
	 * @param    string    an alias
	 *
	 * @return    CI_DB_query_builder
	 */
	public function selectSum($select = '', $alias = '')
	{
		return $this->maxMinAvgSum($select, $alias, 'SUM');
	}

	//--------------------------------------------------------------------

	/**
	 * SELECT [MAX|MIN|AVG|SUM]()
	 *
	 * @used-by    selectMax()
	 * @used-by    selectMin()
	 * @used-by    selectAvg()
	 * @used-by    selectSum()
	 *
	 * @param    string $select Field name
	 * @param    string $alias
	 * @param    string $type
	 *
	 * @return    CI_DB_query_builder
	 */
	protected function maxMinAvgSum($select = '', $alias = '', $type = 'MAX')
	{
		if ( ! is_string($select) OR $select === '')
		{
			$this->display_error('db_invalid_query');
		}

		$type = strtoupper($type);

		if ( ! in_array($type, ['MAX', 'MIN', 'AVG', 'SUM']))
		{
			show_error('Invalid function type: '.$type);
		}

		if ($alias === '')
		{
			$alias = $this->createAliasFromTable(trim($select));
		}

		$sql = $type.'('.$this->protect_identifiers(trim($select)).') AS '.$this->escape_identifiers(trim($alias));

		$this->QBSelect[]   = $sql;
		$this->QBNoEscape[] = null;

		if ($this->QBCaching === true)
		{
			$this->QBCacheSelect[] = $sql;
			$this->QBCacheExists[] = 'select';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the alias name based on the table
	 *
	 * @param    string $item
	 *
	 * @return    string
	 */
	protected function createAliasFromTable($item)
	{
		if (strpos($item, '.') !== false)
		{
			$item = explode('.', $item);

			return end($item);
		}

		return $item;
	}

	//--------------------------------------------------------------------

	/**
	 * DISTINCT
	 *
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @param    bool $val
	 *
	 * @return    CI_DB_query_builder
	 */
	public function distinct($val = true)
	{
		$this->QBDistinct = is_bool($val) ? $val : true;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * From
	 *
	 * Generates the FROM portion of the query
	 *
	 * @param    mixed $from can be a string or array
	 *
	 * @return    CI_DB_query_builder
	 */
	public function from($from)
	{
		foreach ((array)$from as $val)
		{
			if (strpos($val, ',') !== false)
			{
				foreach (explode(',', $val) as $v)
				{
					$v = trim($v);
					$this->trackAliases($v);

					$this->QBFrom[] = $v = $this->protect_identifiers($v, true, null, false);

					if ($this->QBCaching === true)
					{
						$this->QBCacheFrom[]   = $v;
						$this->QBCacheExists[] = 'from';
					}
				}
			}
			else
			{
				$val = trim($val);

				// Extract any aliases that might exist. We use this information
				// in the protect_identifiers to know whether to add a table prefix
				$this->trackAliases($val);

				$this->QBFrom[] = $val; // = $this->protect_identifiers($val, true, null, false);

				if ($this->QBCaching === true)
				{
					$this->QBCacheFrom[]   = $val;
					$this->QBCacheExists[] = 'from';
				}
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * JOIN
	 *
	 * Generates the JOIN portion of the query
	 *
	 * @param    string
	 * @param    string    the join condition
	 * @param    string    the type of join
	 * @param    string    whether not to try to escape identifiers
	 *
	 * @return    CI_DB_query_builder
	 */
	public function join($table, $cond, $type = '', $escape = null)
	{
		if ($type !== '')
		{
			$type = strtoupper(trim($type));

			if ( ! in_array($type, ['LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'], true))
			{
				$type = '';
			}
			else
			{
				$type .= ' ';
			}
		}

		// Extract any aliases that might exist. We use this information
		// in the protect_identifiers to know whether to add a table prefix
		$this->trackAliases($table);

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		if ( ! $this->_has_operator($cond))
		{
			$cond = ' USING ('.($escape ? $this->escape_identifiers($cond) : $cond).')';
		}
		elseif ($escape === false)
		{
			$cond = ' ON '.$cond;
		}
		else
		{
			// Split multiple conditions
			if (preg_match_all('/\sAND\s|\sOR\s/i', $cond, $joints, PREG_OFFSET_CAPTURE))
			{
				$conditions = [];
				$joints     = $joints[0];
				array_unshift($joints, ['', 0]);

				for ($i = count($joints) - 1, $pos = strlen($cond); $i >= 0; $i--)
				{
					$joints[$i][1] += strlen($joints[$i][0]); // offset
					$conditions[$i] = substr($cond, $joints[$i][1], $pos - $joints[$i][1]);
					$pos            = $joints[$i][1] - strlen($joints[$i][0]);
					$joints[$i]     = $joints[$i][0];
				}
			}
			else
			{
				$conditions = [$cond];
				$joints     = [''];
			}

			$cond = ' ON ';
			for ($i = 0, $c = count($conditions); $i < $c; $i++)
			{
				$operator = $this->_get_operator($conditions[$i]);
				$cond .= $joints[$i];
				$cond .= preg_match("/(\(*)?([\[\]\w\.'-]+)".preg_quote($operator)."(.*)/i", $conditions[$i], $match)
					? $match[1].$this->protect_identifiers($match[2]).$operator.$this->protect_identifiers($match[3])
					: $conditions[$i];
			}
		}

		// Do we want to escape the table name?
		if ($escape === true)
		{
			$table = $this->protect_identifiers($table, true, null, false);
		}

		// Assemble the JOIN statement
		$this->QBJoin[] = $join = $type.'JOIN '.$table.$cond;

		if ($this->QBCaching === true)
		{
			$this->QBCacheJoin[]   = $join;
			$this->QBCacheExists[] = 'join';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    CI_DB_query_builder
	 */
	public function where($key, $value = null, $escape = null)
	{
		return $this->whereHaving('QBWhere', $key, $value, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE
	 *
	 * Generates the WHERE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed
	 * @param    mixed
	 * @param    bool
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orWhere($key, $value = null, $escape = null)
	{
		return $this->whereHaving('QBWhere', $key, $value, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE, HAVING
	 *
	 * @used-by    where()
	 * @used-by    orWhere()
	 * @used-by    having()
	 * @used-by    orHaving()
	 *
	 * @param    string $qb_key 'QBWhere' or 'QBHaving'
	 * @param    mixed  $key
	 * @param    mixed  $value
	 * @param    string $type
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	protected function whereHaving($qb_key, $key, $value = null, $type = 'AND ', $escape = null)
	{
		$qb_cache_key = ($qb_key === 'QBHaving') ? 'QBCacheHaving' : 'QBCacheWhere';

		if ( ! is_array($key))
		{
			$key = [$key => $value];
		}

		// If the escape value was not set will base it on the global setting
		is_bool($escape) OR $escape = $this->_protect_identifiers;

		foreach ($key as $k => $v)
		{
			$prefix = (count($this->$qb_key) === 0 && count($this->$qb_cache_key) === 0)
				? $this->groupGetType('')
				: $this->groupGetType($type);

			if ($v !== null)
			{
				if ($escape === true)
				{
					$v = ' '.$this->escape($v);
				}

				if ( ! $this->_has_operator($k))
				{
					$k .= ' = ';
				}
			}
			elseif ( ! $this->_has_operator($k))
			{
				// value appears not to have been set, assign the test to IS NULL
				$k .= ' IS NULL';
			}
			elseif (preg_match('/\s*(!?=|<>|IS(?:\s+NOT)?)\s*$/i', $k, $match, PREG_OFFSET_CAPTURE))
			{
				$k = substr($k, 0, $match[0][1]).($match[1][0] === '=' ? ' IS NULL' : ' IS NOT NULL');
			}

			$this->{$qb_key}[] = ['condition' => $prefix.$k.$v, 'escape' => $escape];
			if ($this->QBCaching === true)
			{
				$this->{$qb_cache_key}[] = ['condition' => $prefix.$k.$v, 'escape' => $escape];
				$this->QBCacheExists[]   = substr($qb_key, 3);
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function whereIn($key = null, $values = null, $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE IN
	 *
	 * Generates a WHERE field IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orWhereIn($key = null, $values = null, $escape = null)
	{
		return $this->_whereIn($key, $values, false, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'AND' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function whereNotIn($key = null, $values = null, $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN
	 *
	 * Generates a WHERE field NOT IN('item', 'item') SQL query,
	 * joined with 'OR' if appropriate.
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orWhereNotIn($key = null, $values = null, $escape = null)
	{
		return $this->_whereIn($key, $values, true, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * Internal WHERE IN
	 *
	 * @used-by    WhereIn()
	 * @used-by    orWhereIn()
	 * @used-by    whereNotIn()
	 * @used-by    orWhereNotIn()
	 *
	 * @param    string $key    The field to search
	 * @param    array  $values The values searched on
	 * @param    bool   $not    If the statement would be IN or NOT IN
	 * @param    string $type
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	protected function _whereIn($key = null, $values = null, $not = false, $type = 'AND ', $escape = null)
	{
		if ($key === null OR $values === null)
		{
			return $this;
		}

		if ( ! is_array($values))
		{
			$values = [$values];
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		$not = ($not) ? ' NOT' : '';

		if ($escape === true)
		{
			$where_in = [];
			foreach ($values as $value)
			{
				$where_in[] = $this->escape($value);
			}
		}
		else
		{
			$where_in = array_values($values);
		}

		$prefix = (count($this->QBWhere) === 0 && count($this->QBCacheWhere) === 0)
			? $this->groupGetType('')
			: $this->groupGetType($type);

		$where_in = [
			'condition' => $prefix.$key.$not.' IN('.implode(', ', $where_in).')',
			'escape'    => $escape,
		];

		$this->QBWhere[] = $where_in;
		if ($this->QBCaching === true)
		{
			$this->QBCacheWhere[]  = $where_in;
			$this->QBCacheExists[] = 'where';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function like($field, $match = '', $side = 'both', $escape = null)
	{
		return $this->_like($field, $match, 'AND ', $side, '', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function notLike($field, $match = '', $side = 'both', $escape = null)
	{
		return $this->_like($field, $match, 'AND ', $side, 'NOT', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR LIKE
	 *
	 * Generates a %LIKE% portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orLike($field, $match = '', $side = 'both', $escape = null)
	{
		return $this->_like($field, $match, 'OR ', $side, '', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR NOT LIKE
	 *
	 * Generates a NOT LIKE portion of the query.
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $side
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orNotLike($field, $match = '', $side = 'both', $escape = null)
	{
		return $this->_like($field, $match, 'OR ', $side, 'NOT', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * Internal LIKE
	 *
	 * @used-by    like()
	 * @used-by    orLike()
	 * @used-by    notLike()
	 * @used-by    orNotLike()
	 *
	 * @param    mixed  $field
	 * @param    string $match
	 * @param    string $type
	 * @param    string $side
	 * @param    string $not
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	protected function _like($field, $match = '', $type = 'AND ', $side = 'both', $not = '', $escape = null)
	{
		if ( ! is_array($field))
		{
			$field = [$field => $match];
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;
		// lowercase $side in case somebody writes e.g. 'BEFORE' instead of 'before' (doh)
		$side = strtolower($side);

		foreach ($field as $k => $v)
		{
			$prefix = (count($this->QBWhere) === 0 && count($this->QBCacheWhere) === 0)
				? $this->groupGetType('') : $this->groupGetType($type);

			if ($escape === true)
			{
				$v = $this->escape_like_str($v);
			}

			if ($side === 'none')
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '{$v}'";
			}
			elseif ($side === 'before')
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '%{$v}'";
			}
			elseif ($side === 'after')
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '{$v}%'";
			}
			else
			{
				$like_statement = "{$prefix} {$k} {$not} LIKE '%{$v}%'";
			}

			// some platforms require an escape sequence definition for LIKE wildcards
			if ($escape === true && $this->_like_escape_str !== '')
			{
				$like_statement .= sprintf($this->_like_escape_str, $this->_like_escape_chr);
			}

			$this->QBWhere[] = ['condition' => $like_statement, 'escape' => $escape];
			if ($this->QBCaching === true)
			{
				$this->QBCacheWhere[]  = ['condition' => $like_statement, 'escape' => $escape];
				$this->QBCacheExists[] = 'where';
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group.
	 *
	 * @param    string $not  (Internal use only)
	 * @param    string $type (Internal use only)
	 *
	 * @return    CI_DB_query_builder
	 */
	public function groupStart($not = '', $type = 'AND ')
	{
		$type = $this->groupGetType($type);

		$this->QBWhereGroupStarted = true;
		$prefix                    = (count($this->QBWhere) === 0 && count($this->QBCacheWhere) === 0) ? ''
			: $type;
		$where                     = [
			'condition' => $prefix.$not.str_repeat(' ', ++$this->QBWhereGroupCount).' (',
			'escape'    => false,
		];

		$this->QBWhere[] = $where;
		if ($this->QBCaching)
		{
			$this->QBCacheWhere[] = $where;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but ORs the group
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orGroupStart()
	{
		return $this->groupStart('', 'OR ');
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but NOTs the group
	 *
	 * @return    CI_DB_query_builder
	 */
	public function notGroupStart()
	{
		return $this->groupStart('NOT ', 'AND ');
	}

	//--------------------------------------------------------------------

	/**
	 * Starts a query group, but OR NOTs the group
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orNotGroupStart()
	{
		return $this->groupStart('NOT ', 'OR ');
	}

	//--------------------------------------------------------------------

	/**
	 * Ends a query group
	 *
	 * @return    CI_DB_query_builder
	 */
	public function groupEnd()
	{
		$this->QBWhereGroupStarted = false;
		$where                     = [
			'condition' => str_repeat(' ', $this->QBWhereGroupCount--).')',
			'escape'    => false,
		];

		$this->QBWhere[] = $where;
		if ($this->QBCaching)
		{
			$this->QBCacheWhere[] = $where;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Group_get_type
	 *
	 * @used-by    groupStart()
	 * @used-by    _like()
	 * @used-by    whereHaving()
	 * @used-by    _whereIn()
	 *
	 * @param    string $type
	 *
	 * @return    string
	 */
	protected function groupGetType($type)
	{
		if ($this->QBWhereGroupStarted)
		{
			$type                      = '';
			$this->QBWhereGroupStarted = false;
		}

		return $type;
	}

	//--------------------------------------------------------------------

	/**
	 * GROUP BY
	 *
	 * @param    string $by
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function groupBy($by, $escape = null)
	{
		is_bool($escape) OR $escape = $this->_protect_identifiers;

		if (is_string($by))
		{
			$by = ($escape === true)
				? explode(',', $by)
				: [$by];
		}

		foreach ($by as $val)
		{
			$val = trim($val);

			if ($val !== '')
			{
				$val = ['field' => $val, 'escape' => $escape];

				$this->QBGroupBy[] = $val;
				if ($this->QBCaching === true)
				{
					$this->QBCacheGroup[]  = $val;
					$this->QBCacheExists[] = 'groupby';
				}
			}
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * HAVING
	 *
	 * Separates multiple calls with 'AND'.
	 *
	 * @param    string $key
	 * @param    string $value
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function having($key, $value = null, $escape = null)
	{
		return $this->whereHaving('QBHaving', $key, $value, 'AND ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * OR HAVING
	 *
	 * Separates multiple calls with 'OR'.
	 *
	 * @param    string $key
	 * @param    string $value
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orHaving($key, $value = null, $escape = null)
	{
		return $this->whereHaving('QBHaving', $key, $value, 'OR ', $escape);
	}

	//--------------------------------------------------------------------

	/**
	 * ORDER BY
	 *
	 * @param    string $orderby
	 * @param    string $direction ASC, DESC or RANDOM
	 * @param    bool   $escape
	 *
	 * @return    CI_DB_query_builder
	 */
	public function orderBy($orderby, $direction = '', $escape = null)
	{
		$direction = strtoupper(trim($direction));

		if ($direction === 'RANDOM')
		{
			$direction = '';

			// Do we have a seed value?
			$orderby = ctype_digit((string)$orderby)
				? sprintf($this->_random_keyword[1], $orderby)
				: $this->_random_keyword[0];
		}
		elseif (empty($orderby))
		{
			return $this;
		}
		elseif ($direction !== '')
		{
			$direction = in_array($direction, ['ASC', 'DESC'], true) ? ' '.$direction : '';
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		if ($escape === false)
		{
			$qb_orderby[] = ['field' => $orderby, 'direction' => $direction, 'escape' => false];
		}
		else
		{
			$qb_orderby = [];
			foreach (explode(',', $orderby) as $field)
			{
				$qb_orderby[] = ($direction === '' &&
				                 preg_match('/\s+(ASC|DESC)$/i', rtrim($field), $match, PREG_OFFSET_CAPTURE))
					? [
						'field'     => ltrim(substr($field, 0, $match[0][1])),
						'direction' => ' '.$match[1][0],
						'escape'    => true,
					]
					: ['field' => trim($field), 'direction' => $direction, 'escape' => true];
			}
		}

		$this->QBOrderBy = array_merge($this->QBOrderBy, $qb_orderby);
		if ($this->QBCaching === true)
		{
			$this->QBCacheOrderBy  = array_merge($this->QBCacheOrderBy, $qb_orderby);
			$this->QBCacheExists[] = 'orderby';
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * @param    int $value  LIMIT value
	 * @param    int $offset OFFSET value
	 *
	 * @return    CI_DB_query_builder
	 */
	public function limit($value, $offset = 0)
	{
		is_null($value) OR $this->QBLimit = (int)$value;
		empty($offset) OR $this->QBOffset = (int)$offset;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the OFFSET value
	 *
	 * @param    int $offset OFFSET value
	 *
	 * @return    CI_DB_query_builder
	 */
	public function offset($offset)
	{
		empty($offset) OR $this->QBOffset = (int)$offset;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * LIMIT string
	 *
	 * Generates a platform-specific LIMIT clause.
	 *
	 * @param    string $sql SQL Query
	 *
	 * @return    string
	 */
	protected function _limit($sql)
	{
		return $sql.' LIMIT '.($this->QBOffset ? $this->QBOffset.', ' : '').$this->QBLimit;
	}

	//--------------------------------------------------------------------

	/**
	 * The "set" function.
	 *
	 * Allows key/value pairs to be set for inserting or updating
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 *
	 * @return    CI_DB_query_builder
	 */
	public function set($key, $value = '', $escape = null)
	{
		$key = $this->objectToArray($key);

		if ( ! is_array($key))
		{
			$key = [$key => $value];
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		foreach ($key as $k => $v)
		{
			$this->QBSet[$this->protect_identifiers($k, false, $escape)] = ($escape)
				? $this->escape($v) : $v;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get SELECT query string
	 *
	 * Compiles a SELECT query string and returns the sql.
	 *
	 * @param    string    the table name to select from (optional)
	 * @param    bool      TRUE: resets QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledSelect($table = '', $reset = true)
	{
		if ($table !== '')
		{
			$this->trackAliases($table);
			$this->from($table);
		}

		$select = $this->compileSelect();

		if ($reset === true)
		{
			$this->resetSelect();
		}

		return $select;
	}

	//--------------------------------------------------------------------

	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @param    string    the table
	 * @param    string    the limit clause
	 * @param    string    the offset clause
	 *
	 * @return    CI_DB_result
	 */
	public function get($table = '', $limit = null, $offset = null)
	{
		if ($table !== '')
		{
			$this->trackAliases($table);
			$this->from($table);
		}

		if ( ! empty($limit))
		{
			$this->limit($limit, $offset);
		}

		// @todo Refactor system to collect binds that are passed to Connection, which are then inserted into the Query object, which does the escaping.
		$result = $this->db->query($this->compileSelect());
		$this->resetSelect();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * "Count All Results" query
	 *
	 * Generates a platform-specific query string that counts all records
	 * returned by an Query Builder query.
	 *
	 * @param    string
	 * @param    bool    the reset clause
	 *
	 * @return    int
	 */
	public function countAllResults($table = '', $reset = true)
	{
		if ($table !== '')
		{
			$this->trackAliases($table);
			$this->from($table);
		}

		// ORDER BY usage is often problematic here (most notably
		// on Microsoft SQL Server) and ultimately unnecessary
		// for selecting COUNT(*) ...
		if ( ! empty($this->QBOrderBy))
		{
			$orderby         = $this->QBOrderBy;
			$this->QBOrderBy = null;
		}

		$result = ($this->QBDistinct === true)
			? $this->query($this->_count_string.$this->protect_identifiers('numrows')."\nFROM (\n".
			               $this->compileSelect()."\n) CI_count_all_results")
			: $this->query($this->compileSelect($this->_count_string.$this->protect_identifiers('numrows')));

		if ($reset === true)
		{
			$this->resetSelect();
		}
		// If we've previously reset the QBOrderBy values, get them back
		elseif ( ! isset($this->QBOrderBy))
		{
			$this->QBOrderBy = $orderby;
		}

		if ($result->num_rows() === 0)
		{
			return 0;
		}

		$row = $result->row();

		return (int)$row->numrows;
	}

	//--------------------------------------------------------------------

	/**
	 * Get_Where
	 *
	 * Allows the where clause, limit and offset to be added directly
	 *
	 * @param    string $table
	 * @param    string $where
	 * @param    int    $limit
	 * @param    int    $offset
	 *
	 * @return    CI_DB_result
	 */
	public function getWhere($table = '', $where = null, $limit = null, $offset = null)
	{
		if ($table !== '')
		{
			$this->from($table);
		}

		if ($where !== null)
		{
			$this->where($where);
		}

		if ( ! empty($limit))
		{
			$this->limit($limit, $offset);
		}

		$result = $this->query($this->compileSelect());
		$this->resetSelect();

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert_Batch
	 *
	 * Compiles batch insert strings and runs the queries
	 *
	 * @param    string $table  Table to insert into
	 * @param    array  $set    An associative array of insert values
	 * @param    bool   $escape Whether to escape values and identifiers
	 *
	 * @return    int    Number of rows inserted or FALSE on failure
	 */
	public function insertBatch($table, $set = null, $escape = null, $batch_size = 100)
	{
		if ($set === null)
		{
			if (empty($this->QBSet))
			{
				return ($this->db_debug) ? $this->display_error('db_must_use_set') : false;
			}
		}
		else
		{
			if (empty($set))
			{
				return ($this->db_debug) ? $this->display_error('insertBatch() called with no data') : false;
			}

			$this->setInsertBatch($set, '', $escape);
		}

		if (strlen($table) === 0)
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}

		// Batch this baby
		$affected_rows = 0;
		for ($i = 0, $total = count($this->QBSet); $i < $total; $i += $batch_size)
		{
			$this->query($this->_insertBatch($this->protect_identifiers($table, true, $escape, false), $this->QBKeys,
				array_slice($this->QBSet, $i, $batch_size)));
			$affected_rows += $this->affected_rows();
		}

		$this->resetWrite();

		return $affected_rows;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param    string $table  Table name
	 * @param    array  $keys   INSERT keys
	 * @param    array  $values INSERT values
	 *
	 * @return    string
	 */
	protected function _insertBatch($table, $keys, $values)
	{
		return 'INSERT INTO '.$table.' ('.implode(', ', $keys).') VALUES '.implode(', ', $values);
	}

	//--------------------------------------------------------------------

	/**
	 * The "setInsertBatch" function.  Allows key/value pairs to be set for batch inserts
	 *
	 * @param    mixed
	 * @param    string
	 * @param    bool
	 *
	 * @return    CI_DB_query_builder
	 */
	public function setInsertBatch($key, $value = '', $escape = null)
	{
		$key = $this->batchObjectToArray($key);

		if ( ! is_array($key))
		{
			$key = [$key => $value];
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		$keys = array_keys($this->objectToArray(current($key)));
		sort($keys);

		foreach ($key as $row)
		{
			$row = $this->objectToArray($row);
			if (count(array_diff($keys, array_keys($row))) > 0 OR count(array_diff(array_keys($row), $keys)) > 0)
			{
				// batch function above returns an error on an empty array
				$this->QBSet[] = [];

				return;
			}

			ksort($row); // puts $row in the same order as our keys

			if ($escape !== false)
			{
				$clean = [];
				foreach ($row as $value)
				{
					$clean[] = $this->escape($value);
				}

				$row = $clean;
			}

			$this->QBSet[] = '('.implode(',', $row).')';
		}

		foreach ($keys as $k)
		{
			$this->QBKeys[] = $this->protect_identifiers($k, false, $escape);
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get INSERT query string
	 *
	 * Compiles an insert query and returns the sql
	 *
	 * @param    string    the table to insert into
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledInsert($table = '', $reset = true)
	{
		if ($this->validateInsert($table) === false)
		{
			return false;
		}

		$sql = $this->_insert(
			$this->protect_identifiers(
				$this->QBFrom[0], true, null, false
			),
			array_keys($this->QBSet),
			array_values($this->QBSet)
		);

		if ($reset === true)
		{
			$this->resetWrite();
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Insert
	 *
	 * Compiles an insert string and runs the query
	 *
	 * @param         string    the table to insert data into
	 * @param         array     an associative array of insert values
	 * @param    bool $escape   Whether to escape values and identifiers
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function insert($table = '', $set = null, $escape = null)
	{
		if ($set !== null)
		{
			$this->set($set, '', $escape);
		}

		if ($this->validateInsert($table) === false)
		{
			return false;
		}

		$sql = $this->_insert(
			$this->protect_identifiers(
				$this->QBFrom[0], true, $escape, false
			),
			array_keys($this->QBSet),
			array_values($this->QBSet)
		);

		$this->resetWrite();

		return $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Validate Insert
	 *
	 * This method is used by both insert() and getCompiledInsert() to
	 * validate that the there data is actually being set and that table
	 * has been chosen to be inserted into.
	 *
	 * @param    string    the table to insert data into
	 *
	 * @return    string
	 */
	protected function validateInsert($table = '')
	{
		if (count($this->QBSet) === 0)
		{
			return ($this->db_debug) ? $this->display_error('db_must_use_set') : false;
		}

		if ($table !== '')
		{
			$this->QBFrom[0] = $table;
		}
		elseif ( ! isset($this->QBFrom[0]))
		{
			return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Replace
	 *
	 * Compiles an replace into string and runs the query
	 *
	 * @param    string    the table to replace data into
	 * @param    array     an associative array of insert values
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function replace($table = '', $set = null)
	{
		if ($set !== null)
		{
			$this->set($set);
		}

		if (count($this->QBSet) === 0)
		{
			return ($this->db_debug) ? $this->display_error('db_must_use_set') : false;
		}

		if ($table === '')
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}

		$sql = $this->_replace($this->protect_identifiers($table, true, null, false), array_keys($this->QBSet),
			array_values($this->QBSet));

		$this->resetWrite();

		return $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Replace statement
	 *
	 * Generates a platform-specific replace string from the supplied data
	 *
	 * @param    string    the table name
	 * @param    array     the insert keys
	 * @param    array     the insert values
	 *
	 * @return    string
	 */
	protected function _replace($table, $keys, $values)
	{
		return 'REPLACE INTO '.$table.' ('.implode(', ', $keys).') VALUES ('.implode(', ', $values).')';
	}

	//--------------------------------------------------------------------

	/**
	 * FROM tables
	 *
	 * Groups tables in FROM clauses if needed, so there is no confusion
	 * about operator precedence.
	 *
	 * Note: This is only used (and overridden) by MySQL and CUBRID.
	 *
	 * @return    string
	 */
	protected function _fromTables()
	{
		return implode(', ', $this->QBFrom);
	}

	//--------------------------------------------------------------------

	/**
	 * Get UPDATE query string
	 *
	 * Compiles an update query and returns the sql
	 *
	 * @param    string    the table to update
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledUpdate($table = '', $reset = true)
	{
		// Combine any cached components with the current statements
		$this->mergeCache();

		if ($this->validateUpdate($table) === false)
		{
			return false;
		}

		$sql = $this->_update($this->QBFrom[0], $this->QBSet);

		if ($reset === true)
		{
			$this->resetWrite();
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * UPDATE
	 *
	 * Compiles an update string and runs the query.
	 *
	 * @param    string $table
	 * @param    array  $set An associative array of update values
	 * @param    mixed  $where
	 * @param    int    $limit
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function update($table = '', $set = null, $where = null, $limit = null)
	{
		// Combine any cached components with the current statements
		$this->mergeCache();

		if ($set !== null)
		{
			$this->set($set);
		}

		if ($this->validateUpdate($table) === false)
		{
			return false;
		}

		if ($where !== null)
		{
			$this->where($where);
		}

		if ( ! empty($limit))
		{
			$this->limit($limit);
		}

		$sql = $this->_update($this->QBFrom[0], $this->QBSet);
		$this->resetWrite();

		return $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Validate Update
	 *
	 * This method is used by both update() and getCompiledUpdate() to
	 * validate that data is actually being set and that a table has been
	 * chosen to be update.
	 *
	 * @param    string    the table to update data on
	 *
	 * @return    bool
	 */
	protected function validateUpdate($table)
	{
		if (count($this->QBSet) === 0)
		{
			return ($this->db_debug) ? $this->display_error('db_must_use_set') : false;
		}

		if ($table !== '')
		{
			$this->QBFrom = [$this->protect_identifiers($table, true, null, false)];
		}
		elseif ( ! isset($this->QBFrom[0]))
		{
			return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param    string    the table to retrieve the results from
	 * @param    array     an associative array of update values
	 * @param    string    the where key
	 *
	 * @return    int    number of rows affected or FALSE on failure
	 */
	public function updateBatch($table, $set = null, $index = null, $batch_size = 100)
	{
		// Combine any cached components with the current statements
		$this->mergeCache();

		if ($index === null)
		{
			return ($this->db_debug) ? $this->display_error('db_must_use_index') : false;
		}

		if ($set === null)
		{
			if (empty($this->QBSet))
			{
				return ($this->db_debug) ? $this->display_error('db_must_use_set') : false;
			}
		}
		else
		{
			if (empty($set))
			{
				return ($this->db_debug) ? $this->display_error('updateBatch() called with no data') : false;
			}

			$this->setUpdateBatch($set, $index);
		}

		if (strlen($table) === 0)
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}

		// Batch this baby
		$affected_rows = 0;
		for ($i = 0, $total = count($this->QBSet); $i < $total; $i += $batch_size)
		{
			$this->query($this->_updateBatch($this->protect_identifiers($table, true, null, false),
				array_slice($this->QBSet, $i, $batch_size), $this->protect_identifiers($index)));
			$affected_rows += $this->affected_rows();
			$this->QBWhere = [];
		}

		$this->resetWrite();

		return $affected_rows;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch statement
	 *
	 * Generates a platform-specific batch update string from the supplied data
	 *
	 * @param    string $table  Table name
	 * @param    array  $values Update data
	 * @param    string $index  WHERE key
	 *
	 * @return    string
	 */
	protected function _updateBatch($table, $values, $index)
	{
		$ids = [];
		foreach ($values as $key => $val)
		{
			$ids[] = $val[$index];

			foreach (array_keys($val) as $field)
			{
				if ($field !== $index)
				{
					$final[$field][] = 'WHEN '.$index.' = '.$val[$index].' THEN '.$val[$field];
				}
			}
		}

		$cases = '';
		foreach ($final as $k => $v)
		{
			$cases .= $k." = CASE \n"
			          .implode("\n", $v)."\n"
			          .'ELSE '.$k.' END, ';
		}

		$this->where($index.' IN('.implode(',', $ids).')', null, false);

		return 'UPDATE '.$table.' SET '.substr($cases, 0, -2).$this->compileWhereHaving('QBWhere');
	}

	//--------------------------------------------------------------------

	/**
	 * The "setUpdateBatch" function.  Allows key/value pairs to be set for batch updating
	 *
	 * @param    array
	 * @param    string
	 * @param    bool
	 *
	 * @return    CI_DB_query_builder
	 */
	public function setUpdateBatch($key, $index = '', $escape = null)
	{
		$key = $this->batchObjectToArray($key);

		if ( ! is_array($key))
		{
			// @todo error
		}

		is_bool($escape) OR $escape = $this->_protect_identifiers;

		foreach ($key as $k => $v)
		{
			$index_set = false;
			$clean     = [];
			foreach ($v as $k2 => $v2)
			{
				if ($k2 === $index)
				{
					$index_set = true;
				}

				$clean[$this->protect_identifiers($k2, false, $escape)] = ($escape === false) ? $v2
					: $this->escape($v2);
			}

			if ($index_set === false)
			{
				return $this->display_error('db_batch_missing_index');
			}

			$this->QBSet[] = $clean;
		}

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Empty Table
	 *
	 * Compiles a delete string and runs "DELETE FROM table"
	 *
	 * @param    string    the table to empty
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function emptyTable($table = '')
	{
		if ($table === '')
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}
		else
		{
			$table = $this->protect_identifiers($table, true, null, false);
		}

		$sql = $this->_delete($table);
		$this->resetWrite();

		return $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate
	 *
	 * Compiles a truncate string and runs the query
	 * If the database does not support the truncate() command
	 * This function maps to "DELETE FROM table"
	 *
	 * @param    string    the table to truncate
	 *
	 * @return    bool    TRUE on success, FALSE on failure
	 */
	public function truncate($table = '')
	{
		if ($table === '')
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}
		else
		{
			$table = $this->protect_identifiers($table, true, null, false);
		}

		$sql = $this->_truncate($table);
		$this->resetWrite();

		return $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the truncate() command,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param    string    the table name
	 *
	 * @return    string
	 */
	protected function _truncate($table)
	{
		return 'TRUNCATE '.$table;
	}

	//--------------------------------------------------------------------

	/**
	 * Get DELETE query string
	 *
	 * Compiles a delete query string and returns the sql
	 *
	 * @param    string    the table to delete from
	 * @param    bool      TRUE: reset QB values; FALSE: leave QB values alone
	 *
	 * @return    string
	 */
	public function getCompiledDelete($table = '', $reset = true)
	{
		$this->returnDeleteSQL = true;
		$sql                   = $this->delete($table, '', null, $reset);
		$this->returnDeleteSQL = false;

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * Compiles a delete string and runs the query
	 *
	 * @param    mixed    the table(s) to delete from. String or array
	 * @param    mixed    the where clause
	 * @param    mixed    the limit clause
	 * @param    bool
	 *
	 * @return    mixed
	 */
	public function delete($table = '', $where = '', $limit = null, $reset_data = true)
	{
		// Combine any cached components with the current statements
		$this->mergeCache();

		if ($table === '')
		{
			if ( ! isset($this->QBFrom[0]))
			{
				return ($this->db_debug) ? $this->display_error('db_must_set_table') : false;
			}

			$table = $this->QBFrom[0];
		}
		elseif (is_array($table))
		{
			empty($where) && $reset_data = false;

			foreach ($table as $single_table)
			{
				$this->delete($single_table, $where, $limit, $reset_data);
			}

			return;
		}
		else
		{
			$table = $this->protect_identifiers($table, true, null, false);
		}

		if ($where !== '')
		{
			$this->where($where);
		}

		if ( ! empty($limit))
		{
			$this->limit($limit);
		}

		if (count($this->QBWhere) === 0)
		{
			return ($this->db_debug) ? $this->display_error('db_del_must_use_where') : false;
		}

		$sql = $this->_delete($table);
		if ($reset_data)
		{
			$this->resetWrite();
		}

		return ($this->returnDeleteSQL === true) ? $sql : $this->query($sql);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string    the table name
	 *
	 * @return    string
	 */
	protected function _delete($table)
	{
		return 'DELETE FROM '.$table.$this->compileWhereHaving('QBWhere')
		       .($this->QBLimit ? ' LIMIT '.$this->QBLimit : '');
	}

	//--------------------------------------------------------------------

	/**
	 * DB Prefix
	 *
	 * Prepends a database prefix if one exists in configuration
	 *
	 * @param    string    the table
	 *
	 * @return    string
	 */
	public function prefixTable($table = '')
	{
		if ($table === '')
		{
			$this->display_error('db_table_name_required');
		}

		return $this->dbprefix.$table;
	}

	//--------------------------------------------------------------------

	/**
	 * Set DB Prefix
	 *
	 * Set's the DB Prefix to something new without needing to reconnect
	 *
	 * @param    string    the prefix
	 *
	 * @return    string
	 */
	public function setPrefix($prefix = '')
	{
		return $this->dbprefix = $prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Track Aliases
	 *
	 * Used to track SQL statements written with aliased tables.
	 *
	 * @param    string    The table to inspect
	 *
	 * @return    string
	 */
	protected function trackAliases($table)
	{
		if (is_array($table))
		{
			foreach ($table as $t)
			{
				$this->trackAliases($t);
			}

			return;
		}

		// Does the string contain a comma?  If so, we need to separate
		// the string into discreet statements
		if (strpos($table, ',') !== false)
		{
			return $this->trackAliases(explode(',', $table));
		}

		// if a table alias is used we can recognize it by a space
		if (strpos($table, ' ') !== false)
		{
			// if the alias is written with the AS keyword, remove it
			$table = preg_replace('/\s+AS\s+/i', ' ', $table);

			// Grab the alias
			$table = trim(strrchr($table, ' '));

			// Store the alias, if it doesn't already exist
			if ( ! in_array($table, $this->QBAliasedTables))
			{
				$this->QBAliasedTables[] = $table;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.
	 *
	 * @param    bool $select_override
	 *
	 * @return    string
	 */
	protected function compileSelect($select_override = false)
	{
		// Combine any cached components with the current statements
		$this->mergeCache();

		// Write the "select" portion of the query
		if ($select_override !== false)
		{
			$sql = $select_override;
		}
		else
		{
			$sql = ( ! $this->QBDistinct) ? 'SELECT ' : 'SELECT DISTINCT ';

			if (count($this->QBSelect) === 0)
			{
				$sql .= '*';
			}
			else
			{
				// Cycle through the "select" portion of the query and prep each column name.
				// The reason we protect identifiers here rather than in the select() function
				// is because until the user calls the from() function we don't know if there are aliases
				foreach ($this->QBSelect as $key => $val)
				{
					$no_escape            = isset($this->QBNoEscape[$key]) ? $this->QBNoEscape[$key] : null;
					$this->QBSelect[$key] = $this->protect_identifiers($val, false, $no_escape);
				}

				$sql .= implode(', ', $this->QBSelect);
			}
		}

		// Write the "FROM" portion of the query
		if (count($this->QBFrom) > 0)
		{
			$sql .= "\nFROM ".$this->_fromTables();
		}

		// Write the "JOIN" portion of the query
		if (count($this->QBJoin) > 0)
		{
			$sql .= "\n".implode("\n", $this->QBJoin);
		}

		$sql .= $this->compileWhereHaving('QBWhere')
		        .$this->compileGroupBy()
		        .$this->compileWhereHaving('QBHaving')
		        .$this->compileOrderBy(); // ORDER BY

		// LIMIT
		if ($this->QBLimit)
		{
			return $this->_limit($sql."\n");
		}

		return $sql;
	}

	//--------------------------------------------------------------------

	/**
	 * Compile WHERE, HAVING statements
	 *
	 * Escapes identifiers in WHERE and HAVING statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of whether
	 * where(), orWhere(), having(), orHaving are called prior to from(),
	 * join() and prefixTable is added only if needed.
	 *
	 * @param    string $qb_key 'QBWhere' or 'QBHaving'
	 *
	 * @return    string    SQL statement
	 */
	protected function compileWhereHaving($qb_key)
	{
		if (count($this->$qb_key) > 0)
		{
			for ($i = 0, $c = count($this->$qb_key); $i < $c; $i++)
			{
				// Is this condition already compiled?
				if (is_string($this->{$qb_key}[$i]))
				{
					continue;
				}
				elseif ($this->{$qb_key}[$i]['escape'] === false)
				{
					$this->{$qb_key}[$i] = $this->{$qb_key}[$i]['condition'];
					continue;
				}

				// Split multiple conditions
				$conditions = preg_split(
					'/((?:^|\s+)AND\s+|(?:^|\s+)OR\s+)/i',
					$this->{$qb_key}[$i]['condition'],
					-1,
					PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
				);

				for ($ci = 0, $cc = count($conditions); $ci < $cc; $ci++)
				{
					if (($op = $this->_get_operator($conditions[$ci])) === false
					    OR
					    ! preg_match('/^(\(?)(.*)('.preg_quote($op, '/').')\s*(.*(?<!\)))?(\)?)$/i', $conditions[$ci],
						    $matches)
					)
					{
						continue;
					}

					// $matches = array(
					//	0 => '(test <= foo)',	/* the whole thing */
					//	1 => '(',		/* optional */
					//	2 => 'test',		/* the field name */
					//	3 => ' <= ',		/* $op */
					//	4 => 'foo',		/* optional, if $op is e.g. 'IS NULL' */
					//	5 => ')'		/* optional */
					// );

					if ( ! empty($matches[4]))
					{
						$this->isLiteral($matches[4]) OR $matches[4] = $this->protect_identifiers(trim($matches[4]));
						$matches[4] = ' '.$matches[4];
					}

					$conditions[$ci] = $matches[1].$this->protect_identifiers(trim($matches[2]))
					                   .' '.trim($matches[3]).$matches[4].$matches[5];
				}

				$this->{$qb_key}[$i] = implode('', $conditions);
			}

			return ($qb_key === 'QBHaving' ? "\nHAVING " : "\nWHERE ")
			       .implode("\n", $this->$qb_key);
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Compile GROUP BY
	 *
	 * Escapes identifiers in GROUP BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of wether
	 * groupBy() is called prior to from(), join() and prefixTable is added
	 * only if needed.
	 *
	 * @return    string    SQL statement
	 */
	protected function compileGroupBy()
	{
		if (count($this->QBGroupBy) > 0)
		{
			for ($i = 0, $c = count($this->QBGroupBy); $i < $c; $i++)
			{
				// Is it already compiled?
				if (is_string($this->QBGroupBy[$i]))
				{
					continue;
				}

				$this->QBGroupBy[$i] = ($this->QBGroupBy[$i]['escape'] === false OR
				                        $this->isLiteral($this->QBGroupBy[$i]['field']))
					? $this->QBGroupBy[$i]['field']
					: $this->protect_identifiers($this->QBGroupBy[$i]['field']);
			}

			return "\nGROUP BY ".implode(', ', $this->QBGroupBy);
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Compile ORDER BY
	 *
	 * Escapes identifiers in ORDER BY statements at execution time.
	 *
	 * Required so that aliases are tracked properly, regardless of wether
	 * orderBy() is called prior to from(), join() and prefixTable is added
	 * only if needed.
	 *
	 * @return    string    SQL statement
	 */
	protected function compileOrderBy()
	{
		if (is_array($this->QBOrderBy) && count($this->QBOrderBy) > 0)
		{
			for ($i = 0, $c = count($this->QBOrderBy); $i < $c; $i++)
			{
				if ($this->QBOrderBy[$i]['escape'] !== false && ! $this->isLiteral($this->QBOrderBy[$i]['field']))
				{
					$this->QBOrderBy[$i]['field'] = $this->protect_identifiers($this->QBOrderBy[$i]['field']);
				}

				$this->QBOrderBy[$i] = $this->QBOrderBy[$i]['field'].$this->QBOrderBy[$i]['direction'];
			}

			return $this->QBOrderBy = "\nORDER BY ".implode(', ', $this->QBOrderBy);
		}
		elseif (is_string($this->QBOrderBy))
		{
			return $this->QBOrderBy;
		}

		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param    object
	 *
	 * @return    array
	 */
	protected function objectToArray($object)
	{
		if ( ! is_object($object))
		{
			return $object;
		}

		$array = [];
		foreach (get_object_vars($object) as $key => $val)
		{
			// There are some built in keys we need to ignore for this conversion
			if ( ! is_object($val) && ! is_array($val) && $key !== '_parent_name')
			{
				$array[$key] = $val;
			}
		}

		return $array;
	}

	//--------------------------------------------------------------------

	/**
	 * Object to Array
	 *
	 * Takes an object as input and converts the class variables to array key/vals
	 *
	 * @param    object
	 *
	 * @return    array
	 */
	protected function batchObjectToArray($object)
	{
		if ( ! is_object($object))
		{
			return $object;
		}

		$array  = [];
		$out    = get_object_vars($object);
		$fields = array_keys($out);

		foreach ($fields as $val)
		{
			// There are some built in keys we need to ignore for this conversion
			if ($val !== '_parent_name')
			{
				$i = 0;
				foreach ($out[$val] as $data)
				{
					$array[$i++][$val] = $data;
				}
			}
		}

		return $array;
	}

	//--------------------------------------------------------------------

	/**
	 * Start Cache
	 *
	 * Starts QB caching
	 *
	 * @return    CI_DB_query_builder
	 */
	public function startCache()
	{
		$this->QBCaching = true;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Stop Cache
	 *
	 * Stops QB caching
	 *
	 * @return    CI_DB_query_builder
	 */
	public function stopCache()
	{
		$this->QBCaching = false;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Flush Cache
	 *
	 * Empties the QB cache
	 *
	 * @return    CI_DB_query_builder
	 */
	public function flushCache()
	{
		$this->resetRun([
			'QBCacheSelect'   => [],
			'QBCacheFrom'     => [],
			'QBCacheJoin'     => [],
			'QBCacheWhere'    => [],
			'QBCacheGroup'    => [],
			'QBCacheHaving'   => [],
			'QBCacheOrderBy'  => [],
			'QBCacheSet'      => [],
			'QBCacheExists'   => [],
			'QBCacheNoEscape' => [],
		]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Merge Cache
	 *
	 * When called, this function merges any cached QB arrays with
	 * locally called ones.
	 *
	 * @return    void
	 */
	protected function mergeCache()
	{
		if (count($this->QBCacheExists) === 0)
		{
			return;
		}
		elseif (in_array('select', $this->QBCacheExists, true))
		{
			$qb_no_escape = $this->QBCacheNoEscape;
		}

		foreach (array_unique($this->QBCacheExists) as $val) // select, from, etc.
		{
			$qb_variable  = 'qb_'.$val;
			$qb_cache_var = 'qb_cache_'.$val;
			$qb_new       = $this->$qb_cache_var;

			for ($i = 0, $c = count($this->$qb_variable); $i < $c; $i++)
			{
				if ( ! in_array($this->{$qb_variable}[$i], $qb_new, true))
				{
					$qb_new[] = $this->{$qb_variable}[$i];
					if ($val === 'select')
					{
						$qb_no_escape[] = $this->QBNoEscape[$i];
					}
				}
			}

			$this->$qb_variable = $qb_new;
			if ($val === 'select')
			{
				$this->QBNoEscape = $qb_no_escape;
			}
		}

		// If we are "protecting identifiers" we need to examine the "from"
		// portion of the query to determine if there are any aliases
		if ($this->_protect_identifiers === true && count($this->QBCacheFrom) > 0)
		{
			$this->trackAliases($this->QBFrom);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Is literal
	 *
	 * Determines if a string represents a literal value or a field name
	 *
	 * @param    string $str
	 *
	 * @return    bool
	 */
	protected function isLiteral($str)
	{
		$str = trim($str);

		if (empty($str) OR ctype_digit($str) OR (string)(float)$str === $str OR
		    in_array(strtoupper($str), ['TRUE', 'FALSE'], true)
		)
		{
			return true;
		}

		static $_str;

		if (empty($_str))
		{
			$_str = ($this->_escape_char !== '"')
				? ['"', "'"] : ["'"];
		}

		return in_array($str[0], $_str, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Reset Query Builder values.
	 *
	 * Publicly-visible method to reset the QB values.
	 *
	 * @return    CI_DB_query_builder
	 */
	public function resetQuery()
	{
		$this->resetSelect();
		$this->resetWrite();

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 *
	 * @param    array    An array of fields to reset
	 *
	 * @return    void
	 */
	protected function resetRun($qb_reset_items)
	{
		foreach ($qb_reset_items as $item => $default_value)
		{
			$this->$item = $default_value;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder values.  Called by the get() function
	 *
	 * @return    void
	 */
	protected function resetSelect()
	{
		$this->resetRun([
			'QBSelect'        => [],
			'QBFrom'          => [],
			'QBJoin'          => [],
			'QBWhere'         => [],
			'QBGroupBy'       => [],
			'QBHaving'        => [],
			'QBOrderBy'       => [],
			'QBAliasedTables' => [],
			'QBNoEscape'      => [],
			'QBDistinct'      => false,
			'QBLimit'         => false,
			'QBOffset'        => false,
		]);
	}

	//--------------------------------------------------------------------

	/**
	 * Resets the query builder "write" values.
	 *
	 * Called by the insert() update() insertBatch() updateBatch() and delete() functions
	 *
	 * @return    void
	 */
	protected function resetWrite()
	{
		$this->resetRun([
			'QBSet'     => [],
			'QBFrom'    => [],
			'QBJoin'    => [],
			'QBWhere'   => [],
			'QBOrderBy' => [],
			'QBKeys'    => [],
			'QBLimit'   => false,
		]);
	}

	//--------------------------------------------------------------------

}