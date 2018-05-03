<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class InputOutputArgvBuilder
{
    private $columns = array(); /* key value pairs where table name is key, columns 
     * are in an array the key points to. */
    private $tables = array(); /* array of tables. More than one will be joined by NATURAL JOIN.
     * First table with key 0, subsequent tables must have column name to be naturally joined as key. */
    private $where = ''; /* WHERE statement with ':column_name' in place of values */
    private $where_values = array(); /* Those values to replace variables. Key/value pairs with
     * ':column_name' as key, value as value. */
    private $addendum = ''; /* Closing statements such as ORDER BY, GROUP BY, LIMIT.
     * The values to be inserted separated by commas when making INSERT statement. */
    private $put_values = array(); /* list of values whose position corresponds w/ the 
     * reciprocal position of the column they are assigned to in the $columns array */
    private $where_builder;
    
    public function __construct()
    {
        
    }
    
    public function __clone()
    {
        $answer = new InputOutputArgvBuilder();
        $answer->columns = \Segment\utilities\Utilities::arrayCopy($this->columns);
        $answer->tables = \Segment\utilities\Utilities::arrayCopy($this->tables);
        $answer->where = $this->where;
        $answer->where_values = \Segment\utilities\Utilities::arrayCopy($this->where_values);
        $answer->addendum = $this->addendum;
        $answer->put_values = \Segment\utilities\Utilities::arrayCopy($this->put_values);
        return $answer;
    }
    
    public function clearColumns()
    {
        $this->columns = array();
    }

    public function setInitialTable($first_table)
    {
        if(is_string($first_table)){
            $this->tables = array(
                0 => $first_table
                    );
        } else {
            throw new \InvalidArgumentException('setInitialTable function first argument'
                    . ' text string only. Input was: '.$first_table);
        }
        return $this;
    }
    
    public function setInnerJoinTables($table, $column)
    {
        if(is_string($table)&&is_string($column)){
            $this->tables = $this->tables + array($column => $table);
        } else {
            throw new \InvalidArgumentException('setInnerJoinTables function arguments'
                   . ' text strings only. Input was: '. print_r(var_dump(func_get_args()), true));
        }
        $args = func_get_args();
        if(count($args)>2){
            $args = array_slice($args, 2);
            if(count($args)%2===0){
                foreach($args as $key => $value){
                    $this->tables = $this->tables + array($value => $key);
                }
            } else {
                throw new \InvalidArgumentException('setInnerJoinTables function arguments'
                   . ' (table , column) pairs only. Input was: '. print_r(var_dump(func_get_args()), true));
            }
        }
        return $this;
    }
    
    public function setColumns($table, ...$column)
    {
        $columns = func_get_args();
        if($this->ensureArrayValueString($columns)){
            if(count($columns)===2){
                if(isset($this->columns[$table]))
                    $this->columns[$table][] = $column;
                else
                    $this->columns[$table] = array($column);
                return $this;
            }
            $columns = array_slice($columns, 1);
            $this->columns = $this->columns + array(
                $table => $columns
            );
        }
        return $this;
    }
    
    /**
     * 
     * @param string $where_clause
     * @return InputOutputArgvBuilder
     * @throws \InvalidArgumentException
     */
    public function setWhere($where_clause)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->where = $where_clause;
        return $this;
    }
    
    public function setWhereValues(array $where_values)
    {
        for($i=count($where_values)-1; $i>-1;$i--){
            if(!is_string($where_values[$i]))
                throw new \InvalidArgumentException(
                    __CLASS__ . '::' . __METHOD__ . ' requires first argument to be array of strings type.'
                    . ' Provided: '. print_r(var_dump($where_values),TRUE));
        }
        $this->where_values = $where_values;
        return $this;
    }
    
    public function setAddendum($addendum, $row_current, $row_increment)
    {
        if(!is_int($row_current)||!$row_current>-1)
            $row_current = 0;
        if(!is_int($row_increment)||!$row_increment>0||!$row_increment<=__ROW_MAXIMUM__)
            $row_increment = __ROW_INCREMENT__;
        if(!is_string($addendum))
            throw new \InvalidArgumentException(
                    'setAddendum function argument must be of type text string.'
                    . ' Argument: '. print_r($addendum,true));
        if(strpos($addendum, 'LIMIT')===FALSE){
            $addendum .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        }
        $this->addendum = $addendum;
        return $this;
    }
    
    /**
     * Takes values to replace incumbent values for corresponding columns. Any to use default 
     * DB settings should be represented with NULL
     * @param (string|integer|float|boolean|NULL) $name variaible number
     * @return InputOutputArgvBuilder
     */
    public function setPutValues(...$put_values)
    {
        $args = func_get_args();
        $this->put_values = $args;
        
        return $this;
    }
    
    public function getTable()
    {
        return $this->tables;
    }
    
    public function getColumns()
    {
        return $this->columns;
    }
    
    public function getWhereStatement()
    {
        return $this->where;
    }
    
    public function getWhereValues()
    {
        return $this->where_values;
    }
    
    public function getAddendum()
    {
        return $this->addendum;
    }
    
    public function getPutValues()
    {
        return $this->put_values;
    }
    
    private function ensureArrayValueString(array $arr)
    {
        $answer = TRUE;
        foreach($arr as $entry){
            $answer = is_string($entry);
            if(!$answer)
                break;
        }
        return $answer;
    }

    public function addIntersection(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp) {   }

    public function addReturnColumn(\Segment\Model\Column $clmn) {}

    public function addReturnMaxColumn(\Segment\Model\Column $clmn) {}

    public function addReturnMeanColumn(\Segment\Model\Column $clmn) {}

    public function addReturnMedianColumn(\Segment\Model\Column $clmn) {}

    public function addReturnMinColumn(\Segment\Model\Column $clmn) {
        
    }

    public function addReturnModeColumn(\Segment\Model\Column $clmn) {
        
    }

    public function addReturnQuantityRows($absolute, $amnt, $start = 0) {
        
    }

    public function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE) {
        
    }

    public function addReturnUniqueColumn(\Segment\Model\Column $clmn) {
        
    }

    public function addSearchEqual(
            \Segment\Model\Column $clmn, SingleValue $value = NULL, \Segment\Model\Statement $var_comp = NULL
            )
    {
        
    }

    public function addSearchEqualAny(
            \Segment\Model\Column $clmn, AnyAllValues $values = NULL, \Segment\Model\Statement $var_comp = NULL
            )
    {
        
    }

    public function addSearchGreater(
            $exclusive = TRUE, SingleValue $value = NULL, \Segment\Model\Statement $var_comp = NULL
            )
    {
        
    }

    public function addSearchGreaterAll($exclusive = TRUE, AnyAllValues $values = NULL, \Segment\Model\Statement $var_comp = NULL) {
        
    }

    public function addSearchGreaterAny($exclusive = TRUE, AnyAllValues $value = NULL, \Segment\Model\Statement $var_comp = NULL) {
        
    }

    public function addSearchLesser($exclusive = TRUE, SingleValue $value = NULL, \Segment\Model\Statement $var_comp = NULL) {
        
    }

    public function addSearchLesserAll($exclusive = TRUE, AnyAllValues $values = NULL, \Segment\Model\Statement $var_comp = NULL) {
        
    }

    public function addSearchLesserAny($exclusive = TRUE, AnyAllValues $values = NULL, \Segment\Model\Statement $var_comp = NULL) {
        
    }

    public function addSearchNotRange($exclusive = TRUE, BetweenValues $values = NULL, \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL) {
        
    }

    public function addSearchRange($exclusive = TRUE, BetweenValues $values = NULL, \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL) {
        
    }

    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp) {
        
    }

    public function setQueryType($type) {
        
    }

}