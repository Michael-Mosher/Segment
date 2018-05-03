<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

/**
 * Class for submitting requests from Controller to query the DAO. It takes REST
 *     function identifiers and arguments, using them to build queries.
 */
class ModelCaller implements \Segment\Model\SelectClauseBuilder, \Segment\Model\SearchClauseBuilder,
        \Segment\Model\SetClauseBuilder
{
    use \Segment\utilities\AbstractClassNamesGetter;
    private $input_output;
    protected $call_id;
    private $db_table_name;
    private $db_column_name;
//    const EQUAL = 'equal';
//    const NEQUAL = 'nequal';
//    const EQUALANY = 'equalany';
//    const GREATER = 'greater';
//    const GREATEQ = 'greateq';
//    const GREATANY = 'greatany';
//    const GREATALL = 'greatall';
//    const LESSER = 'lesser';
//    const LESSEQ = 'lesseq';
//    const LESSANY = 'lessany';
//    const LESSALL = 'lessall';
//    const BETWEEN = 'between';
//    const NBETWEEN = 'nbetween';
    const EXCLUSIVERANGE = 2048; // bool field
    const ANDORNOCONJ = 4096; // "and", "or", "noconj
    const STATEMENT1 = 4; // \Segment\Model\Statement
    const STATEMENT2 = 8; // \Segment\Model\Statement
    const LIMITSTARTNUM = 16; // float
    const LIMITSTARTABS = 32; // bool (TRUE absolute, FALSE percentage)
    const LIMITAMTNUM = 64; // float
    const LIMITAMTABS = 128; // bool (TRUE absolute, FALSE percentage)
    const ALIAS = 256; // string
    const ORDERDIRECTION = 512;
    const DELETETABLE = 1024;


    /**
     * Construct ModelCaller
     * @param string $call_id Name of the called HTTP function.
     * @param string $req_type string representing request method type
     * @param string $tbl_n The executable string name of a \Segment\Model\Table class.
     * @param string $clmn_n The executable string name of a \Segment\Model\Column class.
     */
    public function __construct(string $call_id, string $req_type, string $tbl_n, string $clmn_n)
    {
        $this->input_output = new StatementBuilder(StatementBuilder::getQueryType($req_type));
        $this->call_id = $call_id;
        $this->db_table_name = $tbl_n;
        $this->db_column_name = $clmn_n;
    }
    
    
    /**
     * Uses the parsed REST data and calls the model returning a Record object.
     * @return \Segment\utilities\Record
     */
    public function execute()
    {
        $answer = array();
        $rows = $this->query(
                $statement = $this->input_output->getStatement(),
                $this->input_output->getWhereValues(),
                $assoc_fetch = new AssocQHFetchArgBuilder()
        );
        error_log(__METHOD__ . " the statement: $statement");
        foreach($rows as $k => $v):
            if(is_int($k)&& is_a($v, "\Segment\utilities\Record")){
                $answer = $rows;
                return $answer;
            }
            break;
        endforeach;
        end($rows);
        for($i=key($rows);$i>-1;$i--):
            $row = $rows[$i];
            $answer[] = $this->makeRecord($row);
        endfor;
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
        $answer = $handler->query(
                $statement,
                $this->input_output->getWhereValues(),
                $fetch_arg = new AssocQHFetchArgBuilder(),
                $this->call_id
        );
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
        return $this->input_output->getStatement();
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
                        return SQL\MySQL\MySqlQueryHandler::getQueryHandler();
                    case 'MSSQL':
                        return SQL\MSSQL\MsSqlQueryHandler::getQueryHandler();
                    case 'DB2':
                        return DB2\DB2_Current\Db2QueryHandler::getQueryHandler();
                    case 'Oracle':
                        return Oracle\Oracle_Current\OracleQueryHandler::getQueryHandler();
                    case 'PostGRE':
                        return SQL\PostGRE\PostGreQueryHandler::getQueryHandler();
                    case 'SQLite':
                        return SQL\SQLite\SqLiteQueryHandler::getQueryHandler();
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
    protected function processParsedRest(array $parsed_rest, \Segment\utilities\DbDescription $rubric,
            $unique_id_table = NULL, $wild_tables = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
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
            switch ($this->input_output->getTypeName()) {
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
     * Add return behavior to the StatementBuilder queue.
     * @param string $clmn
     * @param string $tbl
     * @param string $operator
     * @param array $options
     */
    public function makeReturnRequest($clmn, $tbl, $operator, array $options)
    {
        $a_raw = $options[self::ALIAS] ?? NULL;
        if(is_string($a_raw)){
            $alias = new StringValue($a_raw);
        }
        $limit_start = $options[self::LIMITSTARTNUM] ?? NULL;
        $limit_start_absolute = $options[self::LIMITSTARTABS] ?? FALSE;
        $limit_length_amount = $options[self::LIMITAMTNUM] ?? NULL;
        $limit_length_absolute = $options[self::LIMITAMTABS] ?? FALSE;
        if($limit_length_absolute || $limit_length_amount || $limit_start || $limit_start_absolute){
            $this->input_output->addReturnQuantityRows($limit_length_absolute, $limit_length_amount, $limit_start_absolute, $limit_start);
        }
        
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->input_output->setTable(
                new $this->db_table_name(
                        Segment\Model\production\__PROJECT_ACRONYM__, $tbl
                )
        );
        switch (trim(strtolower($operator))) {
            case "normal":
                $this->addReturnColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(
                                        Segment\Model\production\__PROJECT_ACRONYM__, $tbl),
                                $clmn),
                        $alias);
                break;
            case "field_count":
                $this->addReturnCountColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(
                                        Segment\Model\production\__PROJECT_ACRONYM__, $tbl),
                                $clmn),
                        $alias);
                break;
            case "field_avg":
                $this->addReturnMeanColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(__DB_NAME__, $tbl),
                                $clmn),
                        $alias);
                break;
            case "field_set":
                $this->addReturnUniqueColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(__DB_NAME__, $tbl),
                                $clmn),
                        $alias);
                break;
            case "field_mode":
                $this->addReturnModeColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(__DB_NAME__, $tbl),
                                $clmn),
                        $alias);
                break;
            case "field_median":
                $this->addReturnMedianColumn(
                        new $this->db_column_name(
                                new $this->db_table_name(__DB_NAME__, $tbl),
                                $clmn),
                        $alias);
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
     * Adds a search parameter to the StatementBuilder, or other InputOutput-extending class instance, located at $this->input_output
     * @param string $target Column name
     * @param string $t_table Table name
     * @param mixed $value Primitive or scalar array. Condition or threshold records must meet in the search
     * @param string $operator ModelCaller const
     */
    public function makeSearchRequest(string $target, string $t_table, $value, string $operator, array $options)
    {
        error_log(__METHOD__ . " the args: target: $target, table name: $t_table, value: "
                . print_r($value, TRUE) . " operator: $operator");
        //\Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $descrip = $this->controller->getDescription();
        $search_func_n = $this->getRestSearch($operator);
        $this->input_output->setTable(new $this->db_table_name(Segment\Model\production\__PROJECT_ACRONYM__, $t_table));
        
        $required_param = !isset($options[self::ANDORNOCONJ]) ? TRUE :
                strtolower(trim($options[self::ANDORNOCONJ]))==="or" ? FALSE : TRUE;
        $statement1 = $options[self::STATEMENT1] ?? NULL;
        $statement2 = $options[self::STATEMENT2] ?? NULL;
        $alias = $options[self::ALIAS] ?? NULL;
        $exclusive_range = $options[self::EXCLUSIVERANGE] ?? TRUE;
        
        if(is_array($value)){
            for($i=count($value)-1;$i>-1;$i--){
                if(is_array($v)&&$this->isModelCallerClassName(key($v))){
                    $caller_name = key($v);
                    $new_caller = new $caller_name($v[$caller_name]);
                    $value[$i] = $new_caller->build_args->getStatement();
//                        $search = key($v);
//                        $args = $v[$search];
//                        $this->makeSearchRequest(
                }
//                if($this->isRestSearch($value[$i])){
//                    $array = explode('_',$value[$i]);
//                    array_walk($array, strtolower());
//                    array_walk($array, ucfirst());
//                    $sub_search_func_n = implode('', $array);
//                    
//                }
                if(!is_scalar($value[$i])&&!is_a($value[$i], 'Statement')){
                    $value[$i] = NULL;
                }
            }
        }
        
        switch(strtolower(trim($operator))){
            case ModelCaller::EQUAL:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchEqual(
                        $clmn,
                        new $value_class_n($temp_value),
                        $statement1, $required_param);
                break;
            case ModelCaller::NEQUAL:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchNotEqual(
                        $clmn,
                        new $value_class_n($temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::EQUALANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--):
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                endfor;
                $temp_value = new AnyAllValues(...$temp_value);
                $this->input_output->addSearchNotEqual(
                        $clmn,
                        $temp_value,
                        $statement1, $required_param);
                break;
            case ModelCaller::GREATER:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchGreater(
                        $clmn,
                        FALSE,
                        new $value_class_n($temp_value),
                        $statement1, $required_param
                        );
                break;
            case ModelCaller::GREATEQ:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchGreater(
                        $clmn,
                        TRUE,
                        new $value_class_n($temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::GREATANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--):
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                endfor;
                $this->input_output->addSearchGreaterAny(
                        $clmn, $exclusive_range,
                        new AnyAllValues($temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::GREATALL:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--):
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                endfor;
                $this->input_output->addSearchGreaterAll(
                        $clmn, $exclusive_range,
                        new AnyAllValues(...$temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::LESSER:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchLesser(
                        $clmn,
                        TRUE,
                        new $value_class_n($temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::LESSEQ:
                $temp_value = is_array($value) ? $value[0] : $value;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp_value, $type);
                $this->input_output->addSearchLesser(
                        $clmn,
                        FALSE,
                        new $value_class_n($value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::LESSANY:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value); $i>-1;$i--):
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                endfor;
                //$this->addSearchLesserAny($clmn, $exclusive, $values, $stmt, $required_param);
                $this->input_output->addSearchLesserAny(
                        $clmn,$exclusive_range,
                        new AnyAllValues(...$temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::LESSALL:
                $temp_value = is_array($value) ? $value : [$value];
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                end($temp_value);
                for($i=key($temp_value);$i>-1;$i--):
                    settype($temp_value[$i], $type);
                    $temp_value[$i] = new $value_class_n($temp_value[$i]);
                endfor;
                $this->input_output->addSearchLesserAll(
                        $clmn, $exclusive_range,
                        new AnyAllValues(...$temp_value),
                        $statement1, $required_param
                );
                break;
            case ModelCaller::BETWEEN:
                $temp1 = is_array($value)&&isset($value[0]) ? $value[0] : $value;
                $temp2 = is_array($value)&&isset($value[1]) ? $value[1] : NULL;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp1, $type);
                settype($temp2, $type);
                $this->input_output->addSearchRange(
                        $clmn, $exclusive_range,
                        new BetweenValues(
                                new $value_class_n($temp1),
                                new $value_class_n($temp2)
                        ), $statement1, $statement2, $required_param
                );
                break;
            case ModelCaller::NBETWEEN:
                $temp1 = is_array($value)&&isset($value[0]) ? $value[0] : $value;
                $temp2 = is_array($value)&&isset($value[1]) ? $value[1] : NULL;
                $clmn = new $this->db_column_name(
                                new $this->db_table_name(
                                        __DB_NAME__,
                                        $t_table
                                ),
                                $target
                        );
                $type = $descrip->getType($clmn);
                $value_class_n = ucfirst($type) . "Value";
                settype($temp1, $type);
                settype($temp2, $type);
                $this->input_output->addSearchNotRange(
                        $clmn, $exclusive_range,
                        new BetweenValues(
                                new $value_class_n($temp1),
                                new $value_class_n($temp2)
                        ), $statement1, $statement2, $required_param
                );
                break;
            default:
                throw new \InvalidArgumentException(
                        __CLASS__ . '::'. __METHOD__ . " expects fourth argument to"
                        . " be a const of ModelCaller. Provided: " . print_r($operator,TRUE)
                        );
        }
    }
    
    /**
     * Adds a request to add a new value to the database in the queued statement
     * @param string $clmn
     * @param string $tbl
     * @param mixed $new_value
     */
    public function makeSetRequest($clmn, $tbl, $new_value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $tbl_n = $this->getClassName('Table', __MODEL_PRODUCTION_NS__);
        $clmn_n = $this->getClassName('Column', __MODEL_PRODUCTION_NS__);
        $table = new $tbl_n(__DB_NAME__, $tbl);
        $column = new $clmn_n($table, $clmn);
        $this->input_output->setTable($table);
        if(is_float($new_value)){
            $this->input_output->addSet($column, new FloatValue($new_value));
        } else if(is_int($new_value)){
            $this->input_output->addSet($column, new IntegerValue($new_value));
        } else if (is_bool($new_value)){
            $this->input_output->addSet($column, new BitValue($new_value));
        } else {
            $this->input_output->addSet($column, new StringValue((string)$new_value));
        }
    }
    
    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $this->input_output->setTable($target);
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
    protected function addReturnColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the mean value of which to be returned.
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnMeanColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the unique values found of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnUniqueColumn(\Segment\Model\Column $clmn)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the modal average of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnModeColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the median average of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnMedianColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the maximum value of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnMaxColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the minimum value of which to be returned
     * @param \Segment\Model\Column $clmn
     * @param StringValue $alias Optional. How column will be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnMinColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add a column, the values of which to be sorted
     * @param \Segment\Model\Column $clmn
     * @param boolean $asc Ascending: TRUE. Descending: FALSE.
     */
    protected function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add limitation to number of records returned
     * @param boolean $absolute_amnt Scalar: TRUE. Percentage: FALSE
     * @param float $amnt
     * @param bool $absolute_start Scalar: TRUE. Percentage: FALSE
     * @param float $start
     */
    protected function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0)
    {
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Adds request for count of values in given column.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\StringValue $alias Optional. How column will
     *  be represented, the output alias. If omitted, NULL.
     */
    protected function addReturnCountColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Add expression to be executed.
     * @param \Segment\Model\Expression $exp
     * @param boolean $parenthesis TRUE expression to be wrapped in parenthesis.
     * @param StringValue $alias
     */
    protected function addReturnExpression(\Segment\Model\Expression $exp, $parenthesis = FALSE, StringValue $alias = NULL)
    {
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * All of the records from each query together.
     * @param \Segment\Model\Statement $first_var First query of the union
     * @param \Segment\Model\Statement $var_comp One or more additional queries of the union
     */
    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $tables = $first_var->getInputOutput()->getTables();
        foreach($var_comp as $stmt){
            $tables = $tables + $stmt->getInputOutput()->getTables();
        }
        foreach($tables as $table){
            $this->input_output->setTable($table);
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
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
        $tables = $first_var->getInputOutput()->getTables();
        foreach($var_comp as $stmt){
            $tables = $tables + $stmt->getInputOutput()->getTables();
        }
        foreach($tables as $table){
            $this->input_output->setTable($table);
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require column of records to equal a supplied value. Optionally, the 
     * value may be derived dynamically from another query.
     * @param \Segment\Model\Column $clmn
     * @param SingleValue $value
     * @param \Segment\Model\Statement $stmt Optional. Default value is NULL.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchEqual(\Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        error_log(__METHOD__);
        $method_n = __METHOD__;
        $method_n = \explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to equal any supplied value for the respective column.
     * Values for comparison may come in part or whole another query.
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\Statement $stmt Optional. Default is NULL.
     * @param AnyAllValues $values
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchEqualAny(\Segment\Model\Column $clmn, AnyAllValues $values = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to be within a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false.
     * @param BetweenValues $values
     * @param \Segment\Model\Statement $stmt1 Least value
     * @param \Segment\Model\Statement $stmt2 Most value
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchRange(\Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $stmt1 = NULL, \Segment\Model\Statement $stmt2 = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt1)){
            $tables = $stmt1->getInputOutput()->getTables();
            if(!is_null($stmt2)){
                $tables = $tables + $stmt2->getInputOutput()->getTables();
            }
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all records to be outside a range of values for the respective column.
     * Whether column values equal to the range demarcations will be accepted requires $exclusive to be FALSE.
     * Values for comparison may come, in part or whole, from another query.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive
     * @param BetweenValues $values Optional. Default NULL. The two values the record must lie between. Can be omitted if using two Statements.
     * @param \Segment\Model\Statement $stmt1 Optional. Default NULL.
     *     If the record must lie between values that are dynamically derived populate these Statements
     * @param \Segment\Model\Statement $stmt2 Optional. Default NULL.
     *     If the record must lie between values that are dynamically derived populate these Statement.
     *     Will cancel $values.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchNotRange(\Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $stmt1 = NULL, \Segment\Model\Statement $stmt2 = NULL,
            $required_param = TRUE
            )
    {
        
        if(!is_null($stmt1)){
            $tables = $stmt1->getInputOutput()->getTables();
            if(!is_null($stmt2)){
                $tables = $tables + $stmt2->getInputOutput()->getTables();
            }
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than the comparison
     * for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value Optional. Default NULL. Can be omitted if value will be derived dynamically via $stmt.
     * @param \Segment\Model\Statement $stmt Optional. Default NULL. Dynamic source of value.
     *     Including it will cancel $value.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchGreater(\Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value Optional. Default NULL. Specific value, or values, to compare to.
     * @param \Segment\Model\Statement $stmt Optional. Default NULL. Dynamic values. Will cancel $value.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchGreaterAny(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $value = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values greater than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value Optional. Default NULL. Specific value, or values, to compare to.
     * @param \Segment\Model\Statement $stmt Optional. Default NULL. Dynamic values. Will cancel $value.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchGreaterAll(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than the comparison
     * value for the respective column. If values equal to the threshold are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param SingleValue $value Optional. Default NULL. Static value to compare to.
     * @param \Segment\Model\Statement $stmt Optional. Default NULL. Dynammic value that cancels $value.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchLesser(\Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than any of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value Optional. Default NULL. Static value, or values, to compare to.
     * @param \Segment\Model\Statement $stmt Optional. Default NULL. Dynamic values that cancel $value.
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchLesserAny(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $stmt = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * Query will require all returned records to have values less than all of the comparison
     * values for the respective column. If values equal to the thresholds are acceptable
     * $exclusive must be FALSE.
     * @param \Segment\Model\Column $clmn
     * @param boolean $exclusive Exclusive: true. Inclusive: false. By default the value is TRUE
     * @param AnyAllValues $value Optional. Default NULL. Static value, or values, to compare to.
     * @param \Segment\Model\Statement $var_comp Optional. Default NULL. Dynamic values that cancel $value
     * @param boolean $required_param Default: TRUE. Asks whether must be met for all records returned
     */
    protected function addSearchLesserAll(\Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE)
    {
        
        if(!is_null($stmt)){
            $tables = $stmt->getInputOutput()->getTables();
            foreach($tables as $table){
                $this->input_output->setTable($table);
            }
        }
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }
    
    /**
     * 
     * @param \Segment\Model\Column $clmn
     * @param \Segment\Model\production\SingleValue $value
     * @param type $required_param
     */
    protected function addSearchWildcard(\Segment\Model\Column $clmn, SingleValue $value, $required_param = TRUE)
    {
        
        $method_n = __METHOD__;
        $method_n=explode('::',$method_n)[1];
        $this->input_output->$method_n(...func_get_args());
    }

    public function getClause(\Segment\Model\Table ...$tables)
    {
        return '';
    }
    
    /**
     * Make a statement clause without knowing what type.
     * @param string $operator
     * @param string $column
     * @param string $table
     * @param mixed $value
     * @param array $options An array using the numeric \Segment\Model\ModelCaller
     *      constants as keys for optional arguments.
     */
    public function makeClauseBlind(string $operator, string $column, string $table, $value, array $options)
    {
        $reflect_return = new \ReflectionClass("\Segment\Controller\RestReturn");
        $payload = strtolower(trim($operator));
        $reflect_search = new \ReflectionClass("\Segment\Controller\RestSearch");
        if($payload==="posts" && $payload==="requests"){
            $this->makeSetRequest($column, $table, $value, $options);
        } else if(array_search ($payload, $reflect_return->getConstants(), TRUE)){
            $this->makeReturnRequest($column, $table, $payload, $options);
        } else if(array_search($payload, $reflect_search->getConstants(), TRUE)){
            $this->makeSearchRequest($column, $table, $value, $payload, $options);
        }
    }

}