<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlWhereClauseBuilder extends \Segment\Model\DelayedBuilder implements \Segment\Model\WhereClauseBuilder
{
    private $where_targets = array();
    private $operators = array();
    private $interstitial = array();
    private $value_quantity = array();
    private $sub_clauses = array();
    private $wrapper;
    private $clauses = array();
    
    public function __construct(\Segment\Model\InputOutput $parent)
    {
        $this->wrapper = $parent;
    }
    
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
    
    /**
     * Add a single where clause to builder
     * @param (string|array) $where_targets
     * @param string $operators
     * @param string $value_var
     * @param integer $value_qt
     * @param string $interstitial
     */
    public function addend(
            $where_targets, $operators, $value_var = ' ?', $value_qt = 1, $interstitial = ' AND ')
    {
        $value_var = $value_var ?? ' ?';
        $value_qt = $value_qt ?? 1;
        $interstitial = $interstitial ?? ' AND ';
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->operators[] = $operators;
        $this->where_targets[] = $where_targets;
        $this->value_quantity[] = $value_qt;
        $this->interstitial[] = is_string($interstitial)&&(
                strtolower(trim($interstitial))==='and'||strtolower(trim($interstitial))==='or')
                ? $interstitial : ' AND ';
        $this->convertOperators();
    }
    
    public function __invoke()
    {
        $answer = $this->getClause();
        return $answer;
    }
    
    /**
     * Returns the clause as a string for querying model
     * @return string
     */
    public function getClause(array $tables = [])
    {
        $answer = '';
        
        // Call queued methods to build clauses
        $q = $this->callQueue();
        foreach ($q as $args):
            call_user_func_array([$this, key($args)], current($args));
        endforeach;
        // combine text and Statement objects to make clause
        error_log(__METHOD__ . " after calling the queue, the clauses: " . print_r($this->clauses, TRUE));
        foreach($this->clauses as $interstitial  => $clauses):
            foreach($clauses as $clause):
                $answer = strlen($answer)>0 ? $interstitial . $clause : $clause . '';
            endforeach;
        endforeach;
        error_log(__METHOD__ . " the answer: $answer");
//        for($i = 0, $max = count($this->where_targets); $i<$max; $i++){
//            if(is_array($this->where_targets[$i])){
//                $inner_clause_interstitial = strpos(strtolower(trim($answer)), 'and')===0
//                        ||strpos(strtolower(trim($answer)), 'and')>0 ? ' OR ' : ' AND ';
//                $inner_clause = new MySQLWhereStatementArgBuilder(
//                        $this->where_targets[$i], $this->operators[$i], $inner_clause_interstitial);
//                $answer .= $inner_clause->getWhereStatement();
//            } else if (is_string($this->where_targets[$i])){
//                $answer .= strlen($answer)>0 ?
//                        $this->interstitial . ' ' . $this->where_targets[$i] . ' '
//                        . $this->operators[$i]
//                        : $this->where_targets[$i] . ' ' . $this->operators[$i];
//            }
//            if($this->value_quantity[$i]>1&&strtolower(trim($this->operators[$i]))==='in'){
//                $answer .= ' (';
//                for($j=0;$j<$this->value_quantity[$i];$j++){
//                    
//                }
//            }
//        }
        return $answer;
    }


    private function convertOperators()
    {
        // check for MySQL operators, or equivalent. Convert as needed
        for ($i = 0, $max = count($this->operators); $i<$max; $i++){
            switch (strtolower(trim($this->operators[$i]))){
                case 'greater than': $this->operators[$i] = '>';
                case '>': break;
                case 'less than': $this->operators[$i] = '<';
                case '<': break;
                case 'equal': $this->operators[$i] = '=';
                case '=': break;
                case 'not':
                case 'not equal':
                case '!=': $this->operators[$i] = '<>';
                case '<>':break;
                case 'greater than or equal': $this->operators[$i] = '>=';
                case '>=': break;
                case 'less than or equal': $this->operators[$i] = '<=';
                case '<=': break;
                case 'fuzzy search': $this->operators[$i] = ' LIKE ';
                case 'like': break;
                case 'between': break;
                case 'in':
                    break;
                default : throw new InvalidArgumentException('Invalid SQL WHERE operator provided as argument ' . $i);
            }
        }
    }
    
    private function addClause($statement, $interstitial)
    {
        $key = $interstitial ? ' AND ' : ' OR ';
        if(!is_array($this->clauses[$key])){
            $this->clauses[$key] = [];
        }
        $this->clauses[$key][] = $statement;
    }

    
    /**
     * Query will require column of records to equal a supplied value. Optionally, the 
     * value may be derived dynamically from another query.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    public function addSearchEqual(\Segment\Model\Column $clmn, \Segment\Model\production\SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $field;
        try {
            $equal_n = $this->wrapper->getClassName('EqualityExpression', __MODEL_PRODUCTION_NS__);
            error_log(__METHOD__ . " the EqualityExpression class name: $equal_n. Does class exist: " . \print_r(\class_exists($equal_n), TRUE)
                    . "\nThe arguments: " . print_r(func_get_args(), TRUE));
            $scalar = is_null($value) ? [NULL] : $value->getValues();
            if(!is_null($var_comp)&&is_null($scalar[0])){
                $field = new \Segment\Model\production\StringValue($var_comp->getStatement());
                $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
            } else {
                $field = new \Segment\Model\production\StringValue(' ?');
                $this->wrapper->setValuesEnd($value);
            }

            $statement = new $equal_n($clmn, $field);
            $this->addClause($statement, $required_param);
        } catch(\Exception $exc){
            error_log(__METHOD__ . " " . $exc->getTraceAsString());
        }
    }
    
    /**
     * Query will require column of records to equal a supplied value. Optionally, the 
     * value may be derived dynamically from another query.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Tells whether must be met for all records returned
     */
    public function addSearchNotEqual(\Segment\Model\Column $clmn, \Segment\Model\production\SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $field;
        $equal_n = $this->wrapper->getClassName('EqualityExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        
        $scalar = is_null($value) ? [NULL] : $value->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            $field = '?';
            $this->wrapper->setValuesEnd($value);
        }
        
        $statement = new $equal_n($target, $field);
        $this->addClause($statement, $required_param);
    }
    
    /**
     * Query will require all records to equal any supplied value for the respective column.
     * Values for comparison may come in part or whole another query.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\Statement $var_comp
     * @param \Segment\Model\production\AnyAllValues $values
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchEqualAny(\Segment\Model\Column $clmn, \Segment\Model\production\AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $field = '';
        $target = $clmn->getWhereColumn();
        $in_set_n = $this->wrapper->getClassName('InSetExpressionn', __MODEL_PRODUCTION_NS__);
        $scalars = is_null($values) ? [NULL] : $values->getValues();
        if(!is_null($var_comp)&&is_null($scalars[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            for($i=count($scalars)-1;$i>-1;$i--){
                $field .= strlen($field)>0 ? ', ?' : '?';
                $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalar[$i]));
            }
        }
        
        $statement = new $in_set_n($target, $field);
        $this->addClause($statement, $required_param);
    }
    
    
    /**
     * Query will require all returned records to have values greater than the comparison
     * for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn The target column
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param \Segment\Model\production\SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchGreater(\Segment\Model\Column $clmn, $exclusive = TRUE, \Segment\Model\production\SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $greater_n = $this->wrapper->getClassName('GreaterExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($value) ? [NULL] : $value->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            $field = '?';
            $this->wrapper->setValuesEnd($value);
        }
        
        $this->addClause(new $greater_n($target, $field, $exclusive), $required_param);
    }
    
    /**
     * Query will require all returned records to have values greater than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn The target column
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param \Segment\Model\production\AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    public function addSearchGreaterAny(\Segment\Model\Column $clmn, $exclusive = TRUE, \Segment\Model\production\AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $greater_n = $this->wrapper->getClassName('GreaterExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($values) ? [NULL] : $values->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            for($i=count($scalar)-1; $i>-1;--$i){
                $field .= strlen($field)>2 ? ', ?' : '?';
                $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalar[$i]));
            }
        }
        
        $this->addClause(new $greater_n($target, $field, $exclusive, GreaterExpression::ANY), $required_param);
    }
    
    /**
     * Query will require all returned records to have values greater than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn The target column
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param \Segment\Model\production\AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    public function addSearchGreaterAll(\Segment\Model\Column $clmn, $exclusive = TRUE, \Segment\Model\production\AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $greater_n = $this->wrapper->getClassName('GreaterExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($values) ? [NULL] : $values->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            for($i=count($scalar)-1; $i>-1;--$i){
                $field .= strlen($field)>2 ? ', ?' : '?';
                $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalar[$i]));
            }
        }
        
        $this->addClause(new $greater_n($target, $field, $exclusive, GreaterExpression::ANY), $required_param);
    }
    
    /**
     * Query will require all returned records to have values less than the comparison
     * value for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param \Segment\Model\production\SingleValue $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    public function addSearchLesser(\Segment\Model\Column $clmn, $exclusive = TRUE, \Segment\Model\production\SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $lesser_n = $this->wrapper->getClassName('LesserExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($value) ? [NULL] : $value->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            $field = '?';
            $this->wrapper->setValuesEnd($value);
        }
        
        $this->addClause(new $lesser_n($target, $field, $exclusive), $required_param);
    }
    
    /**
     * Query will require all returned records to have values less than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn The target column
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param \Segment\Model\production\AnyAllValues $value
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchLesserAll(\Segment\Model\Column $clmn, $exclusive = TRUE, \Segment\Model\production\AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        $lesser_n = $this->wrapper->getClassName('LesserExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($values) ? [NULL] : $values->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            for($i=count($scalar)-1; $i>-1;--$i){
                $field .= strlen($field)>2 ? ', ?' : '?';
                $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalar[$i]));
            }
        }
        
        $this->addClause(new $lesser_n($target, $field, $exclusive, LesserExpression::ANY), $required_param);
    }

    /**
     * Query will require all returned records to have values lesser than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive
     * @param \Segment\Model\production\AnyAllValues $values
     * @param \Segment\Model\Statement $var_comp
     * @param boolean $required_param
     */
    public function addSearchLesserAny(\Segment\Model\Column $clmn, $exclusive = TRUE,
            \Segment\Model\production\AnyAllValues $values = NULL, \Segment\Model\Statement $var_comp = NULL,
            $required_param = TRUE)
    {
        $lesser_n = $this->wrapper->getClassName('LesserExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        $scalar = is_null($values) ? [NULL] : $values->getValues();
        if(!is_null($var_comp)&&is_null($scalar[0])){
            $field = $var_comp->getStatement();
            $this->wrapper->setValuesEnd(...$var_comp->getInputOutput()->getWhereValues());
        } else {
            for($i=count($scalar)-1; $i>-1;--$i){
                $field .= strlen($field)>2 ? ', ?' : '?';
                $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalar[$i]));
            }
        }
        
        $this->addClause(new $lesser_n($target, $field, $exclusive, LesserExpression::ANY), $required_param);
    }

    /**
     * Query will require all records to be outside a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param \Segment\Model\Column $clmn The target column
     * @param boolean $exclusive
     * @param \Segment\Model\production\BetweenValues $values
     * @param \Segment\Model\Statement $var_comp1
     * @param \Segment\Model\Statement $var_comp2
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchNotRange(\Segment\Model\Column $clmn, $exclusive = TRUE,
            \Segment\Model\production\BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL, $required_param = TRUE)
    {
        $field1; $field2;
        $range_n = $this->wrapper->getClassName('BetweenExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        /*$operator = $exclusive ? ' NOT BETWEEN ' : '<';
        $operator2 = $operator==='<' ? '>' : NULL;*/
        $scalars = is_null($values) ? [NULL, NULL] : $values->getValues();
        if(!is_null($var_comp1)&&is_null($scalars[0])){
            $field1 = $var_comp1->getStatement();
            $this->setValuesEnd($var_comp1->getValues());
        } else {
            $field1 = '?';
            $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalars[0]));
        }
        if(!is_null($var_comp2)&&is_null($scalars[1])){
            $field2 = $var_comp2->getStatement();
            $this->setValuesEnd($var_comp2->getValues());
        } else {
            $field2 = '?';
            $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalars[1]));
        }
        $options = $exclusive ? BetweenExpression::EXCL|BetweenExpression::NOT : BetweenExpression::NOT;
        $this->wrapper->addClause(new $range_n($target, $field1, $field2, $options), $required_param);
        //$this->where_statement->addendRange($target, $operator, $field1, $field2, $operator2);
    }

    /**
     * Query will require all records to be within a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param \Segment\Model\Column $clmn Target column
     * @param boolean $exclusive Exclusive: true. Inclusive: false.
     * @param \Segment\Model\production\BetweenValues $values
     * @param \Segment\Model\Statement $var_comp1 Least value
     * @param \Segment\Model\Statement $var_comp2 Most value
     * @param boolean $required_param Default: TRUE. Askes whether must be met for all records returned
     */
    public function addSearchRange(\Segment\Model\Column $clmn, $exclusive = TRUE,
            \Segment\Model\production\BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL, $required_param = TRUE)
    {
        $field1; $field2;
        $target = $clmn->getWhereColumn();
        $between_n = $this->wrapper->getClassName('BetweenExpression', __MODEL_PRODUCTION_NS__);
        $scalars = is_null($values) ? [NULL, NULL] : $values->getValues();
        if(!is_null($var_comp1)&&is_null($scalars[0])){
            $field1 = $var_comp1->getStatement();
            $this->wrapper->setValuesEnd($var_comp1->getValues());
        } else {
            $field1 = $this->wrapper->getPdoPlaceholder((string)$scalars[0]);
            $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalars[0]));
        }
        if(!is_null($var_comp2)&&is_null($scalars[1])){
            $field2 = $var_comp2->getStatement();
            $this->wrapper->setValuesEnd($var_comp2->getValues());
        } else {
            $field2 = $this->wrapper->getPdoPlaceholder((string)$scalars[0]);
            $this->wrapper->setValuesEnd(new \Segment\Model\production\SingleValue($scalars[1]));
        }
        $options = $exclusive ? BetweenExpression::EXCL : 0;
        $this->wrapper->addClause(new $between_n($target, $field1, $field2, $options), $required_param);
        //$this->where_statement->addendRange($target, $operator, $field1, $field2, $operator2);
    }
    
    /**
     * Queries DAO for non-exact matches over those fields set by business logic.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\SingleValue $value
     * @param boolean $required_param
     */
    public function addSearchWildcard(\Segment\Model\Column $clmn, \Segment\Model\production\SingleValue $value, $required_param = FALSE)
    {
        $field;
        $wild_n = $this->wrapper->getClassName('WildcardExpression', __MODEL_PRODUCTION_NS__);
        $target = $clmn->getWhereColumn();
        
        $scalar = is_null($value) ? [NULL] : $value->getValues();
        
        $field = '?';
        $this->wrapper->setValuesEnd($value);
        $statement = new $wild_n($target, $field);
        $this->addClause($statement, $required_param);
    }

    /**
     * Returns the WHERE clause target values
     * @return array<mixed>
     */
    public function getValues()
    {
        return $this->where_targets;
    }
    
    
    public function setCallQueue($func_n, ...$args)
    {
        error_log(__METHOD__ . " before call to parent. The function $func_n and the arguments"
                . " " . print_r($args, TRUE));
        parent::setCallQueue($func_n, ...$args);
    }

}