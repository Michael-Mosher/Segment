<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


/*
class Model implements ModelCaller, Factory
{

    public function __construct()
    {
        
    }

    // receive request
    public function execute(Rest $rest)
    {
        $answer = '{}';
        $raw_arguments = $rest->getValue('arguments');
        
        // pass request to appropriate compartment for given category
        $compartment = $this->getCompartmentObj($raw_arguments['x']);
        
        // format query result based on category
        $fetch_arg = $compartment->getFetchArg();
        
        // generate query statement based on database type
        $construct_arguments = $compartment->getInputOutputArguments($raw_arguments);
        $IO = $compartment->getInputOutput(
                $construct_arguments, $this->getDBQuery(__RDB_SYSTEM__), $fetch_arg
                );

        // query database
        $answer = $IO->get(__DB_STATEMENT_TYPE__);

        // return payload
        header('Content-type: application/json');
        echo json_encode($answer);
        
    }
    
    private function getCompartmentObj($compartment)
    {
        if(!is_string($compartment))
            throw new \InvalidArgumentException('Model->getCompartmentObj expects first argument'
                    . ' to be of type text string. Provided: ' . print_r($compartment, TRUE));
        
        $payload;
        try{
            if(strpos($compartment, '_')>-1){
                $compartment = explode('_', $compartment);
                array_walk($class_name, '\Segment\utilities\Utilities::callableUCFirst');
                $compartment = implode('', $compartment);
            }
            $compartment = ucfirst($compartment);
            $payload = new $compartment();
        } catch (Exception $ex) {
            error_log($ex->getMessage());
        }
        if(!isset($payload)||!is_a($payload, 'ModelCompartment'))
            $payload = new GetGallery();
        return $payload;
    }
    
    private function getDBQuery($db_type)
    {
        if(!is_string($db_type))
            throw new \InvalidArgumentException('Model->getDBQuery expects first argument'
                    . ' to be of type text string. Provided: ' . print_r($db_type, TRUE));
        
                switch ($db_type){
                    case 'MySql':
                        return MySqlQH::getQueryHandler();
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

    public function getInstance($output, $input) {
        
    }

    public function initializeDbStatement(\ModelCall $call) {
        
    }

    public function makeRecord(array $db_out) {
        
    }

    public function query(\Segment\Model\Statement $statement) {
        
    }

}
*/

/*
class Security
{
    public static function generateHash()
    {
        return crypt(__PROJECT_NAME__ . __DOMAIN__, date('l jS \of F Y h:i:s A'));
    }
    
    public static function encrypt($str)
    {
        if(!is_string($str))
            throw new \InvalidArgumentException('Security::encrypt requires first argument to be text string.'
                    . ' Provided: ' . print_r($str, TRUE));
        return crypt($str, '$1$' . \Segment\utilities\Utilities::getRandomString(8) . '$');
    }
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
        $where_statement = new MySqlWhereStatementArgBuilder(
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
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
    {
        $incumbent_table; $where_targets; $where_operators; $where_values;
        $row_increment = isset($raw_arguments['row_increment']) ? $raw_arguments['row_increment']
                : __ROW_INCREMENT__;
        $row_current = isset($raw_arguments['row_current']) ? $raw_arguments['row_current'] : 0;
        $construct_arguments = new InputOutputArgvBuilder();
        //$construct_arguments['addendum'] .= ' LIMIT ' . $row_current . ', ' . $row_increment;
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
            $where_statement = new MySqlWhereStatementArgBuilder(
                $where_targets, $where_operators);
            $construct_arguments = $construct_arguments->setWhere(
                    $where_statement()
                    )->setWhereValues($where_values);
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
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
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
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
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
        $where_statement = new MySqlWhereStatementArgBuilder(
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
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
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
            $where_statement = new MySqlWhereStatementArgBuilder(
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
    
    public function getFetchArg()
    {
        return new AssocQHFetchArgBuilder();
    }
    
    public function getInputOutputArguments(array $raw_arguments)
    {
        $row_increment = PHP_INT_MAX;
        $row_current = 0;
        $construct_arguments = new InputOutputArgvBuilder();
        //$construct_arguments['addendum'] .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        $construct_arguments = $construct_arguments->setInitialTable(
                'gallery')->setColumns(
                'gallery', 'file_name')->setColumns(
                'categories', 'category_id', 'category')->setAddendum(
                ' GROUP BY gallery.file_name, ORDER BY gallery.file_name ', $row_current, $row_increment);
        return $construct_arguments;
    }
}*/
