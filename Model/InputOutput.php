<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class InputOutput
{
    use \Segment\utilities\AbstractClassNamesGetter;

    /**
     * The DAO
     * @var QueryHandler 
     */
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
    protected $where_values = []; /* Values object containing the values to be bound to the finished statement.
     * Order matters, as must correspond to the order the '?' appear in WHERE statement. */
    protected $addendum = ''; /* Closing statements such as ORDER BY, GROUP BY, LIMIT.
     * The values to be inserted separated by commas when making INSERT statement. */
    protected $put_values = array(); /* list of values whose position corresponds w/ the
     * reciprocal position of the column they are assigned to in the $columns array */
    protected $output = null;
    protected static $thread_count = 0;
    /**
     * Those statements that are prerequisites of this one.
     * @var InputOutput 
     */
    protected $pre_statements = array();
    protected $db_type_1;
    protected $db_type_2;
    protected $db_list = array();
    /**
     * Object describing the requirements of each column of each table.
     * @var DbDescription
     */
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
        $this->where_values = array_merge($this->where_values, $values);
        error_log(__METHOD__ . " the where_values: " . print_r($this->where_values, TRUE));
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

    //abstract public function getWhere();

//    abstract public function getAddendum();

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

    /**
     * @param Column $table Column object containing the Table to be queried. If more than one Table
     *      in InputOutput then the Column object's column will be the key for a join.
     */
    abstract public function setTable(Column $table);

    abstract public function getAddendumClause();
    abstract public function getDeleteClause();
    abstract public function getFromClause();
    abstract public function getSetClause();
    abstract public function getWhereClause();
    abstract public function getReturnClause();
    /**
     * Assigns the addendum string from Addendum's getAddendum()
     * @param Addendum $builder
     */
    public function addendAddendum(Addendum $builder)
    {
        $addition = $builder->getAddendum();
        $this->addendum .= strlen($this->addendum)>0 ? ', ' . $addition : $addition;
    }
}