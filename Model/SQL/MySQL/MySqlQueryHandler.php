<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlQueryHandler implements \Segment\Model\QueryHandler
{
    private static $instance;
    private $pdo = null;
    /*private $asf = null;*/
    private $sql_stmt = '';

    private function __construct(){}

    public static function getQueryHandler()
    {
        if (self::$instance == NULL) {
            $temp_class_n = __CLASS__;
            self::$instance = new  $temp_class_n();
            self::$instance->pdo = self::startConnection();
            //self::query($this->pdo, $command);
        }
        return self::$instance;
    }
    
    private static function startConnection()
    {
        $arg = 'mysql:dbname='. __DB_USER__ . ';host=' .
                __HOST__ . ';charset=utf8';
        try {
            $link = new \PDO($arg, __DB_USER__, __DB_PASS__);
            $link->exec('USE ' . __DB_NAME__);
        } catch (\PDOException $e) {
            error_log(__METHOD__ . ' ' . $e->getMessage());
            exit();
        }
        return $link;
    }
    
    /**
     * Returns array of DbDescription objects providing description of each column in the table requested
     * @param string $table
     * @return array<\Segment\Controller\production\TolDbDescripFetch>
     */
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
            $temp = $PDOS->fetchAll();
            $answer = array();
            end($temp);
            for($i = key($temp);$i>-1;$i--){
                $row = new \Segment\Controller\production\TOL\TolDbDescripFetch($table);
                foreach($temp[$i] as $key => $value){
                    $row->addend($key, [$value]);
                }
                $answer[] = $row;
            }
            
            return $answer;
        } catch(\PDOException $e) {
            error_log('Error executing describeTable: ' . print_r($e, true));
            echo "Database Connection Failed\n";
            exit();
        }
    }
    
    /**
     * 
     * @param \Segment\Model\AbstractStatement $query_statement_string
     * @param array<mixed> $where_values
     * @param \Segment\Model\QHFetchArgBuilder $fetch
     * @param string $call_type_id
     * @return type
     */
    public function query(\Segment\Model\AbstractStatement $query_statement_string, array $where_values, \Segment\Model\QHFetchArgBuilder $fetch)
    {
        try{
            //$singleton = self::getQueryHandler();
            //$query_statement_string .= ';';
            $call_type_id = func_get_arg(3);
            $set_attribute_status = $this->pdo->setAttribute(\PDO::ATTR_FETCH_TABLE_NAMES, \PDO::CASE_NATURAL);
            $PDOS = $this->pdo->prepare($query_statement_string->getStatement());
            if(\Segment\utilities\Utilities::checkArrayEmpty($where_values)){
                $statement_execute_success = $PDOS->execute();
            } else {
                $this->processWhereParams($PDOS, ...$where_values);
                $statement_execute_success = $PDOS->execute($where_values);
            }
            if(!$statement_execute_success)
                error_log('Error executing PDOStatement');
            
            $answer = $fetch->fetch($PDOS, $call_type_id);
            //$this->MySQLCleanUp($answer);

            return $answer;
        } catch(\PDOException $e) {
            error_log('Error executing query: ' . print_r($e->getMessage(), true));
            echo "Database Connection Failed\n";
            exit();
        } //final    { $PDOS->closeCursor(); }
        //return $collection;
    }
    
    private function processWhereParams(\PDOStatement &$statement, 
            \Segment\Model\production\SingleValue ...$where_params)
    {
        $modifier = 1;
        foreach($where_params as $i => $param){
            if(is_a($param, "\Segment\Model\production\StringValue")){
                $statement->bindParam($i+$modifier, \strval($param), \PDO::PARAM_STR);
            } else if(is_a($param, "\Segment\Model\production\BooleanValue")){
                $statement->bindParam($i+$modifier, (bool)\strval($param), \PDO::PARAM_BOOL);
            } else if(is_a($param, "\Segment\Model\production\IntegerValue")
                    || is_a($param, "\Segment\Model\production\FloatValue")){
                $statement->bindParam($i+$modifier, (float)\strval($param), \PDO::PARAM_INT);
            } else if(is_a($param, "\Segment\Model\SearchValues")){
                $statement->bindParam($i+$modifier, \strval($param), \PDO::PARAM_NULL);
            } else
                $modifier--;
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