<?php

namespace Segment\Model;

/**
 * Singleton Database Access Object
 */
interface QueryHandler
{
    public static function getQueryHandler();
    public static function describeTable($table_name_string);       
    public function query($query_statement_string, array $where_values, QHFetchArgBuilder $fetch);
}    


interface QHFetchArgBuilder
{
    public function fetch(Statement $stmt, $call_id);
}

abstract class Table
{
    private $db;
    private $table;
    
    abstract public function __construct($table, $db);
    
    public function getDb()
    {
        return $this->db;
    }
    
    public function getTable()
    {
        return $this->table;
    }
}

abstract class Column
{
    use search_value;
    
    abstract public function __construct(Table $table, $column);
    abstract public function getColumn();
    
    public function getTable()
    {
        return $this->table;
    }
    
    public function getDb()
    {
        return $this->db;
    }
    
    abstract public function getReturnColumn();
    
    abstract public function getFromColumn();
    
    abstract public function getWhereColumn();
    
    abstract public function getInsertColumn();
    
    abstract public function getUpdateColumn();
    
    abstract public function getDeleteColumn();
    
    abstract public function getAddendumColumn();
}


trait search_value
{
    protected $values = [];
    protected $count = 0;
    
    /**
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
    
    /**
     * 
     * @return integer
     */
    public function getValuesQuantity()
    {
        return $this->count;
    }
    
    public function __toString()
    {
        $values = $this->getValues();
        $answer = '';
        for($i=0,$max=count($values);$i<$max;$i++){
            if(empty($answer))
                $answer = $values[$i];
            else
                $answer .= ", $values[$i]";
        }
        return $answer;
    }
}

abstract class SearchValues
{
    public function getValues()
    {
        return $this->values;
    }
    
    public function getValuesQuantity()
    {
        return $this->count;
    }
    
    public function __toString()
    {
        $values = $this->getValues();
        $answer = '';
        for($i=0,$max=count($values);$i<$max;$i++){
            if(strlen($answer)===0)
                $answer .= $values[$i];
            else
                $answer .= ", $values[$i]";
        }
        return $answer;
    }
}

interface Expression
{ }

interface Statement
{
    public function __construct(InputOutput $collection, $type);
    
    /**
     * @return string Statement as a string
     */
    public function getStatement();
    
    /**
     * @return InputOutput The StatementBuilder source
     */
    public function getInputOutput();
}

abstract class AbstractStatement implements Statement
{
    use search_value;

    protected $input_output;
    
    const GET = 'get';
    const DELETE = 'delete';
    const PUT = 'put';
    const POST = 'post';
    
    
    public function getInputOutput()
    {
        return clone $this->input_output;
    }
    
    public function getDbs()
    {
        return $this->input_output->getDbs();
    }
    
    public function getTables()
    {
        return $this->input_output->getTables();
    }
    
    public function getStatement()
    {
        return $this->getValues()[0];
    }
    
    abstract public function getGetStatement();
    abstract public function getPostStatement();
    abstract public function getPutStatement();
    abstract public function getDeleteStatement();
}
        
interface MultiStatement
{
    public function addStatements(Statement $stmt1, Statement ...$stmt_multi);
}

abstract class Variable implements Expression
{
    use search_value;
    
    protected $values;
    /**
     * 
     * @param string $value Required to conform to DB expression syntax
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(1);
        $this->values->offsetSet(0, $value);
        $this->count++;
    }
}


abstract class AverageExpression implements Expression
{
    use search_value;
    /**
     * Finds the mathematical mean of a column
     * @param Column $clmn
     */
    public function __construct(Column $clmn)
    {
        $this->values = new \SplFixedArray(1);
        $this->values->offsetSet(0, $clmn);
        $this->count++;
    }
}


abstract class ConcatenateExpression implements Expression
{
    use search_value;

    /**
     * Constructor
     * @param \Segment\Model\SearchValues|\Segment\Model\Expression $value1
     * @param \Segment\Model\SearchValues|\Segment\Model\Expression $values Variable-length variable.
     */
    public function __construct($value1, ...$values)
    {
        $count = count($values);
        $v_arr = new \SplFixedArray($count+1);
        $v_arr->offsetSet(0, $value1);
        for($i=0;$i<$count;$i++){
            $v_arr->offsetSet($i+1, $values[$i]);
        }
        $this->values = $v_arr;
        $this->count = $v_arr->count();
    }
}


abstract class AssignmentExpression implements Expression
{
    use search_value;

    const VALUE = 0;
    const VARIABLE = 1;
    
    /**
     * 
     * @param SearchValues|Statement|Expression $value
     * @param Variable $var
     */
    public function __construct($value, Variable $var)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::VALUE, $value);
        $this->values->offsetSet(self::VARIABLE, $var);
        $this->count = $this->values->count();
    }
}


abstract class AliasExpression implements Expression
{
    use search_value;
    protected $parenthesis;
    const VALUE = 0;
    const ALIAS = 1;
    
    /**
     * Only the first value will be used.
     * @param Column|Statement $value
     * @param production\StringValue $alias
     * @param boolean $parenthesis
     */
    public function __construct($value, \Segment\Model\production\StringValue $alias, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(2);
        $this->parenthesis = $parenthesis;
        $this->values->offsetSet(self::VALUE, $value);
        $this->values->offsetSet($index, $alias);
        $this->count = $this->values->count();
    }
    
    public function usesParenthesis()
    {
        return $this->parenthesis;
    }
}


abstract class EqualityExpression implements Expression
{
    use search_value;

    const FIELD = 0;
    const VALUE = 1;
    /**
     * Boolean equality expression
     * @param Column|Expression $field
     * @param production\SingleValue|Expression|Variable $value
     */
    public function __construct($field, $value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUE, $value);
        $this->count = 2;
    }
}


abstract class GreaterExpression implements Expression
{
    use search_value;

    const ALL = -1;
    const ANY = 1;
    const NONE = 0;
    const FIELD = 1000;
    const VALUES = 1001;
    
    /**
     * Ordinal "greater than" expression
     * @param Column|Expression $field The column or expression to be tested
     * @param production\SingleValue|production\AnyAllValues|Statement|Variable $values The values, whether static
     *  or dynammic, to be tested against
     */
    public function __construct($field, $values, $options = self::NONE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUES, $values);
        $this->count = $this->values->count;
    }
}


abstract class LesserExpression implements Expression
{
    use search_value;
    
    const ALL = -1;
    const ANY = 1;
    const NONE = 0;
    const FIELD = 1000;
    const VALUES = 1001;
    
    /**
     * Ordinal "less than" expression
     * @param Column|Expression $field The column or expression to be tested
     * @param SingleValue|AnyAllValues|Statement|Variable $values The values, whether
     *  static or dynammic, to be tested against
     */
    public function __construct($field, $values, $options = self::NONE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUES, $values);
        $this->count = $this->values->count;
    }
}


abstract class InSetExpression implements Expression
{
    use search_value;

    const ITEM = 1000;
    const SET = 1001;
    
    /**
     * Boolean set-membership-checking expression
     * @param Expression|Column $field
     * @param \Segment\Model\production\AnyAllValues $values
     */
    public function __construct($item, $set)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::ITEM, $field);
        $this->values->offsetSet(self::SET, $values);
        $this->count = $this->values->count;
    }
}


abstract class BetweenExpression implements Expression
{
    use search_value;

    const EXCL = 2;
    const NOT = 4;
    protected $exclusive_between;
    protected $not_between;
    const FIELD = 0;
    const VALUES = 1;

    /**
     * Boolean equality expression
     * @param Expression|Column $field
     * @param \Segment\Model\production\BetweenValues $values
     * @param integer $options
     */
    public function __construct($field,  production\BetweenValues $values, $options = 0)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUES, $values);
        $this->exclusive_between = $options | ~self::EXCL;
        $this->not_between = $options | ~self::NOT;
        $this->count = $this->values->count;
    }
    
    public function isExclusive()
    {
        return $this->exclusive_between;
    }
    
    public function isInvertedSet()
    {
        return $this->not_between;
    }
}


abstract class ModuloExpression implements Expression
{
    use search_value;

    const DIVIDEND = 0;
    const DIVISOR = 1;
    protected $parenthesis;
    
    /**
     * 
     * @param IntegerValue $dividend Only the first value will be used.
     * @param IntegerValue $divisor Only the first value will be used.
     * @param boolean $parenthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($dividend, $divisor, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args());
        if((is_a($dividend, '\Segment\Model\production\IntegerValue')||
                is_a($dividend,'\Segment\Model\production\Variable'))&&
                (is_a($divisor,'\Segment\Model\production\IntegerValue')||
                is_a($divisor, '\Segment\Model\production\Variable'))){
            $this->parenthesis = $parenthesis;
            $this->values = new \SplFixedArray(2);
            $this->values->offsetSet(self::DIVIDEND, $dividend);
            $this->values->offsetSet(self::DIVISOR, $divisor);
            $this->count = $this->values->count;
        }
    }
    
    public function usesParenthesis()
    {
        return $this->parenthesis;
    }
}

abstract class InputOutput
{
    use \Segment\utilities\AbstractClassNamesGetter;
    
    protected $qh;
    protected $return_builder;
    protected $from_builder;
    protected $where_builder;
    protected $set_builder;
    protected $delete_builder;
    protected $columns = array(); /* key value pairs where table name is key, column is value. */
    protected $tables = array(); /* array of tables. More than one will be joined by NATURAL JOIN.
     * First table with key 0, subsequent tables must have column name to be naturally joined as key. */
    protected $where = ''; /* WHERE statement with '?' in place of values */
    protected $where_values; /* Values object containing the values to be bound to the finished statement.
     * Order matters, as must correspond to the order the '?' appear in WHERE statement. */
    protected $addendum = ''; /* Closing statements such as ORDER BY, GROUP BY, LIMIT.
     * The values to be inserted separated by commas when making INSERT statement. */
    protected $put_values = array(); /* list of values whose position corresponds w/ the 
     * reciprocal position of the column they are assigned to in the $columns array */
    protected $output = null;
    protected static $thread_count = 0;
    protected $pre_statements = array();
    protected $db_type_1;
    protected $db_type_2;
    protected $db_list = array();
    protected $db_description;

    function __construct(InputOutputArgvBuilder $obj, QueryHandler $PDOOutput,
            QHFetchArgBuilder $qh_fetch_arg = NULL)
    {
        $this->initialize($obj);
        $this->qh = $PDOOutput;
        $this->qh_fetch_arg = isset($qh_fetch_arg) ? $qh_fetch_arg : new AssocQHFetchArgBuilder();
    }
    
    /**
     * Add search values to be sought in search clause to end of repository.
     * @param SearchValues $values Variable-length variable. The values to be added
     */
    public function setValuesEnd(SearchValues ...$values)
    {
        array_merge($this->where_values, $values);
    }
    
    /**
     * Add search values to be sought in search clause to repository before an index point.
     * @param integer $index The index the values will be added before.
     * @param SearchValues $values Variable-length variable. The values to be added
     */
    public function setValuesBefore($index, SearchValues ...$values)
    {
        $temp = array_slice($this->where_values, $index);
        $this->values = array_merge($temp, $values, $this->where_values);
    }
    
    public function setPreStatements(Statement $stmnt)
    {
        $this->pre_statements[] = $stmnt;
    }

    public function initialize(DelayedBuilder $obj)
    {
        if(is_a($obj, 'WhereClauseBuilder')&&!empty($this->where_builder)){
            $this->where_builder = $obj;
        } else if(is_a($obj, 'SelectClauseBuilder')&&!empty($this->return_builder)){
            $this->return_builder = $obj;
        } else if(is_a($obj, 'DeleteClauseBuilder')&&!empty($this->delete_builder)){
            $this->delete_builder = $obj;
        } else if(is_a($obj, 'SetClauseBuilder')&&!empty($this->set_builder)){
            $this->set_builder = $obj;
        } else if(is_a($obj, 'FromClauseBuilder')&&!empty($this->from_builder)){
            $this->from_builder = $obj;
        }
    }
    
    /**
     * Return (PHP) Database Object placeholder variable string
     * @param string $var_as_str
     * @return string
     */
    public function getPdoPlaceholder($var_as_str)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__CLASS__, __METHOD__, func_get_args());
        return '?';
    }
    
    public function __clone()
    {
        $this->delete_builder = clone $this->delete_builder;
        $this->from_builder = clone $this->from_builder;
        $this->return_builder = clone $this->return_builder;
        $this->where_builder = clone $this->where_builder;
        $this->pre_statements = \Segment\utilities\Utilities::arrayCopy($this->pre_statements);
        $this->db_list = \Segment\utilities\Utilities::arrayCopy($this->db_list);
        $this->db_type_1 = $this->db_type_1;
        $this->db_type_2 = $this->db_type_2;
        $this->tables = \Segment\utilities\Utilities::arrayCopy($this->tables);
    }
    
    public function cloneDelete()
    {
        return clone $this->delete_builder;
    }
    
    public function cloneFrom()
    {
        return clone $this->from_builder;
    }
    
    public function cloneReturn()
    {
        return clone $this->return_builder;
    }
    
    public function cloneSet()
    {
        return clone $this->set_builder;
    }
    
    public function cloneWhere()
    {
        return clone $this->where_builder;
    }
    
    public function getDbs()
    {
        return \Segment\utilities\Utilities::arrayCopy($this->db_list);
    }
    public function getTables()
    {
        return \Segment\utilities\Utilities::arrayCopy($this->tables);
    }

    abstract public function getColumns();

    /**
     * Returns the values to be sought
     * @return array<SearchValues> The values
     */
    abstract public function getWhereValues();

    abstract public function getWhere();

    abstract public function getPutValues();

    protected function get($statement_class_name)
    {
        if(!is_string($statement_class_name)){
            trigger_error('get expected Argument 1 to be String', E_USER_WARNING);
        }
        $answer = array();
        $statement = new $statement_class_name();
        if(is_a($statement, 'Statement'))
            $answer = $this->qh->query(
                    $statement->getGetStatement($this), $this->getWhereValues(), $this->qh_fetch_arg);
        return $answer;
    }

    protected function post($statement_class_name)
    {
        if(!is_string($statement_class_name)){
            trigger_error('post expected Argument 1 to be String', E_USER_WARNING);
        }
        $answer = array();
        $statement = new $statement_class_name();
        if(is_a($statement, 'Statement'))
                $answer = $this->qh->query($statement->getPostStatement($this), $this->getWhereValues());
        return $answer;
    }

    protected function put($statement_class_name)
    {
        if(!is_string($statement_class_name)){
            trigger_error('put expected Argument 1 to be String', E_USER_WARNING);
        }
        $answer = array();
        $statement = new $statement_class_name();
        if(is_a($statement, 'Statement'))
                $answer = $this->qh->query($statement->getPutStatement($this), $this->getWhereValues());
        return $answer;
    }

    protected function delete($statement_class_name)
    {
        if(!is_string($statement_class_name)){
            trigger_error('delete expected Argument 1 to be String', E_USER_WARNING);
        }
        $answer = array();
        $statement = new $statement_class_name();
        if(is_a($statement, 'Statement'))
                $answer = $this->qh->query($statement->getDeleteStatement($this), $this->getWhereValues());
        return $answer;
    }

    public function describe()
    {
        $answer = array();
        foreach ($this->tables as $key => $value) {
            $answer[$value] = $this->qh->describeTable($value);
        }
        return $answer;
    }
    
    abstract protected function setDb($db);
    
    
    public function setDescription(DbDescription $description)
    {
        $this->description = $description;
    }
    
    abstract public function setTable($table);
    
    abstract public function getAddendumClause();
    abstract public function getDeleteClause();
    abstract public function getFromClause();
    abstract public function getSetClause();
    abstract public function getWhereClause();
}


abstract class DelayedBuilder
{
    protected $queue;
    
    /**
     * 
     * @param Callable $func_n
     * @param mixed $args Variable length argument
     */
    public function setCallQueue(callable $func_n, ...$args)
    {
        $this->queue[] = [$func_n => $args];
    }
    
    protected function getCallQueue()
    {
        return $this->queue;
    }
    
    public function callQueue()
    {
        $queue = $this->getCallQueue();
        foreach($queue as $call => $args){
            $this->$call($args);
        }
        $this->queue = array();
    }
}

interface SelectClauseBuilder
{
    
    /**
     * Add a column to be returned by query
     * @param Column $clmn
     */
    public function addReturnColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the mean value of which to be returned.
     * @param Column $clmn
     */
    public function addReturnMeanColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the unique values found of which to be returned
     * @param Column $clmn
     */
    public function addReturnUniqueColumn(Column $clmn);
    
    /**
     * Add a column, the modal average of which to be returned
     * @param Column $clmn
     */
    public function addReturnModeColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the median average of which to be returned
     * @param Column $clmn
     */
    public function addReturnMedianColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the maximum value of which to be returned
     * @param Column $clmn
     */
    public function addReturnMaxColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the minimum value of which to be returned
     * @param Column $clmn
     */
    public function addReturnMinColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add a column, the values of which to be sorted
     * @param Column $clmn
     * @param boolean $asc Ascending: true. Descending: false.
     */
    public function addReturnSortColumn(Column $clmn, $asc = TRUE);
    
    /**
     * Add limitation to number of records returned
     * @param boolean $absolute_amnt Regarding $amnt: Scalar: TRUE; Percentage: FALSE
     * @param integer $amnt The number, or percentage, of records returned.
     * @param boolean $absolute_start Optional. Default TRUE. Regarding $start: Scalar: TRUE; Percentage: FALSE
     * @param integer $start Optional. Default 0 (zero). The starting index or percentage
     */
    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0);
    
    public function addReturnCountColumn(Column $clmn, production\StringValue $alias = NULL);
    
    /**
     * Add expression to be executed.
     * @param Expression $exp
     * @param boolean $parenthesis TRUE expression to be wrapped in parenthesis.
     * @param production\StringValue $alias
     */
    public function addReturnExpression(Expression $exp, $parenthesis = FALSE, production\StringValue $alias = NULL);
    
    /**
     * All of the records from each query together.
     * @param Statement $first_var First query of the union
     * @param Statement $var_comp One or more additional queries of the union
     */
    public function addUnion(Statement $first_var, Statement ...$var_comp);
    
    /**
     * An intersection of the results of each subquery.
     * @param Statement $first_var First query of the intersection
     * @param Statement $var_comp One or more additional queries of the intersection
     */
    public function addIntersection(Statement $first_var, Statement ...$var_comp);
}

interface SearchClauseBuilder
{
    /**
     * Query will require column of records to equal a supplied value. Optionally, the 
     * value may be derived dynamically from another query.
     * @param Column $clmn
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqual(Column $clmn, production\SingleValue $value = NULL, Statement $var_comp = NULL,
            $required_param = TRUE);
    
    /**
     * Query will require all records to equal any supplied value for the respective column.
     * Values for comparison may come in part or whole another query.
     * @param Column $clmn
     * @param Statement $var_comp
     * @param AnyAllValues $values
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqualAny(Column $clmn, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all records to be within a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive Exclusive: true. Inclusive: false.
     * @param BetweenValues $values
     * @param Statement $var_comp1 Least value
     * @param Statement $var_comp2 Most value
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchRange(Column $clmn, $exclusive = TRUE, production\BetweenValues $values = NULL,
            Statement $var_comp1 = NULL, Statement $var_comp2 = NULL, $required_param = TRUE);
    
    /**
     * Query will require all records to be outside a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive
     * @param BetweenValues $values
     * @param Statement $var_comp1
     * @param Statement $var_comp2
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchNotRange(Column $clmn, $exclusive = TRUE, production\BetweenValues $values = NULL,
            Statement $var_comp1 = NULL, Statement $var_comp2 = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values greater than the comparison
     * for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreater(Column $clmn, $exclusive = TRUE, production\SingleValue $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values greater than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAny(Column $clmn, $exclusive = TRUE, production\AnyAllValues $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values greater than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAll(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values less than the comparison
     * value for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesser(Column $clmn, $exclusive = TRUE, production\SingleValue $value = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values less than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAny(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    /**
     * Query will require all returned records to have values less than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAll(Column $clmn, $exclusive = TRUE, production\AnyAllValues $values = NULL,
            Statement $var_comp = NULL, $required_param = TRUE);
    
    public function addSearchWildcard(Column $clmn, production\SingleValue $value, $required_param = TRUE);
    
}

interface SetClauseBuilder
{
    public function addSet(Column $target, SearchValues $value);
    public function getClause();
}

abstract class InsertSetClauseBuilder extends DelayedBuilder implements SetClauseBuilder
{
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
}

abstract class UpdateSetClauseBuilder extends DelayedBuilder implements SetClauseBuilder
{
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
}


interface DeleteClauseBuilder
{
    /**
     * Composes DELETE clause string based on the tables in scope for query
     * @param array<string> $tables Array of table names
     */
    public function getClause($tables);
}


interface FromClauseBuilder
{
    
    /**
     * Composes FROM clause string based on the tables in scope for query
     * @param array<string> $tables Array of table names
     */
    public function getClause($tables);
}


interface WhereClauseBuilder extends SearchClauseBuilder
{
    public function __construct(InputOutput $parent);
    public function __invoke();
    public function getClause();
    public function addend($where_targets, $operators, $value_var, $value_qt, $interstitial);
    /**
     * Returns where clause sought values
     * @return array<SearchValues> values to be searched
     */
    public function getValues();
}

interface DbDescripitonFetcher //Short name DbDescripFetch. Use short name as root name of concrete implementations.
{
    /**
     * Return Records of administrative descriptions of all of the database columns
     *     via the DbDescripton class.
     * @param string $table Optional. Name of a DB table. Will limit descripton to that table's columns
     * @return DbDescription
     */
    public function getDescription($table = FALSE);
}
