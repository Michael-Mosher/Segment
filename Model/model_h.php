<?php

interface Query_Handler /* Singleton Database Access Object */
{
    public static function getQueryHandler();
    public static function describeTable($table_name_string);       
    public function query($query_statement_string, array $where_clause, QHFetchArgBuilder $fetch);
}    

class MySQLQH implements Query_Handler
{
    private static $instance;
    private $pdo = null;
    /*private $asf = null;*/
    private $sql_stmt = '';

    private function __construct(){}

    public static function getQueryHandler()
    {
        if (self::$instance == null) {
            $class_name = __CLASS__;
            self::$instance = new $class_name();
            self::$instance->pdo = self::startConnection();
            //self::query($this->pdo, $command);
        }

        return self::$instance;
    }
    
    private static function startConnection()
    {
        $arg = 'mysql:dbname='. __DB_NAME__ . ';host=' . __HOST__ . ';charset=utf8';
        try {
            $link = new PDO($arg, __DB_USER__, __DB_PASS__);
            $link->exec('USE ' . __DB_NAME__);
        } catch (PDOException $e) {
            echo "Database Connection Failed\n";
            exit();
        }
        return $link;
    }
        
    public static function describeTable($table)
    {
        if(!is_string($table)){
            trigger_error('describeTable expected Argument 1 to be String', E_USER_WARNING);
        }
        try{
            $singleton = self::getQueryHandler();
            
            $sql = 'DESCRIBE ' . $table . ';';
            $PDOS = $singleton->pdo->prepare($sql);
            
            $PDOS->execute();
            
            $answer = $PDOS->fetchAll();
            return $answer;
        } catch(PDOException $e) {
            error_log('Error executing describeTable: ' . print_r($e, true));
            echo "Database Connection Failed\n";
            exit();
        }
    }
        
    public function query($query_statement_string, array $where_clause, QHFetchArgBuilder $fetch)
    {
        if(!is_string($query_statement_string)){
            trigger_error('query expected Argument 1 to be String', E_USER_WARNING);
        }
        try{
            $query_statement_string .= ';';
            $set_attribute_status = $this->pdo->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, PDO::CASE_NATURAL);
            $PDOS = $this->pdo->prepare($query_statement_string);
            if(Utilities::checkArrayEmpty($where_clause)){
                $statement_execute_success = $PDOS->execute();
            } else {
                $this->processWhereParams($PDOS, $where_clause);
                $statement_execute_success = $PDOS->execute($where_clause);
            }
            if(!$statement_execute_success)
                error_log('Error executing PDOStatement');
            $answer = $fetch->fetch($PDOS);
            $this->MySQLCleanUp($answer);
            return $answer;
        } catch(PDOException $e) {
            error_log('Error executing query: ' . print_r($e, true));
            echo "Database Connection Failed\n";
            exit();
        }
    }
    
    private function processWhereParams(PDOStatement &$statement, array $where_params)
    {
        for($i = 0, $max = count($where_params); $i<$max; $i++){
            $param = $where_params[$i];
            if(is_string($param)){
                $statement->bindParam($i+1, $where_params[$i], PDO::PARAM_STR);
            } else if(is_bool($param)){
                $statement->bindParam($i+1, $where_params[$i], PDO::PARAM_BOOL);
            } else if(is_int($param)){
                $statement->bindParam($i+1, $where_params[$i], PDO::PARAM_INT);
            } else if(is_null($param)){
                $statement->bindParam($i+1, $where_params[$i], PDO::PARAM_NULL);
            } else
                continue;
        }
    }
    
    private function MySQLCleanUp(array &$rows)
    {
        for($i=count($rows)-1;$i>-1;--$i){
            $rows[$i] = json_decode(str_replace('":,', '":0,',str_replace(
            '"\u0000"', '0', str_replace(
                    '"\u0001"', TRUE, $rows[$i]))), TRUE);
        }
    }
}
interface QHFetchArgBuilder
{
    public function fetch(PDOStatement $stmt);
}
class AssocQHFetchArgBuilder implements QHFetchArgBuilder
{
    public function __construct()
    {}

    public function fetch(PDOStatement $stmt)
    {
        $answer = array(
            'test'
        );
        $i = 0;
        while($entry = $stmt->fetch(PDO::FETCH_NAMED)){
            $answer[$i++] = json_encode($entry);
        }
        return $answer;
    }
}

interface WhereStatementArgBuilder
{
    public function __construct(array $where_targets, array $operators);
    public function __invoke();
}

class MySQLWhereStatementArgBuilder implements WhereStatementArgBuilder
{
    private $where_targets;
    private $operators;
    private $interstitial;
    public function __construct(array $where_targets, array $operators, $interstitial = ' AND ')
    {
        $this->operators = Utilities::arrayCopy($operators);
        $this->convertOperators();
        $this->where_targets = Utilities::arrayCopy($where_targets);
        $this->interstitial = is_string($interstitial)&&(
                strtolower(trim($interstitial))==='and'||strtolower(trim($interstitial))==='or')
                ? $interstitial : ' AND ';
    }
    
    public function __invoke()
    {
        $answer = '';
        for($i = 0, $max = count($this->where_targets); $i<$max; $i++){
            if(is_array($this->where_targets[$i])){
                $inner_clause_interstitial = strpos(strtolower(trim($answer)), 'and')===0
                        ||strpos(strtolower(trim($answer)), 'and')>0 ? ' OR ' : ' AND ';
                $inner_clause = new MySQLWhereStatementArgBuilder(
                        $this->where_targets[$i], $this->operators[$i], $inner_clause_interstitial);
                $answer .= $inner_clause();
            } else if (is_string($this->where_targets[$i])){
                $answer .= strlen($answer)>0 ?
                        $this->interstitial . ' ' . $this->where_targets[$i] . ' '
                        . $this->operators[$i] . ' ?'
                        : $this->where_targets[$i] . ' ' . $this->operators[$i] . ' ?';
            }
        }
        return $answer;
    }
    
    private function convertOperators()
    {
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
                case 'in': break;
                default : throw new InvalidArgumentException('Invalid SQL WHERE operator provided as entry ' . $i);
            }
        }
    }
}

interface Statement
{
    public function __construct();
    public function initialize(InputOutput $collection);
        /* use getFormattedTables and other methods to generate
        *  statement for Query_Handler */
    public function getGetStatement(InputOutput $collection);
    public function getPostStatement(InputOutput $collection);
    public function getPutStatement(InputOutput $collection);
    public function getDeleteStatement(InputOutput $collection);
}

class SQLStatement implements Statement
{
    private $input_output;

    public function __construct()
    { }

    public function initialize(InputOutput $collection)
    {
        if(!empty($collection)){
            $this->input_output  = clone $collection;
            return true;
        }
        return false;
    }
 /* use getFormattedTables and other methods to generate
*  statement for Query_Handler */
    private function getFormattedTables()
    {
        $answer ='';
        $array = $this->input_output->getTables();
        error_log('SQLStatement getFormattedTables $array: ' . print_r($array, true));
        if(!empty($array)){
            $previous_table = '';
            foreach($array as $column => $table){
                if(empty($answer)){
                    $answer .= $table;
                    $previous_table = $table;
                    continue;
                }
                $answer .= ' LEFT JOIN ' . $table . ' ON ' . $previous_table . '.' . $column
                        .' = '. $table . '.' . $column;
                $previous_table = $table;
            }
        }
        return $answer;
    }
    
    private function getFormattedColumns()
    {
        $answer = '';
        $array = $this->input_output->getColumns();
        if(is_array($array)){
            foreach($array as $key => $value){
                if(is_array($value)){
                    foreach($value as $temp){
                        if(empty($answer))
                            $answer .= "$key.$temp";
                        else
                            $answer .= ",$key.$temp";
                    }
                }
            }
        }
        return $answer;
    }
    
    private function getPutFormattedColumns()
    {
        $answer = '';
        $columns_array = $this->input_output->getColumns();
        $put_values_array = $this->input_output->getPutValues();
        if(count($columns_array)===count($put_values_array)){
            $i = 0;
            foreach($columns_array as $temp_key => $temp_value){
                if(empty($answer))
                    $answer .= $temp_key.'.'.$temp_value . ' = ' . $put_values_array[$i++];
                else
                    $answer .= ', ' . $temp_key.'.'.$temp_value . ' = ' . $put_values_array[$i++];
            }
        }
        return $answer;
    }
    

    public function getGetStatement(InputOutput $collection)
    {
        $this->input_output = clone $collection;
        $answer = 'SELECT ';
        $answer .= $this->getFormattedColumns();

        $answer .= ' FROM ' . $this->getFormattedTables();
        $answer .= empty($this->input_output->getWhere()) ? ''
                    : ' WHERE ' . $this->input_output->getWhere();
        $answer .= $this->input_output->getAddendum();
        return $answer;
    }

    public function getPutStatement(InputOutput $collection)
    {
        $this->input_output = clone $collection;
        $answer = 'UPDATE SET '. $this->getPutFormattedColumns() . ' FROM '
                . $this->getFormattedTables();
        $answer .= empty($this->input_output->getWhere()) ? ''
                : 'WHERE ' . $this->input_output->getWhere() . $this->input_output->getAddendum();

        return $answer;
    }

    public function getPostStatement(InputOutput $collection)
    {
        $this->input_output = clone $collection;
        $answer = empty($this->input_output->getAddendum()) ? ''
                : 'INSERT INTO ' . $this->getFormattedTables() . ' ('
                . $this->getFormattedColumns() . ') VALUES ' . $this->input_output->getAddendum();
        return $answer;
    }
    
    public function getDeleteStatement(InputOutput $collection)
    {
        $this->input_output = clone $collection;
        $answer = (empty($this->input_output->getWhere())||Utilities::checkArrayEmpty($this->input_output->getWhereValues()))
                ? '' : 'DELETE FROM ' . $this->getFormattedTables($this->tables)
                . ' WHERE ' . $this->input_output->getWhere() . $this->input_output->getAddendum();
        return $answer;
    }
    
    private function isMultiToMulti()
    {
        return count($this->input_output->getTables())>2;
    }
}

class InputOutput
{
    private $qh;
    private $columns = array(); /* key value pairs where table name is key, column is value. */
    private $tables = array(); /* array of tables. More than one will be joined by NATURAL JOIN.
     * First table with key 0, subsequent tables must have column name to be naturally joined as key. */
    private $where = ''; /* WHERE statement with ':column_name' in place of values */
    private $where_values = array(); /* Those values to replace variables. Key/value pairs with
     * ':column_name' as key, value as value. */
    private $addendum = ''; /* Closing statements such as ORDER BY, GROUP BY, LIMIT.
     * The values to be inserted separated by commas when making INSERT statement. */
    private $put_values = array(); /* list of values whose position corresponds w/ the 
     * reciprocal position of the column they are assigned to in the $columns array */
    private $output = null;
    private static $thread_count = 0;

    function __construct(InputOutputArgvBuilder $obj, Query_Handler $PDOOutput,
            QHFetchArgBuilder $qh_fetch_arg = NULL)
    {
        $this->initialize($obj);
        $this->qh = $PDOOutput;
        $this->qh_fetch_arg = isset($qh_fetch_arg) ? $qh_fetch_arg : new AssocQHFetchArgBuilder();
    }

    public function initialize(InputOutputArgvBuilder $obj)
    {
        $this->columns = $obj->getColumns();
        $this->tables = $obj->getTable();
        $this->where = $obj->getWhereStatement();
        $this->where_values = $obj->getWhereValues();
        $this->addendum = $obj->getAddendum();
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getWhereValues()
    {
        return $this->where_values;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function getAddendum()
    {
        return $this->addendum;
    }

    public function getPutValues()
    {
        return $this->put_values;
    }

    public function get($statement_class_name)
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

    public function post($statement_class_name)
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

    public function put($statement_class_name)
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

    public function delete($statement_class_name)
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
}


class SelectStatement extends Statement
{

    public function __construct()
    {
    
    }
        
    function getWhereClause()
    {
        return $this->where_values;
    }

    function initialize($collection = null)
    {
        if(!empty($collection)){
            $this->tables = $collection->getTables();
            
            $this->columns = $collection->getColumns();
            $this->where_values = $collection->getWhereValues();
            $this->where = $collection->getWhere();
            $this->addendum = $collection->getAddendum();
            $this->resultsProcessor  = clone $collection;    
        }
        
        return $this;
    }
        
    public function getStatement()
    {        
        $answer = 'SELECT ';
        $answer .= (is_array($this->columns)&&count($this->columns)>0) ? $this->getFormattedColumns($this->columns) : $this->columns;
        $answer .= ' FROM ' . $this->getFormattedTables($this->tables);
                
        if(!empty($this->where)){
            $answer .= ' WHERE ' . $this->where;
        }
        $answer .= $this->addendum;
            
        return $answer;
    } //end getSelect()
        
    function getFormattedColumns($array = array()){
        $answer = '';
        if(isset($array)&&!empty($array)){
                
            foreach($array as $temp){
                if(empty($answer))
                    $answer .= $temp;
                else
                    $answer .= ',' . $temp;
            }
                    
        }
        return $answer;
    }
        
    function getFormattedTables($array = array()){
        $answer ='';
        if(!empty($array)){
            $previous_table = '';
            foreach($array as $column => $table){
                if(empty($answer)){
                    $answer .= $table;
                    $previous_table = $table;
                    continue;
                }

                $answer .= ' LEFT JOIN ' . $table . ' ON ' . $previous_table . '.' . $column
                        .' = '. $table . '.' . $column;
                $previous_table = $table;
            }
        }
        return $answer;
    }

}
        
class UpdateStatement extends Statement
{
    public function getStatement()
    {
        $answer = 'UPDATE SET ' . 
            $this->getFormattedColumns($this->columns) . 
            ' FROM ' . 
            $this->getFormattedTables($this->tables);
                
        if(!empty($this->where))
            $answer .= ' WHERE ' . $this->where . $this->addendum;
                
                
        return $answer;
    } //end getStatement()
    function getFormattedColumns($array = array())
    {
        $answer = '';
        if(!empty($array)){
            foreach($array as $key => $value){
                if(!empty($answer))
                    $answer .= ' , ';    
                $answer .= key($value).'.'.$value[key($value)] . ' = ' . $key;
            }
                    
        }
        return $answer;
    }
                
    
}
    
class DeleteStatement extends Statement
{
        
    public function getStatement()
    {
        if(!empty($this->where)&&!empty($this->Where_Values)){
            $answer = 'DELETE ' . ' FROM ' . $this->getFormattedTables($this->tables);
                
        if(!empty($this->where))
            $answer .= ' WHERE ' . $this->where . $this->addendum;
        }
            
        return $answer;
    } //end getStatement()
}
    
class InsertStatement extends Statement
{
        
    public function getStatement()
    {
        if(!empty($this->addendum)){
            $answer = 'INSERT INTO ' . 
                $this->getFormattedTables($this->tables) . 
                ' (' . $this->getFormattedColumns($this->columns) . 
                ') VALUES ' . $this->addendum ;

        }
            
        return $answer;
    } //end getStatement()
        
}

class Wares extends InputOutput
{
    private $output;
    private $statement;
        
    function __construct(InputOutputArgvBuilder $argv, Query_Handler $PDOOutput,
            QHFetchArgBuilder $fetch = NULL, $statement = 'SQLStatement')
    {
        $this->statement = is_string($statement) ? $statement : 'SQLStatement';
        parent::__construct($argv, $PDOOutput);
    }

    public function getRowMax()
    {
        return $this->row_max;
    }
    
    public function getRowCurrent()
    {
        return $this->row_current;
    }
}
    
class AdminSelect extends InputOutput
{

    private $row_max = 0;
    private $row_increment = __ROW_INCREMENT__;
    private $row_current = 0;
    private $statement;

    public function __construct(InputOutputArgvBuilder $argv, Query_Handler $qh,
            QHFetchArgBuilder $fetch = NULL, $statement = 'SQLStatement')
    {

        $this->statement = $statement;
        parent::__construct($argv, $qh, $fetch);
    }
    
    public function getRowMax()
    {
        return $this->row_max;
    }
    
    public function getRowCurrent()
    {
        return $this->row_current;
    }
}
    
class RowCount extends InputOutput
{

    function __construct(InputOutputArgvBuilder $argv, Query_Handler $PDOOutput,
            QHFetchArgBuilder $fetch = NULL)
    {
        $temp_array = $argv->getColumns();
        $argv->setColumns('COUNT(' . key($temp_array), $temp_array[key($temp_array)][0] . ')');
        parent::__construct($argv, $PDOOutput, $fetch);
    }
        
    public function get($string_arg)
    {
        $raw = parent::get($string_arg);
        error_log('RowCount get $raw: ' . print_r($raw, true));
        if(is_array($raw))
            return $raw[0][key($raw[0])];
        else
            return $raw;
    }

}

class GetDescription extends InputOutput
{
    public function __construct(InputOutputArgvBuilder $argv, Query_Handler $PDOOutput,
            QHFetchArgBuilder $fetch = NULL)
    {
        parent::__construct($argv, $PDOOutput, $fetch);
    }
}

class GetAdmin
{
    private $variable = 'V|||A|||R';
    private $select;
    private $primary;
    private $admin;
    private $read_only;
    private $has_original;
    private $input_output;
    private $statement_name;
        
    public function __construct(InputOutput $admin_sql, array $get, $statement_name = 'SQLStatement')
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
    
    private function initialize(InputOutput $admin_sql, array $get)
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

        $select_multiple = $last_table[1]===key($column)//&&$last_table[0]===$column[key($column)][0]
                ? true : false;
        $select = array('select_multiple' => $select_multiple);
        if($select_multiple){        
            error_log('GetAdmin->getSelectMenuValues tables: ' . print_r($tables,true)
                        . ' and last_table: ' . print_r($last_table,true));
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
    public function __construct()
    {
        
    }
    
    public function __clone()
    {
        $answer = new InputOutputArgvBuilder();
        $answer->columns = Utilities::arrayCopy($this->columns);
        $answer->tables = Utilities::arrayCopy($this->tables);
        $answer->where = $this->where;
        $answer->where_values = Utilities::arrayCopy($this->where_values);
        $answer->addendum = $this->addendum;
        $answer->put_values = Utilities::arrayCopy($this->put_values);
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
            throw new InvalidArgumentException('setInitialTable function first argument'
                    . ' text string only. Input was: '.$first_table);
        }
        return $this;
    }
    
    public function setInnerJoinTables($table, $column)
    {
        if(is_string($table)&&is_string($column)){
            $this->tables = $this->tables + array($column => $table);
        } else {
            throw new InvalidArgumentException('setInnerJoinTables function arguments'
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
                throw new InvalidArgumentException('setInnerJoinTables function arguments'
                   . ' (table , column) pairs only. Input was: '. print_r(var_dump(func_get_args()), true));
            }
        }
        return $this;
    }
    
    public function setColumns($table, $column)
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
    
    public function setWhere($where_clause)
    {
        if(is_string($where_clause))
            $this->where = $where_clause;
        else
            throw new InvalidArgumentException(
                'setWhere function first argument must be of type text string. First argument: '
                . func_get_arg(0));
        return $this;
    }
    
    public function setWhereValues(array $where_values)
    {
        for($i=count($where_values)-1; $i>-1;$i--){
            if(!is_string($where_values[$i]))
                throw new InvalidArgumentException(
                    'setWhereValues function arguments must be of type text string in format ":value".'
                    . ' Arguments: '. print_r(var_dump($where_values),true));
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
            throw new InvalidArgumentException(
                    'setAddendum function argument must be of type text string.'
                    . ' Argument: '. print_r($addendum,true));
        if(strpos($addendum, 'LIMIT')===FALSE){
            $addendum .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        }
        $this->addendum = $addendum;
        return $this;
    }
    
    public function setPutValues($put_values)
    {
        $args = func_get_args();
        if($this->ensureArrayValueString($args))
            $this->put_values = $args;
        else
            throw new InvalidArgumentException(
                    'setPutValues function arguments must be of type text string.'
                    . ' Arguments: '. print_r(var_dump($args),true));
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
        $answer = true;
        foreach($arr as $entry){
            $answer = is_string($entry);
            if(!$answer)
                break;
        }
        return $answer;
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
            throw new InvalidArgumentException(
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

class Security
{
    public static function generateHash();
    
    public static function encrypt($str);
}

interface ModelCategory
{
    abstract function getFetchArg();
    abstract function getInputOutputArguments(array $raw_arguments);
}

class GetGallery implements ModelCategory
{
    public function __construct()
    {

    }
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
    {
        $row_increment = __ROW_INCREMENT__;
        $row_current = 0;
        $row_increment = isset($raw_arguments['row_increment']) ? $raw_arguments['row_increment']
                : __ROW_INCREMENT__;
        $row_current = isset($raw_arguments['row_current']) ? $raw_arguments['row_current'] : 0;
        $where_statement = new MySQLWhereStatementArgBuilder(
                $raw_arguments['where']['where_targets'], $raw_arguments['where']['operators']);
        $construct_arguments = new InputOutputArgvBuilder();
        $construct_arguments['addendum'] .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        $construct_arguments = $construct_arguments->setInitialTable(
                'gallery'
                )->setInnerJoinTables('category_id', 'image_id')->setInnerJoinTables(
                        'categories', 'category_id')->setColumns(
                                'gallery', 'file_name', 'name', 'medium', 'year', 'height', 'width'
                                )->setColumns('categories', 'category')->setAddendum(
                                        ' ORDER BY year DESC, name ASC', $row_current, $row_increment
                                        )->setWhere($where_statement())->setWhereValues(
                                                $raw_arguments['where']['where_values']
        );
        return $construct_arguments;
    }
}

class AdminGetValues implements ModelCategory
{
    public function __construct()
    {

    }
    
    function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    function getInputOutputArguments(array $raw_arguments)
    {
        $incumbent_table; $where_targets; $where_operators; $where_values;
        $row_increment = isset($raw_arguments['row_increment']) ? $raw_arguments['row_increment']
                : __ROW_INCREMENT__;
        $row_current = isset($raw_arguments['row_current']) ? $raw_arguments['row_current'] : 0;
        $construct_arguments = new InputOutputArgvBuilder();
        $tables = isset($raw_arguments['tables'])&&is_string($raw_arguments['tables'])
                ? json_decode($raw_arguments['tables'], TRUE) : isset($raw_arguments['tables'])
                ? $raw_arguments['tables'] : array();
        $columns = isset($raw_arguments['columns'])&&is_string($raw_arguments['columns'])
                ? json_decode($raw_arguments['tables'], TRUE) : isset($raw_arguments['columns'])
                ? $raw_arguments['columns'] : array();
        $addendum = isset($raw_arguments['addendum'])&&is_string($raw_arguments['addendum'])
                ? $raw_arguments['addendum'] : '';
        if(isset($raw_arguments['where'])){
            $where_targets = isset($raw_arguments['where']['where_targets']) ? $raw_arguments['where']['where_targets']
                    : $where_targets;
            $where_values = isset($raw_arguments['where']['where_values']) ? $raw_arguments['where']['where_values']
                    : $where_values;
            $where_operators = isset($raw_arguments['where']['operators']) ? $raw_arguments['where']['operators']
                    : $where_operators;
        }
        if(isset($tables[0])){
            $construct_arguments = $construct_arguments->setInitialTable($tables[0]);
            unset($tables[0]);
        }
        foreach($tables as $key => $value){
            $construct_arguments->setInnerJoinTables($key, $value);
        }
        foreach($columns as $key => $value){
            for($i=0, $max=count($value); $i<$max; $construct_arguments->setColumns($key, $value[$i]), $i++){}
        }
        $construct_arguments->setAddendum($addendum, $row_current, $row_increment);
        if(isset($where_targets)&&isset($where_values)&&isset($where_operators)){
            $where_statement = new MySQLWhereStatementArgBuilder(
                $where_targets, $where_operators);
            $construct_arguments = $construct_arguments->setWhere(
                    $where_statement()
                    )->setWhereValues($where_values);
            error_log('AdminGetValues has Where clause. $where_values: ' . print_r($where_values, TRUE));
            $new_rest_assoc = array_combine($raw_arguments['where']['where_targets'],
                $raw_arguments['where']['where_values']);
        }
        return $construct_arguments;
    }
}

class FieldSet implements ModelCategory
{
    public function __construct()
    {

    }
    
    function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    function getInputOutputArguments(array $raw_arguments)
    {
        $construct_arguments = new FieldSetArgvBuilder();
        if(isset($raw_arguments['field_set'])){
            list($tbl, $clmn) = explode('.', $raw_arguments['field_set']);
        } else if(isset($raw_arguments['columns'])){
            $tbl = key($raw_arguments['columns']);
            $clmn = $raw_arguments['columns'][key($raw_arguments['columns'])][0];
        }

        $where_statement = '1=1';
        $construct_arguments = new FieldSetArgvBuilder();
        $construct_arguments = $construct_arguments->setInitialTable(
                $tbl)->setColumns($tbl, $clmn)->setWhere($where_statement)->setAddendum(
                " ORDER BY {$tbl}.{$clmn}");
        return $construct_arguments;
    }
}

class AdminDescription implements ModelCategory
{
    public function __construct()
    {

    }
    
    function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    function getInputOutputArguments(array $raw_arguments)
    {
        $construct_arguments = new InputOutputArgvBuilder();
        $where_values = array(
            'gallery.image_id',
            'gallery.file_name',
            'gallery.splash_page',
            'gallery.name',
            'gallery.year',
            'gallery.medium',
            'gallery.height',
            'gallery.width',
            'categories.category_id',
            'categories.category'
        );
        $where_targets = array_fill(0, count($where_values), 'admin.field_name');
        $where_operators = array_fill(0, count($where_values), '=');
        $where_statement = new MySQLWhereStatementArgBuilder(
                $where_targets, $where_operators, 'OR ');
        $construct_arguments = $construct_arguments->setInitialTable(
                'admin')->setColumns(
                        'admin', 'field_name', 'data_unsigned', 'data_size', 'data_type', 'data_primary','data_select'
                        )->setWhere($where_statement())->setWhereValues($where_values);
        return $construct_arguments;
    }
}

class GetRowMax implements ModelCategory
{
    public function __construct()
    {

    }
    
    function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    function getInputOutputArguments(array $raw_arguments)
    {
        $row_increment = isset($raw_arguments['row_increment']) ? $raw_arguments['row_increment']
            : __ROW_INCREMENT__;
        $row_current = isset($raw_arguments['row_current']) ? $raw_arguments['row_current'] : 0;

        $construct_arguments = new InputOutputArgvBuilder();
        $construct_arguments = $construct_arguments->setInitialTable(
                'gallery')->setInnerJoinTables('category_id', 'image_id')->setInnerJoinTables(
                        'categories', 'category_id')->setColumns(
                                ' COUNT(gallery', 'image_id)'
                                        )->setAddendum(' GROUP BY gallery.image_id ');
        if(isset($raw_arguments['where']['where_targets'])&&isset($raw_arguments['where']['operators'])
                &&isset($raw_arguments['where']['operators'])){
            $where_statement = new MySQLWhereStatementArgBuilder(
                $raw_arguments['where']['where_targets'], $raw_arguments['where']['operators']);
            $construct_arguments->setWhere($where_statement())->setWhereValues(
                                                $raw_arguments['where']['where_values']);
        }
        return $construct_arguments;
    }
}

class ImageList implements ModelCategory
{
    public function __construct()
    {

    }
    
    function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    function getInputOutputArguments(array $raw_arguments)
    {
        $row_increment = PHP_INT_MAX;
        $row_current = 0;
        $construct_arguments = new InputOutputArgvBuilder();
        $construct_arguments = $construct_arguments->setInitialTable(
                'gallery')->setColumns(
                'gallery', 'file_name')->setColumns(
                'categories', 'category_id', 'category')->setAddendum(
                ' GROUP BY gallery.file_name, ORDER BY gallery.file_name ', $row_current, $row_increment);
        return $construct_arguments;
    }
}
