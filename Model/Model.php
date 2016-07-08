<?php
namespace Segment\Model\production;

class AssocQHFetchArgBuilder implements \Segment\Model\QHFetchArgBuilder
{
    public function __construct()
    {}

    /**
     * Fetches all returned DB rows with column names as keys formatted as array of Rest
     * @param \Segment\Model\Segment\Model\Statement $stmt
     * @param string $call_id
     * @return array<\Segment\utilities\Rest>
     */
    public function fetch(\Segment\Model\Statement $stmt, $call_id)
    {
        $answer = array();
        while($entry = $stmt->fetch(\PDO::FETCH_NAMED)){
            $row = new \Segment\utilities\Rest($call_id);
            foreach($entry as $key => $value){
                $row->addend($key, [$value]);
            }
            $answer[] = $row;
        }
        return $answer;
    }
}

class Values
{
    private $values = array();
    private $count = 0;
    
    public function getValues()
    {
        return $this->values;
    }
    
    public function addend(array $end_values)
    {
        if(\Segment\utilities\Utilities::isArrayScalar($end_values)&&
                !\Segment\utilities\Utilities::checkArrayEmpty($end_values)){
            $this->values = array_merge ($this->values, $end_values);
            $this->count += count($end_values);
        }
    }
    
    public function insert(array $mid_values, $index)
    {
        if(\Segment\utilities\Utilities::isArrayScalar($end_values)&&
                !\Segment\utilities\Utilities::checkArrayEmpty($end_values)){
            $end_values = array_slice($this->values, $index);
            $this->values = array_merge($this->values, $mid_values, $end_values);
            $this->count += count($end_values);
        }
    }
    
    public function getCount()
    {
        return $this->count;
    }
    
    public function getIndex($value)
    {
        return array_search($value, $this->values);
    }
}

class SingleValue extends \Segment\Model\SearchValues
{
    private $values = array();
    private $count = 0;
    public function __construct($value)
    {
        $this->values[] = $value;
        $this->count++;
    }
}

class AnyAllValues extends \Segment\Model\SearchValues
{
    private $values;
    private $count = 0;
    
    /**
     * @param SingleValue $values Variable-length variable.
     */
    public function __construct(SingleValue ...$values)
    {
        $this->values = $values;
        $this->count = isarray($values) ? count($values) : 1;
    }
}

class BetweenValues extends \Segment\Model\SearchValues
{
    private $values = array();
    private $count = 0;
    public function __construct($first, $second)
    {
        $lower_bound = $first<$second ? $first : $second;
        $upper_bound = $lower_bound===$first ? $second : $first;
        $this->values = [
            $lower_bound,
            $upper_bound
        ];
        $this->count = 2;
    }
}

class IntegerValue extends SingleValue
{
    /**
     * 
     * @param integer $value
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        parent::__construct($value);
    }
}

class FloatValue extends IntegerValue
{
    /**
     * 
     * @param float $value
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        parent::__construct($value);
    }
}

class StringValue extends SingleValue
{
    /**
     * 
     * @param string $value
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        parent::__construct($value);
    }
}

class SubtractionExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $minuend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $subtrahend Either IntegerValue, FloatValue, or Variable.
     *  Only the first value will be used.
     * @param boolean $paranthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($minuend, $subtrahend, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($minuend, 'IntegerValue')||is_a($minuend,'Variable'))&&
                (is_a($subtrahend,'IntegerValue')||is_a($subtrahend, 'Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $minuend->getValues()[0];
            $var2 = $subtrahend->getValues()[0];
            $this->values[] = "{$lp}{$var1}-{$var2}{$rp}";
            $this->count++;
        }
    }
}

class AdditionExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $augend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $addend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param boolean $parenthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($augend, $addend, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($augend, '\Segment\Model\production\IntegerValue')||is_a($augend,'\Segment\Model\production\Variable'))&&
                (is_a($addend,'\Segment\Model\production\IntegerValue')||is_a($addend, '\Segment\Model\production\Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $augend->getValues()[0];
            $var2 = $addend->getValues()[0];
            $this->values[] = "{$lp}{$var1}+{$var2}{$rp}";
            $this->count++;
        }
    }
}

class DivisionExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $dividend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $divisor Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param boolean $parenthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($dividend, $divisor, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($dividend, '\Segment\Model\production\IntegerValue')||
is_a($dividend,'\Segment\Model\production\Variable'))&&
                (is_a($divisor,'\Segment\Model\production\IntegerValue')||
is_a($divisor, '\Segment\Model\production\Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $dividend->getValues()[0];
            $var2 = $divisor->getValues()[0];
            $this->values[] = "{$lp}{$var1}/{$var2}{$rp}";
            $this->count++;
        }
    }
}

class WildcardExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
   
    /**
     * Boolean equality expression
     * @param mixed $field
     * @param mixed $value1
     * @param mixed $value2
     * @param integer $options
     */
    public function __construct($field, $value)
    {
        $this->values[] = $field;
        $this->count++;
    }
}

class MultiplicationExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $multiplier Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $multiplicand Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param boolean $paranthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($multiplier, $multiplicand, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($multiplier, '\Segment\Model\production\IntegerValue')||
is_a($multiplier,'\Segment\Model\production\Variable'))&&
                (is_a($multiplicand,'\Segment\Model\production\IntegerValue')||
is_a($multiplicand, '\Segment\Model\production\Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $multiplier->getValues()[0];
            $var2 = $multiplicand->getValues()[0];
            $this->values[] = "{$lp}{$var1}*{$var2}{$rp}";
            $this->count++;
        }
    }
}


class TableLink
{
    private $table1;
    private $table2;
    private $link;
    
    public function __construct(\Segment\Model\Table $table1, \Segment\Model\Table $table2, \Segment\Model\Column $link)
    {
        $this->table1 = $table1;
        $this->table2 = $table2;
        $this->link = $link;
    }
}


class StatementBuilder extends \Segment\Model\InputOutput implements \Segment\Model\SelectClauseBuilder,
        \Segment\Model\SearchClauseBuilder, \Segment\Model\SetClauseBuilder
{
    /**
     * The $db_type variables are used to determine class name of builders and wrappers.
     * The first is most specific, the larger numbers are less specific, so if the first
     * one doesn't yield a legitimate class name then the second should be used, and so on.
     */
    
    private $query_type;

    const QUERY_TYPE_GET = 2;
    const QUERY_TYPE_POST = 4;
    const QUERY_TYPE_PUT = 8;
    const QUERY_TYPE_DELETE = 16;
    
    /**
     * Set either GET, POST, PUT, or DELETE
     * @param integer $type
     */
    public function __construct($type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if($type===self::QUERY_TYPE_GET||$type===self::QUERY_TYPE_POST||$type===self::QUERY_TYPE_PUT
                ||$type===self::QUERY_TYPE_DELETE){
            $this->db_type_1 = __RDB_SYSTEM__;
            $this->db_type_2 = __RDB_SYSTEM2__;
            $this->query_type = $type;
            $this->return_builder = new __STATEMENT_BUILDER__();
            if(!$this->query_type===self::QUERY_TYPE_POST){
                $this->where_builder = new __WHERE_BUILDER__();
                $tname = $this->getTypeName();
                $from_class_name = '__' . $tname . '_FROM_CLAUSE_BUILDER__';
                $this->from_builder = new $from_class_name();
            }
            if($type===self::QUERY_TYPE_POST)
                $this->set_builder =  new __INSERT_SET_BUILDER__();
            else if($type===self::QUERY_TYPE_PUT)
                $this->set_builder = new __UPDATE_SET_BUILDER__();
            else if($type===self::QUERY_TYPE_DELETE)
                $this->delete_builder = new __DELETE_BUILDER__();
        } else
            throw new \LogicException(__NAMESPACE__.'\''.__CLASS__.'::'.__METHOD__.
	            ' first argument, $type, must be a constant of ' . __CLASS__
		    . '. Provided: ' . print_r($type, TRUE));
    }
    
    public function getTypeName()
    {
        $answer = FALSE;
        switch ($this->query_type)
        {
            case self::QUERY_TYPE_DELETE:
                $answer = 'DELETE';
                break;
            case self::QUERY_TYPE_GET:
                $answer = 'GET';
                break;
            case self::QUERY_TYPE_POST:
                $answer = 'POST';
                break;
            case self::QUERY_TYPE_PUT:
                $answer = 'PUT';
                break;
            
        }
        return $answer;
    }
    
    public function addIntersection(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $this->setDb($first_var->getInputOutput()->getDbs());
        $this->setTable($first_var->getInputOutput()->getDbs());
        foreach($var_comp as $k => $statement){
            $this->setTable($statement->getInputOutput()->getDbs());
            $this->setTable($statement->getInputOutput()->getTables());
        }
        $this->return_builder->setCallQueue('addIntersection',...func_get_args());
    }

    public function addReturnColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnColumn',$clmn);
    }

    public function addReturnMaxColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnMaxColumn',$clmn);
    }

    public function addReturnMeanColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnMeanColumn',$clmn);
    }

    public function addReturnMedianColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnMedianColumn',$clmn);
    }

    public function addReturnMinColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnMinColumn',$clmn);
    }

    public function addReturnModeColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnModeColumn',$clmn);
    }

    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnQuantityRows',$absolute, $amnt, $start);
    }

    public function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnSortColumn',$clmn, $asc);
    }

    public function addReturnUniqueColumn(\Segment\Model\Column $clmn)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnUniqueColumn',$clmn);
    }

    public function addSearchEqual(
            \Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue('addSearchEqual',$clmn, $value, $var_comp, $required_param);
    }
    
    public function addSearchNotEqual(
            \Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue('addSearchNotEqual',$clmn, $value, $var_comp, $required_param);
    }

    public function addSearchEqualAny(
            \Segment\Model\Column $clmn, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue('addSearchEqualAny',$clmn, $values, $var_comp, $required_param);
    }

    public function addSearchGreater(
            \Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchGreater',$clmn, $exclusive, $value, $var_comp, $required_param);
    }

    public function addSearchGreaterAll(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchGreaterAll',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchGreaterAny(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchGreaterAny',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchLesser(
            \Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchLesser',$clmn, $exclusive, $value, $var_comp, $required_param);
    }

    public function addSearchLesserAll(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchLesserAll',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchLesserAny(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchLesserAny',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchNotRange(
            \Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL,
            $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp1))
            $this->setDb ($var_comp1->getInputOutput ()->getDbs());
        if(!empty($var_comp2))
            $this->setDb ($var_comp2->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchNotRange',$clmn, $exclusive, $values, $var_comp1, $var_comp2);
    }

    public function addSearchRange(\Segment\Model\Column $clmn, $exclusive = TRUE,
            BetweenValues $values = NULL, \Segment\Model\Statement $var_comp1 = NULL,
            \Segment\Model\Statement $var_comp2 = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp1))
            $this->setDb ($var_comp1->getInputOutput ()->getDbs());
        if(!empty($var_comp2))
            $this->setDb ($var_comp2->getInputOutput ()->getDbs());
        $this->setTable($clmn->getTable());
        $this->where_builder->setCallQueue(
                'addSearchRange',$clmn, $exclusive, $values, $var_comp1, $var_comp2);
    }

    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $this->setDb($first_var->getInputOutput()->getDbs());
        $this->setTable($first_var->getInputOutput()->getDbs());
        foreach($var_comp as $k => $statement){
            $this->setTable($statement->getInputOutput()->getDbs());
            $this->setTable($statement->getInputOutput()->getTables());
        }
        $this->return_builder->setCallQueue('addUnion', ...func_get_args());
    }
    
    protected function setDb($db)
    {
        if(!is_integer(array_search($db, $this->db_list)))
            $this->db_list[] = $db;
    }
    
    
    public function get($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __NAMESPACE__);
        parent::get($statement_class_name);
    }
    
    public function post($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __NAMESPACE__);
        parent::post($statement_class_name);
    }
    
    public function put($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __NAMESPACE__);
        parent::put($statement_class_name);
    }

    public function delete($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __NAMESPACE__);
        parent::delete($statement_class_name);
    }

    public function getStatement()
    {
        $stmt_n = $this->getClassName('Statement');
        return new $stmt_n($this);
    }

    public function addReturnCountColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addReturnCountColumn',$clmn);
    }

    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $this->setDb($target->getDb());
        $this->setTable($target->getTable());
        $this->set_builder->addSet($target, $value);
    }

    public function getSetClause()
    {
        return $this->set_builder->getClause();
    }
    
    
    public function setTable($table)
    {
        if(is_a($table, '\Segment\Model\Column')||is_a($table, '\Segment\Model\Table')||
                is_a($table, '\Segment\Model\production\Expression')){
            $this->tables[] = print_r($table, TRUE);
        }
        $this->tables = array_unique($this->tables);
    }

    public function addReturnExpression(\Segment\Model\Expression $exp, $parenthesis = false, StringValue $alias = NULL)
    {
        $this->return_builder->setCallQueue('addReturnExpression', $exp, $parenthesis, $alias);
    }

    public function addSearchWildcard(\Segment\Model\Column $clmn, SingleValue $value, $required_param = FALSE)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn->getTable());
        $this->return_builder->setCallQueue('addSearchWildcard',...func_get_args());
    }

    public function getAddendum()
    {

    }

    public function getAddendumClause()
    {
        return $this->return_builder->getAddendum();
    }

    /**
     * @deprecated since version 1
     */
    public function getColumns(){
        
    }

    public function getDeleteClause()
    {
        return $this->delete_builder->getClause();
    }

    public function getFromClause()
    {
        return $this->return_builder->getClause();
    }

    public function getPutValues()
    {
        return $this->put_values;
    }

    /**
     * @deprecated since version 1
     */
    public function getWhere(){
        
    }

    public function getWhereClause()
    {
        return $this->where_builder->getClause();
    }

    public function getWhereValues()
    {
        return $this->where_values;
    }

    public function getClause()
    {}
}

class GetAdmin
{    // /admin/admin.php calls this class to process fruit of cURL call from view
    private $variable = 'V|||A|||R';
    private $select;
    private $primary;
    private $admin;
    private $read_only;
    private $has_original;
    private $input_output;
    private $statement_name;
        
    public function __construct(\Segment\Model\InputOutput $admin_sql, array $get,
            $statement_name = 'SQLStatement'
            )
    {
        if(!is_string($statement_name)){
            trigger_error('construct expected Argument 3 to be String', E_USER_WARNING);
        }
        $this->input_output = $admin_sql;
        $this->setAdminVariables($get);
        $this->statement_name = $statement_name;
    }
    
    public function purgeDescriptionColumns(array $descriptions)
    {
        foreach($descriptions as $key => $description){
            for($i = 0, $max = count($description); $i<$max; $i++){
                if($this->columnExists($description[$i]['Field']));
                
                else 
                    unset($descriptions[$key][$i]);
            }
        }
        return $descriptions;
    }
    
    private function columnExists($field_name)
    {
        if(!is_string($field_name)){
            trigger_error('columnExists expected Argument 1 to be String', E_USER_WARNING);
        }
        $answer = strtolower($field_name)=='file_path'||strtolower($field_name)=='thumb_path'||
                        strtolower($field_name)=='thumb_name' ? false : true;
        return $answer;
    }
    
    private function initialize(\Segment\Model\InputOutput $admin_sql, array $get)
    {
        $this->input_output = $admin_sql;
        $this->setAdminVariables($get);
    }
    
    private function setAdminVariables(array $get)
    {
        if (empty($this->admin))
            $this->admin = !empty($get)&&isset($get['admin']) ? $get['admin'] : '';
        $this->read_only = $this->admin=='delete' ? true : false;
        $this->has_original = $this->admin=='delete'||$this->admin == 'update' ? true : false;
    }
        
    public function getSelectMenuValues(array $column)
    {
        $cparts = array();
        $tables = $this->input_output->getTables();
        end($tables);
        $last_table = count($tables)>1 ? each($tables) : '';
        $select_multiple = $last_table[1]===key($column)
                ? true : false;
        $select = array('select_multiple' => $select_multiple);
        if($select_multiple){
            $temp_input_output = clone $this->input_output;
            $temp_input_output->initialize(array(
                'columns' => array(
                key($column) => array( $column[key($column)][0] )
                ),'tables' => array(
                    $last_table[1]
                ),'where' => '',
                'where_values' => array(),
                'addendum' => ' GROUP BY ' . key($column) . '.' . $column[key($column)][0]
                    ));
            $new_array = array();
            $query_output_array = $temp_input_output->get($this->statement_name);
            for($i = 0, $max = count($query_output_array); $i<$max; $i++){
                $new_array[$i] = $query_output_array[$i][key($query_output_array[$i])];
            }
             $select['values'] = $new_array;
        }
        return $select;
    }
        
    public function getMetaData(array $descriptions)
    {
        $index = 0;
        foreach($descriptions as $key => $description){
            foreach($description as $entry){
                $temp = explode('(', $entry['Type']);
                $type = $temp[0];
                $temp = explode(')', $temp[1]);
                $select_column = is_array($entry['Field']) ? $entry['Field'][0] : $entry['Field'];
                $data[] = array(
                    'field_name' => $entry['Field'],
                    'field_data' => $this->getDataType($type, $temp[0], $temp[1], $entry['Key']),
                    'select' => $this->getSelectMenuValues(array(
                        $key => array($select_column)
                    ))
                );
                unset($description[$index++]);
                unset($type);
                for($i = 0, $max = count($temp); $i < $max; ++$i){
                    unset($temp[$i]);
                }
            }
        }
        return $data;
    }

    private function getDataType($type = '', $size = 0, $attribute='', $key = '')
    {
        $data = array();
        if(strpos($attribute, 'unsigned')!==false)
            $data['unsigned'] = true;
        switch($type){
            case 'text': $data['size'] = 65535;
            case 'blob':
            case 'tinyttext': $data['size'] = !isset($data['size']) ? 255 : $data['size'];
            case 'mediumblob':
            case 'mediumtext': $data['size'] = !isset($data['size']) ? 16777215 : $data['size'];
            case 'longblob': 
            case 'longtext': $data['size'] = !isset($data['size']) ? 4294967295 : $data['size'];
            case 'char': 
            case 'varchar': $data['type'] = $type=='char'&&$size==255&&empty($key) ? 'image' : 'text';
                $data['size'] = !isset($data['size']) ? $size : $data['size'];
                break;
                        // need to check that the 'size' hasn't already been occupied, create function for 
                        // checking $size and zerofill and compare to default for $type and sign state
            case 'bit' : $data['type'] = $size==1 ? 'boolean' : 'number';
                $data['size'] = $size;
                break;
            case 'smallint': $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
            case 'tinyint': $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
            case 'int': $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
            case 'bigint': $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
            case 'int' : $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
            case 'mediumint' : $data['size'] = !isset($data['size']) ? $this->getNumber($type, $size, $attribute) : 
                $data['size'];
                $data['type'] = 'number';
                $data['primary'] = $key == 'PRI' ? true : false;
                break;
            case 'float' : $temp = explode(', ', $size);
                $answer['type'] = 'decimal';
                $answer['size'] = array(
                    $this->getNumber('int', $temp[0], $attribute), 
                    $this->getNumber('int', $temp[1], $attribute)
                );
                break;
            case 'double' : $temp = explode(', ', $size);
                $answer['type'] = 'decimal';
                $answer['size'] = array(
                    $this->getNumber('bigint', $temp[0], $attribute), 
                    $this->getNumber('bigint', $temp[1], $attribute)
                );
                break;
            case 'decimal' : $temp = explode(', ', $size);
                $answer['type'] = 'decimal';
                $answer['size'] = array(
                    $this->getNumber('bigint', $temp[0], $attribute), 
                    $this->getNumber('bigint', $temp[1], $attribute)
                );
                break;
            case 'date' : $data['type'] = 'calendar';
                $data['size'] = 'YYYY-MM-DD';
                break;
            case 'time' : $data['type'] = 'calendar';
                $data['size'] = 'THH-mm-ss';
                break;
            case 'year' : $data['type'] = 'calendar';
                $data['size'] = $size==4 ? 'YYYY' : 'YY';
                break;
            case 'datetime' :
            case 'timestamp' : $data['type'] = 'calendar';
                $data['size'] = ['YYYY-MM-DD',
                    'THH-mm-ss'
                    ];
                break;
                        
        }
        return $data;
    }
        
    private function getNumber($type, $size, $attribute)
    {
        $s = !empty($size)&&$size>0 ? true : false;
        if($s)
            return $size;
        switch($type){
            case 'smallint': $answer = strpos('unsigned', $attribute)>-1 ? 5 : 6;
                return $answer;
            case 'tinyint': $answer = strpos('unsigned', $attribute)>-1 ? 3 : 4;
                return $answer;
            case 'int': $answer = strpos('unsigned', $attribute)>-1 ? 10 : 11;
                return $answer;
            case 'bigint': $answer = strpos('unsigned', $attribute)>-1 ? 20 : 20;
                return $answer;
            case 'mediumint' : $answer = strpos('unsigned', $attribute)>-1 ? 8 : 8;
                return $answer;
        }
    }

}

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

class FieldSetArgvBuilder extends InputOutputArgvBuilder
{
    public function setAddendum($addendum, $row_current, $row_increment)
    {
        if(!is_int($row_current)||!$row_current>-1)
            $row_current = FALSE;
        if(!is_int($row_increment)||!$row_increment>0||!$row_increment<=__ROW_MAXIMUM__)
            $row_increment = __ROW_INCREMENT__;
        if(!(is_string($addendum)&&strlen($addendum)>1))
            throw new \InvalidArgumentException(
                    'setAddendum function argument must be of type text string.'
                    . ' Argument: '. print_r($addendum,true));
        if(strpos($addendum, 'LIMIT')===FALSE){
            if(!$row_current)
                $addendum = $addendum;
            else if($row_current)
                $addendum .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        }
        $this->addendum = $addendum;
        return $this;
    }
}

class DbTableConsolidatedRows
{
    private $rows = array();
    public function consolidate(array $table, $limit = 0)
    {
            $incumbent_key;
            $cluster = array();
            $position = 0;
            for($max = count($table); $max>-1; ){
                --$max;
                if(!isset($incumbent_key)||$incumbent_key===$table[$max][key($table[$max])]){
                    $cluster[] = $table[$max];
                    $incumbent_key = $table[$max][key($table[$max])];
                } else {
                    $incumbent_key = $table[$max][key($table[$max])];
                    if(count($cluster)===1)
                        $this->rows[$position++] = $cluster;
                    else if(count($cluster)>1){
                        $this->processCluster($cluster, $position++);
                        $cluster = [
                            $table[$max]
                        ];
                    }
                }
            }
            return \Segment\utilities\Utilities::arrayCopy($this->rows);
    }
    
    private function processCluster(array $cluster, $position)
    {
        $keys = array_keys($cluster[0]);
        $row = array();
        foreach($keys as $val){
            $row[$val] = array();
        }
        $first_time_through = TRUE;
        for($i=count($cluster) - 1;$i>-1;$i--){
            if($first_time_through){
                $cluster[$i] = array_reverse($cluster[$i]);
                $first_time_through = FALSE;
            }
            for($clmn = count($cluster[0])-1; $clmn>-1; --$clmn){
                list($column, $cell) = each($cluster[$i]);
                $row[$column][] = $cell;
            }
        }
        foreach ($row as $key => $value) {
            $value = array_unique($value);
            if(count($value)===1)
                $row[$key] = $value[0];
            else
                $row[$key] = $value;
        }
        $this->rows[$position] = $row;
    }
}

function normalizeAdminTableResults(array $table_results)
{
    $answer = array();
    $i = count($table_results);
    while($i--){
        $answer = $answer + [
            $table_results[$i]['field_name'] => [
                'field_name' => explode('.', $table_results[$i]['field_name'])[1],
                'field_data' => [
                    'type' => $table_results[$i]['data_type'],
                    'size' => $table_results[$i]['data_size'],
                    'unsigned_number' => $table_results[$i]['data_unsigned'],
                    'primary' => $table_results[$i]['data_primary']
                ],
                'select' => $table_results[$i]['data_select']
            ]
        ];
    }
    return array_reverse($answer);
}


class ModelCaller implements \Segment\Model\SelectClauseBuilder, \Segment\Model\SearchClauseBuilder,
        \Segment\Model\SetClauseBuilder
{
    private $input_output;
    protected $call_id;
    const EQUAL = 'equal';
    const NEQUAL = 'nequal';
    const EQUALANY = 'equalany';
    const GREATER = 'greater';
    const GREATEQ = 'greateq';
    const GREATANY = 'greatany';
    const GREATALL = 'greatall';
    const LESSER = 'lesser';
    const LESSEQ = 'lesseq';
    const LESSANY = 'lessany';
    const LESSALL = 'lessall';
    const BETWEEN = 'between';
    const NBETWEEN = 'nbetween';
    
    /**
     * 
     * @param string $call_id
     */
    public function __construct($call_id)
    {
        $this->input_output = new StatementBuilder($parsed_rest->getValue($parsed_rest->key()));
        $this->call_id = $call_id;
    }
    
    
    /**
     * Uses the parsed REST data and calls the model returning a Record object.
     * @return \Segment\utilities\Record
     */
    public function execute()
    {
        $answer = array();
        $rows = $this->query($this->input_output->getStatement(), $this->call_id);
        last($rows);
        for($i=key($rows);$i>-1;$i--){
            $row = $rows[$i];
            $answer[] = $this->makeRecord($row);
        }
        return $answer;
    }
    
    /**
     * @param \Segment\Model\InputOutput $call
     * @return \Segment\Model\Statement object of interface Statement
     */
    public function initializeDbStatement(\Segment\Model\InputOutput $call)
    {
        $answer = $call->getStatement();
        return $answer;
    }
    
    /**
     * @param \Segment\Model\Statement $statement
     * @return array data base output
     */
    public function query(\Segment\Model\Statement $statement)
    {
        $handler = $this->getDBQuery(__RDB_SYSTEM__);
        $answer = $handler->query($statement, $this->controller->getId());
        return $answer;
    }
    
    /**
     * @param array $db_out takes one associative array representing a DB row
     * @return \Segment\utilities\Record returns a Record object formally representing a DB row
     */
    public function makeRecord(array $db_out)
    {
        $answer = new \Segment\utilities\Record();
        foreach($db_out as $key => $value){
            $answer->addend($key, [
                $value
            ]);
        }
        return $answer;
    }
    
    public function getStatement()
    {
        return $this->build_args->getStatement();
    }
    
    /**
     * Fetches a \Segment\Model\QueryHandler object.
     * @param string $db_type
     * @return \Segment\Model\QueryHandler
     * @throws \InvalidArgumentException
     */
    protected function getDBQuery($db_type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        switch ($db_type){
                    case 'MySQL':
                        return MySQLQH::getQueryHandler();
                    case 'MSSQL':
                        return MSSQLQH::getQueryHandler();
                    case 'DB2':
                        return DB2QH::getQueryHandler();
                    case 'Oracle':
                        return OracleQH::getQueryHandler();
                    case 'PostGRE':
                        return PostGREQH::getQueryHandler();
                    case 'SQLite':
                        return SQLiteQH::getQueryHandler();
                    default :
                        return NULL;
                }
    }
    
    /**
     * Determines if value is a REST search function
     * @param string $value The name to be tested
     * @return boolean TRUE if search trigger name, FALSE if not.
     */
    protected function isRestSearch($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $reflect  = new \ReflectionClass(__CLASS__);
        $constants = $reflect->getConstants();
        $answer = array_search($value, $constants);
        return $answer;
    }
    
    /**
     * Determine if string provided is the name of an extension of ModelCaller
     * @param string $name
     * @return boolean TRUE, it is a ModelCaller class name, FALSE it is not.
     */
    protected function isModelCallerClassName($name)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if(is_a($name, 'ModelCaller', TRUE)){
            return TRUE;
        }
        return FALSE;
    }
    
    
    /**
     * Provides string name of function to add the $value search to StatementBuilder
     * @param string $value
     * @return string
     */
    protected function getRestSearch($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $answer;
        switch(strtolower(trim($value))){
            case 'equal':
                $answer = 'addSearchEqual';
                break;
            case 'nequal':
                $answer = 'addSearchNotEqual';
                break;
            case 'equalany':
                $answer = 'addSearchEqualAny';
                break;
            case 'greater':
            case 'greateq':
                $answer = 'addSearchGreater';
                break;
            case 'greatany':
                $answer = 'addSearchGreaterAny';
                break;
            case 'greatall':
                $answer = 'addSearchGreaterAll';
                break;
            case 'lesser':
            case 'lesseq':
                $answer = 'addSearchLesser';
                break;
            case 'lessany':
                $answer = 'addSearchLesserAny';
                break;
            case 'lessall':
                $answer = 'addSearchLesserAll';
                break;
            case 'between':
                $answer = 'addSearchBetween';
                break;
            case 'nbetween':
                $answer = 'addSearchNotBetween';
                break;
            default:break;
        }
        return $answer;
    }
    
    /**
     * Returns value, or values, provided compatible for the specified Column
     * @param \Segment\Model\Column $field
     * @param mixed $value
     * @return mixed
     */
    protected function ensureValue(\Segment\Model\Column $field, $value)
    {
        if(is_scalar($value)){
            $description = $this->controller->getDescription();
            $type = $description->getType($field);
            if(strtolower($type)==='boolean')
                $value = is_null ($value)||!isset($value) ? FALSE : $value;
            if(strtolower($type)==='integer'||  strtolower($type)==='float')
                $value = is_null ($value)||!isset($value) ? 0 : $value;
                    
        } else if(is_array($value)&&isset($value[[0]])){
            $description = $this->controller->getDescription();
            $type = $description->getType($field);
            end($value);
            for($i=key($value);$i>-1;$i--){
                if(strtolower($type)==='boolean')
                    $value[$i] = is_null($value[$i])||!isset($value[$i]) ? FALSE : $value[$i];
                if(strtolower($type)==='integer'||  strtolower($type)==='float')
                    $value[$i] = is_null($value[$i])||!isset($value[$i]) ? 0 : $value[$i];
            }
        }
        return $value;
    }
    
    /**
     * Processes the FIELD, VALUE, AND, OR, REQUESTS, POST, and name of database
     *         column keys and their values in the associative array
     *         derived from the JSON-formatted REST argument.
     * @param array $parsed_rest Associative array from JSON-formatted REST argument
     * @param \Segment\utilities\DbDescription $rubric
     * @param string $unique_id_table Optional. The name of the table the unique_id
     *         field for a PUT or DELETE request should be associated with.
     * @param array $wild_tables Optional. Associative Array in format keys = string name of table,
     *         values = scalar arrays of string names of columns.
     */
    protected function processsParsedRest(array $parsed_rest, \Segment\utilities\DbDescription $rubric,
            $unique_id_table = NULL, $wild_tables = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $clmn_n = $this->build_args->getClassName('Column');
        $tbl_n = $this->build_args->getClassName('Table');
        if(isset($parsed_rest['and'])||isset($parsed_rest['or'])){
            $index = isset($parsed_rest['and']) ? 'and' : 'or';
            $required = $index==='and' ? TRUE : FALSE;
            
            for($i=count($parsed_rest[$index])-1;$i>-1;$i--){
                try{
                    $v = $parsed_rest[$index][$i];
                    if(is_array($v)&&$this->isRestSearch(key($v))){
                        $search = key($v);
                        $args = $v[$search];
                        $this->makeSearchRequest(
                                $args['field'],
                                $rubric->getPrimaryTable($args['field']),
                                $args['value'],
                                $search);
                    }
                } catch (\Exception $ex) {
                    error_log($ex->getMessage());
                }
                
            }
        } else {
            switch ($this->build_args->getTypeName()) {
                case 'POST':
                    $this->processPostRest($parsed_rest, $rubric, $unique_id_table, $wild_tables);
                    break;
                case 'PUT':
                    $this->processPutRest($parsed_rest, $rubric, $unique_id_table, $wild_tables);
                    break;
                case 'GET':
                    $this->processGetRest($parsed_rest, $rubric, $unique_id_table, $wild_tables);
                    break;
                case 'DELETE':
                    $this->processDeleteRest($parsed_rest, $rubric, $unique_id_table, $wild_tables);
                    break;
                default:
                    
                    break;
            }
                
        }
            
    }
    
    /**
     * 
     * @param string $clmn
     * @param string $tbl
     * @param string $operator
     * @param StringValue $alias
     */
    protected function makeReturnRequest($clmn, $tbl, $operator, StringValue $alias = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $clmn_n = $this->build_args->getClassName('Column', __NAMESPACE__);
        $tbl_n = $this->build_args->getClassName('Table', __NAMESPACE__);
        
        switch (trim(strtolower($operator))) {
            case "normal":
                $this->addReturnColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__PROJECT_ACRONYM__, $tbl), $clmn), $alias);
                break;
            case "field_count":
                $this->addReturnCountColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__PROJECT_ACRONYM__, $tbl), $clmn), $alias);
                break;
            case "field_avg":
                $this->addReturnMeanColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__DB_NAME__, $tbl), $clmn), $alias);
                break;
            case "field_set":
                $this->addReturnUniqueColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__DB_NAME__, $tbl), $clmn));
                break;
            case "field_mode":
                $this->addReturnModeColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__DB_NAME__, $tbl), $clmn), $alias);
                break;
            case "field_median":
                $this->addReturnMedianColumn(new $clmn_n(new $tbl_n(Segment\Model\production\__DB_NAME__, $tbl), $clmn), $alias);
                break;
            case "field_firstq":
                break;
            case "field_thirdq":
                break;
            default:
                break;
        }
    }
    
    /**
     * Adds a search parameter to the StatementBuilder, or other InputOutput-extending class instance, located at $this->build_args
     * @param string $target Column name
     * @param string $t_table Table name
     * @param mixed $value Primitive or scalar array. Condition or threshold records must meet in the search
     * @param string $operator ModelCaller const
     */
    protected function makeSearchRequest($target, $t_table, $value, $operator)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $clmn_n = $this->build_args->getClassName('Column');
        $tbl_n = $this->build_args->getClassName('Table');
        $descrip = $this->controller->getDescription();
        $search_func_n = $this->getRestSearch($operator);
        
        if(is_array($value)){
            for($i=count($value)-1;$i>-1;$i--){
                if(is_array($v)&&$this->isModelCallerClassName(key($v))){
                    $caller_name = key($v);
                    $new_caller = new $caller_name($v[$caller_name]);
                    $value[$i] = $new_caller->build_args->getStatement();
                }
                if(!is_scalar($value[$i])&&!is_a($value[$i], 'Statement')){
                    $value[$i] = NULL;
                }
            }
        }
        
        switch(strtolower(trim($operator))){
            case ModelCaller::EQUAL:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchEqual(
                        $clmn,
                        new $value_class_n($temp_value));
                break;
            case ModelCaller::NEQUAL:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchNotEqual(
                        $clmn,
                        $temp_value
                );
                break;
            case ModelCaller::EQUALANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--){
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                }
                $temp_value = new AnyAllValues(...$temp_value);
                $this->build_args->addSearchNotEqual(
                        $clmn,
                        $temp_value);
                break;
            case ModelCaller::GREATER:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchGreater(
                        $clmn,
                        TRUE,
                        $temp_value
                        );
                break;
            case ModelCaller::GREATEQ:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchGreater(
                        $clmn,
                        FALSE,
                        $temp_value
                        );
                break;
            case ModelCaller::GREATANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--){
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                }
                $temp_value = new AnyAllValues(...$temp_value);
                $this->build_args->addSearchGreaterAny(
                        $clmn,
                        $temp_value);
                break;
            case ModelCaller::GREATALL:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--){
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                }
                $this->build_args->addSearchGreaterAll(
                        new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        ),
                        new AnyAllValues(...$temp_value));
                break;
            case ModelCaller::LESSER:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchLesser(
                        $clmn,
                        TRUE,
                        new $value_class_n($temp_value)
                        );
                break;
            case ModelCaller::LESSEQ:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->build_args->addSearchLesser(
                        $clmn,
                        FALSE,
                        new $value_class_n($value)
                        );
                break;
            case ModelCaller::LESSANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value); $i>-1;$i--){
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                }
                $this->build_args->addSearchLesserAny(
                        $clmn,
                        new AnyAllValues(...$temp_value));
                break;
            case ModelCaller::LESSALL:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--){
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                }
                $this->build_args->addSearchLesserAll(
                        $clmn,
                        new AnyAllValues(...$temp_value));
                break;
            case ModelCaller::BETWEEN:
                $temp1 = is_array($value)&&isset($value[0]) ? $value[0] : $value;
                $temp2 = is_array($value)&&isset($value[1]) ? $value[1] : NULL;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp1, $type);
                settype($temp2, $type);
                $temp1 = new $value_class_n($temp1);
                $temp2 = new $value_class_n($temp2);
                $this->build_args->addSearchBetween(
                        $clmn,
                        new BetweenValues($temp1,$temp2));
                break;
            case ModelCaller::NBETWEEN:
                $temp1 = is_array($value)&&isset($value[0]) ? $value[0] : $value;
                $temp2 = is_array($value)&&isset($value[1]) ? $value[1] : NULL;
                $clmn = new $clmn_n(
                                new $tbl_n(
                                        Segment\Model\production\__DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp1, $type);
                settype($temp2, $type);
                $temp1 = new $value_class_n($temp1);
                $temp2 = new $value_class_n($temp2);
                $this->build_args->addSearchNotBetween(
                        $clmn,
                        new BetweenValues($temp1,$temp2));
                break;
            default:
                throw new \InvalidArgumentException(
                        __CLASS__ . '::'. __METHOD__ . " expects fourth argument to"
                        . " be a const of ModelCaller. Provided: " . print_r($operator,TRUE)
                        );
        }
    }
    
    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $this->input_output->addSet($target,$value);
    }
    
    
    public function getSetClause()
    {
        return $this->input_output->getSetClause();
    }
    
    /**
     * Add a column to be returned by query
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. If omitted, NULL
     */
    public function addReturnColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the mean value of which to be returned.
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnMeanColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the unique values found of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnUniqueColumn(\Segment\Model\Column $clmn)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the modal average of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnModeColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the median average of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnMedianColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the maximum value of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnMaxColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the minimum value of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    public function addReturnMinColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the values of which to be sorted
     * @param \Segment\Model\Column $clmn
     * @param boolean $asc Ascending: TRUE. Descending: FALSE.
     */
    public function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add limitation to number of records returned
     * @param boolean $absolute Scalar: TRUE. Percentage: FALSE
     * @param integer $amnt
     * @param integer $start
     */
    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Adds request for count of values in given column.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\StringValue $alias Optional. How column will
     *  be represented, the output alias. If omitted, NULL.
     */
    public function addReturnCountColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add expression to be executed.
     * @param \Segment\Model\Expression $exp
     * @param boolean $parenthesis TRUE expression to be wrapped in parenthesis.
     * @param StringValue $alias
     */
    public function addReturnExpression(\Segment\Model\Expression $exp, $parenthesis = FALSE, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * All of the records from each query together.
     * @param \Segment\Model\Statement $first_var First query of the union
     * @param \Segment\Model\Statement $var_comp One or more additional queries of the union
     */
    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * An intersection of the results of each subquery.
     * @param \Segment\Model\Statement $first_var First query of the intersection
     * @param \Segment\Model\Statement $var_comp One or more additional queries of the intersection
     */
    public function addIntersection(\Segment\Model\Statement $first_var,
            \Segment\Model\Statement ...$var_comp)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require column of records to equal a supplied value. Optionally, the 
     * value may be derived dynamically from another query.
     * @param \Segment\Model\Column $clmn
     * @param SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqual(\Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to equal any supplied value for the respective column.
     * Values for comparison may come in part or whole another query.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\Statement $var_comp
     * @param AnyAllValues $values
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqualAny(\Segment\Model\Column $clmn, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to be within a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive Exclusive: true. Inclusive: false.
     * @param BetweenValues $values
     * @param \Segment\Model\Statement $var_comp1 Least value
     * @param \Segment\Model\Statement $var_comp2 Most value
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchRange(\Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to be outside a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param boolean $exclusive
     * @param BetweenValues $values
     * @param \Segment\Model\Statement $var_comp1
     * @param \Segment\Model\Statement $var_comp2
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchNotRange(\Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL,
            $required_param = TRUE
            )
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than the comparison
     * for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreater(\Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAny(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreaterAll(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than the comparison
     * value for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesser(\Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAny(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAll(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * 
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\SingleValue $value
     * @param type $required_param
     */
    public function addSearchWildcard(\Segment\Model\Column $clmn, SingleValue $value, $required_param = TRUE)
    {
        $method_n = __METHOD__;
        $this->input_output->$method_n(...func_get_args());
    }

    public function getClause() {
        return '';
    }

}
