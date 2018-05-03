<?php
namespace Segment\Model\production\SQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


class SqlStatement extends \Segment\Model\AbstractStatement
{
    /**
     * Constructor
     * @param \Segment\Model\InputOutput $collection The StatementBuilder source
     * @param string $type \Segment\Model\AbstractStatement constant
     */
    public function __construct(\Segment\Model\InputOutput $collection, $type = '')
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->input_output = $collection;
        switch ($type)
        {
            case \Segment\Model\AbstractStatement::DELETE:
                $temp_suffix = strlen($this->input_output->getWhereClause())>0 ? ' WHERE '
                    . $this->input_output->getWhereClause() : '';
                if(strlen($temp_suffix)>0){
                    $temp_suffix .= $this->input_output->getAddendumClause();
                    $temp_prefix = 'DELETE ' . $this->input_output->getDeleteClause();
                    $temp_prefix .= ' FROM ' . $this->getFromClause() . $temp_suffix;
                } else 
                    $temp_prefix = '';
                $this->values[0] = "$temp_prefix;";
                break;
            case \Segment\Model\AbstractStatement::POST:
                $this->values[0] = 'INSERT ' . $this->input_output->getSetClause() . ';';
                break;
            case \Segment\Model\AbstractStatement::PUT:
                $temp_suffix = strlen($this->input_output->getWhereClause())>0 ?
                        ' WHERE ' . $this->input_output->getWhereClause() : '';
                if(strlen($temp_suffix)>0){
                    $temp_suffix = ' SET '. $this->input_output->getSetClause() . $temp_suffix;
                    $temp_suffix = 'UPDATE ' . $this->input_output->getFromClause() . $temp_suffix;
                    $temp_suffix .= $this->input_output->getAddendumClause();
                }
                $this->values[] = "{$temp_suffix};";
                break;
            case \Segment\Model\AbstractStatement::GET:
            default:
                $temp_suffix = strlen($this->input_output->getWhereClause())>0 ? ' WHERE '
                        . $this->input_output->getWhereClause() : '';
                $temp_prefix = 'SELECT ' . $this->input_output->getReturnClause();
                
                // getTables, pass as argument to new *SqlFromClauseBuilder::getClause
                $temp_tables = [];
                foreach ($collection->getTables() as $key => $value) {
                    if($key===0){
                        $temp_tables[0] = new SqlTable($value, new \Segment\Model\production\StringValue(__DB_NAME__));
                    } else {
                        $temp_tables[$key] = new SqlTable($value, new \Segment\Model\production\StringValue(__DB_NAME__));
                    }
                }
                $exper_from_bldr = new MySQL\MySqlFromClauseBuilder();
                $exper_output = $exper_from_bldr->getClause(...$temp_tables);
                //$collection->getFromClause(['experiment2']);
                
                $temp_stmt = strlen($exper_output)>0 ? $temp_prefix .
                            ' FROM ' . $exper_output . $temp_suffix : $temp_prefix . $temp_suffix;
                
                $this->values[0] = $temp_stmt . $this->input_output->getAddendumClause() . ";";
                break;
        }
    }
    
    
 /* use getFormattedTables and other methods to generate
                                        *  statement for QueryHandler */
    
    /**
     * Returns a SQL formatted string for referencing tables
     * @return string
     */
    private function getFormattedTables()
    {
        $answer ='';
        $array = $this->input_output->getTables();
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
    
    /**
     * Returns a SQL formatted string for referencing columns of tables
     * @return string
     */
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
    
    /**
     * Returns a SQL formatted string for PUT statement columns of tables
     * @return string
     */
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

    /**
     * Returns a SQL formatted GET query string
     * @param \Segment\Model\InputOutput $collection
     * @return string
     */
    public function getGetStatement(\Segment\Model\InputOutput $collection = NULL)
    {
        $this->input_output = \is_null($collection) ? $this->input_output : clone $collection;
        $answer = 'SELECT ';
        $answer .= $this->getFormattedColumns();
        /*if($this->isMultiToMulti()){
            $comma_split = explode (',', $answer);
            $comma_split[count($comma_split)-1] = ' DISTINCT ' . $comma_split[count($comma_split)-1];
            $answer = implode(',', $comma_split);
        }*/
        $answer .= ' FROM ' . $this->getFormattedTables();
        $answer .= empty($this->input_output->getWhere()) ? ''
                    : ' WHERE ' . $this->input_output->getWhere();
        $answer .= $this->input_output->getAddendum();
        error_log(__METHOD__ . " the statement: $answer");
        return $answer;
    } //end getGetStatement()

    /**
     * Returns a SQL formatted PUT query string
     * @param \Segment\Model\InputOutput $collection
     * @return string
     */
    public function getPutStatement(\Segment\Model\InputOutput $collection = NULL)
    {
        $this->input_output = is_null($collection) ? $this->input_output : clone $collection;
        $answer = 'UPDATE SET '. $this->getPutFormattedColumns() . ' FROM '
                . $this->getFormattedTables();
        $answer .= empty($this->input_output->getWhere()) ? ''
                : 'WHERE ' . $this->input_output->getWhere() . $this->input_output->getAddendum();

        return $answer;
    } //end getStatement()

    /**
     * Returns a SQL formatted POST query string
     * @param \Segment\Model\InputOutput $collection
     * @return string
     */
    public function getPostStatement(\Segment\Model\InputOutput $collection = NULL)
    {
        $this->input_output = is_null($collection) ? $this->input_output : clone $collection;
        $answer = empty($this->input_output->getAddendum()) ? ''
                : 'INSERT INTO ' . $this->getFormattedTables() . ' ('
                . $this->getFormattedColumns() . ') VALUES ' . $this->input_output->getAddendum();
        return $answer;
    } //end getStatement()
    
    /**
     * Returns a SQL formatted DELETE query string
     * @param \Segment\Model\InputOutput $collection
     * @return string
     */
    public function getDeleteStatement(\Segment\Model\InputOutput $collection = NULL)
    {
        $this->input_output = is_null($collection) ? $this->input_output : clone $collection;
        $answer = (empty($this->input_output->getWhere())||
                \Segment\utilities\Utilities::checkArrayEmpty($this->input_output->getWhereValues()))
                ? '' : 'DELETE FROM ' . $this->getFormattedTables($this->tables)
                . ' WHERE ' . $this->input_output->getWhere() . $this->input_output->getAddendum();
        return $answer;
    } //end getStatement()
    
    
    /**
     * Determines if initialized tables are in many-to-many configuration.
     * @return boolean
     */
    private function isMultiToMulti()
    {
        return count($this->input_output->getTables())>2;
    }
}