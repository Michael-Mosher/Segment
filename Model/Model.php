<?php
require_once('/home/luvmachi/public_html/christinamarvel.com/test/utilities/variables.php');
require_once('../cma.php');
require_once(__ROOT__ . '/utilities/templates.php');

class Model
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
            throw new InvalidArgumentException('Model->getCompartmentObj expects first argument'
                    . ' to be of type text string. Provided: ' . print_r($compartment, TRUE));
        
        $payload;
        try{
            if(strpos($compartment, '_')>-1){
                $compartment = explode('_', $compartment);
                array_walk($class_name, 'Utilities::callableUCFirst');
                $compartment = implode('', $compartment);
            }
            $compartment = ucfirst($compartment);
            $payload = new $compartment();
        } catch (Exception $ex) {
            error_log($ex);
        }
        if(!isset($payload)||!is_a($payload, 'ModelCompartment'))
            $payload = new GetGallery();
        return $payload;
    }
    
    private function getDBQuery($db_type)
    {
        if(!is_string($db_type))
            throw new InvalidArgumentException('Model->getDBQuery expects first argument'
                    . ' to be of type text string. Provided: ' . print_r($db_type, TRUE));
        
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

}
