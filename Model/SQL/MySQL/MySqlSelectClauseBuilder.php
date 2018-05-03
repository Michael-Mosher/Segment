<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlSelectClauseBuilder extends \Segment\Model\DelayedBuilder implements \Segment\Model\SelectClauseBuilder
{
    /**
     * $select stores current Select clause
     * @var string
     */
    private $select = '';
    private $limit = '';
    private $order_by = array();
    private $group_by = array();
    private $wrapper;
    private $last_select_func;
    private $last_select_list = 'addReturnCountAll,addReturnAssignAll';
    private $last_addendum_func;
    private $last_addendum_list = '';
    private $prerequisite_statements = array();
    private $prerequisite_functions = array();
    
    public function __construct(\Segment\Model\InputOutput $wrapper)
    {
        $this->wrapper = $wrapper;
        $this->last_addendum_func = new \SplFixedArray(1);
        $this->last_select_func = new \SplFixedArray(1);
    }
    
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
    
    /**
     * Returns the clause as a string for querying model
     * @return string
     */
    public function getClause(array $tables = [])
    {
        $answer = '';
        
        // combine text and Statement objects to make clause
        $q = $this->callQueue();
        foreach ($q as $args){
            call_user_func_array([$this, key($args)], current($args));
        }
        $answer = $this->select;

        /**
         * If there are any SQL addendum clause components then create Addendum and pass to wrapper
         */
        if(count($this->group_by)>0 || count($this->order_by) || strlen($this->limit)>0){
            $addend_n = $this->wrapper->getClassName('Addendum', __MODEL_PRODUCTION_NS__);
            $addend = new $addend_n($this->order_by, $this->group_by, $this->limit);
            $this->wrapper->addendAddendum($addend);
        }
        
        return $answer;
    }
    
    public function addReturnColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        error_log(__METHOD__ . " you made it: " . print_r(func_get_args(), TRUE));
        $this->select .= strlen($this->select)>0 ? ', ' . $clmn->getReturnColumn() : $clmn->getReturnColumn();
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
    }
    
    public function addReturnCountColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->select .= strlen($this->select)>0 ? ', COUNT(' . $clmn->getReturnColumn() . ')'
                : 'COUNT(' . $clmn->getReturnColumn() . ')';
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
    }
    
    public function addReturnExpression(\Segment\Model\Expression $exp, $parenthesis = FALSE,
            \Segment\Model\production\StringValue $alias = NULL)
    {
        $lp = $parenthesis ? '(' : '';
        $rp = $parenthesis ? ')' : '';
        $this->select .= strlen($this->select)>0 ? ', ' . "{$lp}{$exp}{$rp}" : "{$lp}{$exp}{$rp}";
        $this->select .= is_null($alias) ? "" : " AS {$alias}";
    }
    
    /**
     * Assigns the value of each column to provided variable. Can only be used when
     *  the quantity of Variable is no more than the returning rows. If less than
     *  the returning rows, the first list will receive Variable assignment.
     * @param \Segment\Model\Variable $col_first
     * @param array<\Segment\Model\Variable> $col_others Optional. Default is NULL
     * @param callable $sub_last_func Optional. Default is NULL
     * @param array $last_func_args Optional. Default is an empty array.
     */
    public function addReturnAssignAll(\Segment\Model\Variable $col_first,
             array $col_others,  callable $sub_last_func = NULL,
            array $last_func_args = array())
    {
        $the_func = new \ReflectionFunction(__METHOD__);
        $normal_num = $the_func->getNumberOfParameters();
        $req_num = $the_func->getNumberOfRequiredParameters();
        $this->processSubLastFunc(func_get_args(), $normal_num, $req_num);
        if(count($col_others)>0){
            \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
            $var_args = array_merge ([$col_first], $col_others);
        }
        if($seg_count = count(explode(',', $this->select))<=count($var_args)){
            $this->select .= ' INTO ';
            for($i=$seg_count-1; $i>-1;$i--){
                $this->select .= $i===$seg_count-1 ? $var_args[$i] : ', ' . $var_args[$i];
            }
        }
    }
    
    /**
     * Counts rows returned of all, or any, columns. If columns have been added to the
     *  clause already then those are the ones to be counted. If $unique is TRUE then
     *  the number of distinct values will be counted.
     * @param boolean $unique
     */
    public function addReturnCountAll($unique = FALSE)
    {
        $the_func = new \ReflectionFunction(__METHOD__);
        $normal_num = $the_func->getNumberOfParameters();
        $req_num = $the_func->getNumberOfRequiredParameters();
        $this->processSubLastFunc(func_get_args(), $normal_num, $req_num);
        if($unique){
            $this->select =  'COUNT(DISTINCT ' . $this->select . ')';
        } else if(strlen($this->select)===0){
            $this->select .= 'COUNT(*)';
        } else {
            $this->select = 'COUNT('.$this->select . ')';
        }
    }

    public function addReturnMaxColumn(\Segment\Model\Column $clmn,\Segment\Model\production\StringValue $alias = NULL)
    {
        $this->select .= strlen($this->select)>0 ? ', MAX(' . $clmn->getReturnColumn() .')'
                : 'MAX('.$clmn->getReturnColumn() .')';
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
    }

    public function addReturnMeanColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->select .= strlen($this->select)>0 ? ', AVG(' . $clmn->getReturnColumn() . ')'
                : 'AVG('.$clmn->getReturnColumn() .')';
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
    }

    public function addReturnMedianColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $primary_db_t = $this->getPrimaryDbType();
        $secondary_db_t = $this->getSecondaryDbType();
        $db = $clmn->getDb();
        $column = $clmn->getReturnColumn();
        $table = $clmn->getTable();
        $inner_stmt1_tb = 'enumerator';
        $inner_stmt2_tb = 'total';
        $rownum_assignment = 'r';
        $total_alias = 'totalrows';
        $col_n = $this->wrapper->getClassName('Column', __MODEL_PRODUCTION_NS__);
        $tbl_n = $this->wrapper->getClassName('Table', __MODEL_PRODUCTION_NS__);
        $strval_n = $this->wrapper->getClassName('StringValue', __MODEL_PRODUCTION_NS__);
        $intval_n = $this->wrapper->getClassName('IntegerValue', __MODEL_PRODUCTION_NS__);
        $variable_n = $this->wrapper->getClassName('Variable', __MODEL_PRODUCTION_NS__);
        $stmt_bld_n = $this->wrapper->getClassName('StatementBuilder', __MODEL_PRODUCTION_NS__);
        $assig_exp_n = $this->wrapper->getClassName('AssignmentExpression', __MODEL_PRODUCTION_NS__);
        $add_exp_n = $this->wrapper->getClassName('AdditionExpression', __MODEL_PRODUCTION_NS__);
        $alias_exp_n = $this->wrapper->getClassName('AliasExpression', __MODEL_PRODUCTION_NS__);
        $avg_exp_n = $this->wrapper->getClassName('AverageExpression', __MODEL_PRODUCTION_NS__);
        $div_exp_n = $this->wrapper->getClassName('DivisionExpression', __MODEL_PRODUCTION_NS__);
        $lst_int_n = $this->wrapper->getClassName('LeastIntegerExpression', __MODEL_PRODUCTION_NS__);
        
        $median_n = new $strval_n('Median');
        $exp_median = new $variable_n($median_n);
        $renum = new $strval_n('rownum');
        $exp_renum = new $variable_n($renum);
        $row_num_n = new $strval_n('row_number');
        /*$stmt = "AVG({$inner_stmt1_tb}.{$column})";
        $this->select .= strlen($this->select)>0 ? ", {$stmt}"
                : $stmt;*/
        $pre_stmt = new $stmt_bld_n('GET');
        $inner_stmt1_bld = new $stmt_bld_n('GET');
        $inner_stmt2_bld = new $stmt_bld_n('GET');
        $inner_stmt3_bld = new $stmt_bld_n('GET');
        
        $this_where = $this->wrapper->cloneWhere();
        $pre_stmt->initialize($this_where);
        $inner_stmt1_bld->initialize($this_where);
        $inner_stmt2_bld->initialize($this_where);
        
        $inner_stmt1_bld->addReturnExpression(new $assig_exp_n(
                new $add_exp_n(
                        new $variable_n(
                                $renum
                        ),
                        new $intval_n(1)
                ),
                new $variable_n($renum)), FALSE, $row_num_n);
        $inner_stmt1_bld->addReturnColumn(new $col_n(new $tbl_n($db, $table), $column));
        $inner_stmt3_bld->addSearchExpression(
                new $assig_exp_n(new IntegerValue(0), $renum));
        $inner_stmt1_bld->setTable(new $alias_exp_n(
                $inner_stmt3_bld->getStatement(),
                new $strval_n($rownum_assignment),
                TRUE
                ));
        $pre_stmt->setTable(
                new $alias_exp_n(
                        $inner_stmt1_bld->getStatement(),
                        new $strval_n($inner_stmt1_tb),
                        TRUE
                ));
        
        $inner_stmt2_bld->addReturnCountColumn(
                new $col_n(new $tbl_n($db, $table), $column,
                        new $strval_n($total_alias))
                );
        $inner_stmt2_bld->setTable($table);
        
        $pre_stmt->setTable(new $alias_exp_n($inner_stmt2_bld, $inner_stmt2_tb, TRUE));
        $pre_stmt->addSearchEqualAny(
                new $col_n(
                        new $tbl_n(
                                $db,
                                $inner_stmt1_tb
                                ),
                        $row_num_n
                        ),
                new AnyAllValues([
                    new $lst_int_n(
                            new $div_exp_n(
                                    new $col_n(
                                            new $tbl_n(
                                                    $db,
                                                    $inner_stmt2_tb
                                            ),
                                            $total_alias
                                            ),
                                    2
                            )
                    ),
                    new $lst_int_n(
                            new $div_exp_n(
                                    new $add_exp_n(new $col_n(
                                            new $tbl_n(
                                                    $db,
                                                    $inner_stmt2_tb
                                            ),
                                            $total_alias
                                            ), 1),
                                    2
                            )
                    )
                ]),
                $var_comp);
        $pre_stmt->addReturnExpression(
                new $assig_exp_n(
                        new $avg_exp_n(
                                new $col_n(
                                        new $tbl_n(
                                                $db,
                                                $inner_stmt1_tb
                                        ),
                                        $column)
                        ),
                        $exp_median));
        $this->wrapper->setPreStatements($pre_stmt);
        $this->select .= strlen($this->select)>0 ? ', ' . ($select = new $alias_exp_n($exp_median, $median_n))
                : (string)$select;
        
        /*
         * SELECT @median:=AVG({$inner_stmt1_tb}.{$column}) as median FROM (
                SELECT @rownum:=@rownum+1 as `row_number`, {$table}.{$column}
                FROM {$table},  (SELECT @rownum:=0) r
                WHERE 1
                -- put some where clause here
                ORDER BY {$table}.{$column}
            ) as {$inner_stmt1_tb}, 
            (
                SELECT count(*) as total_rows
                FROM {$table}
                WHERE 1
                -- put same where clause here
            ) as {$inner_stmt2_tb}
            WHERE --1 AND
         * {$inner_stmt1_tb}.row_number IN ( ceil({$inner_stmt2_tb}.total_rows/2), ceil(({$inner_stmt2_tb}.total_rows+1)/2) );
        */
    }

    public function addReturnMinColumn(\Segment\Model\Column $clmn,\Segment\Model\production\StringValue $alias = NULL)
    {
        $this->select .= strlen($this->select)>0 ? ', MIN(' . $clmn->getReturnColumn() .')'
                : 'MIN('.$clmn->getReturnColumn() .')';
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
    }

    public function addReturnModeColumn(
            \Segment\Model\Column $clmn,
            \Segment\Model\production\StringValue $alias = NULL
            )
    {
        $this->select .= '';
        if(!is_null($alias))
            $this->select .= ' AS ' . $alias->getValues()[0];
        /*
         * SELECT category_id.category_id, count( category_id.category_id )
FROM `category_id`
GROUP BY category_id.category_id
ORDER BY count( category_id.category_id ) DESC 
         */
    }

    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if(strlen($this->limit)===0){
            if($absolute)
                $this->limit = "LIMIT {$start}, {$amnt}";
            else{
                $pre_statement = new \Segment\Model\production\StatementBuilder('GET');
                $clmn_n = $this->wrapper->getClassName('Column', __MODEL_PRODUCTION_NS__);
                $var_n = $this->wrapper->getClassName('Variable', __MODEL_PRODUCTION_NS__);
                $count = new $var_n('_row_max_var');
                $column;
                $distinct = FALSE;
                end($this->queue);
                for($i=key($this->queue);$i>0;$i--){
                    if(\Segment\utilities\Utilities::isArrayValueA($this->queue[$i], $clmn_n)){
                        //array_
                        $column = $this->queue[$i][key($this->queue[$i])];
                        
                        // use reflection of SelectClauseBuilder and match to 
                    }
                    if(key($this->queue[$i])==='addReturnUniqueColumn'){
                        $column = $column!==$this->queue[$i][key($this->queue[$i])] ?
                                $this->queue[$i][key($this->queue[$i])]
                                : $column;
                        $distinct = TRUE;
                    }
                }
                $pre_statement;
                $pre_statement->addReturnCountColumn($column);
                // get all functions queued in SearchClauseBuilder and add to $sub_statement
                
                'LIMIT ' . ((($start/100) * $count) + 1). ', ' . (($amnt/100) * $count);
            }
                
        }
    }

    public function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE)
    {
        $order = $asc ? 'ASC' : 'DESC';
        $this->order_by[] = '' . $clmn->getReturnColumn() . ' ' . $order;
    }

    public function addReturnUniqueColumn(\Segment\Model\Column $clmn)
    {
        $this->group_by[] = $clmn;
    }
    
    
    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $field = '';
        $interstitial = $operator = ' AND ';
        $statement = ' UNION ' . $first_var->getStatement();
        $this->setValuesEnd($first_var->getValues());
        for($i=count($var_comp)-1;$i>-1;$i--){
            $field = $var_comp[$i]->getStatement();
            $this->setValuesEnd($var_comp[$i]->getValues());
            $statement .= $operator . $field;
        }
        
        $this->addClause($statement, $interstitial);
    }
    
    
    public function addIntersection(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $field = '';
        $interstitial = $operator = ' AND ';
        $statement = ' INTERSECT ' . $first_var->getStatement();
        $this->setValuesEnd($first_var->getValues());
        for($i=count($var_comp)-1;$i>-1;$i--){
            $field = $var_comp[$i]->getStatement();
            $this->setValuesEnd($var_comp[$i]->getValues());
            $statement .= $operator . $field;
        }
        
        $this->addClause($statement, $interstitial);
    }
    
    public function setCallQueue($func_n, ...$args)
    {
        $special_select = explode(',', $this->last_select_list);
        $special_addend = explode(',', $this->last_addendum_list);
        $incumbent_last = [NULL => NULL];
        if(array_search($func_n, $special_addend)){
            if(isset($this->last_addendum_func)){
                $incumbent_last = $this->last_addendum_func;
            }
            $this->last_addendum_func = [
                $func_n => array_merge($args, $incumbent_last)
            ];
        } else if(array_search($func_n, $special_select)){
            if(isset($this->last_select_func)){
                $incumbent_last = $this->last_select_func;
            }
            $this->last_select_func = [
                $func_n => array_merge($args, $incumbent_last)
            ];
        } else {
            parent::setCallQueue($func_n, ...$args);
        }
    }
    
    private function processSubLastFunc(array $all_func_args, $normal_num, $req_num)
    {
        
        $length = count($all_func_args)-$normal_num;
        $offset = -1*$length;
        $sub_func_args = array_slice($all_func_args, $offset, $length);
        $sub_last_func = $sub_func_args[0];
        unset($sub_func_args[0]);
        $this->$sub_last_func(...$sub_func_args);
        
    }

}