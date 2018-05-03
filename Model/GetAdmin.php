<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

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
        /*if($get['admin']=='update'||$get['admin']=='delete'){
            self::getSelectMenuValues($admin_sql);
            self::prepareColumns(GetAdmin::getMetaData($admin_sql));
        }*/
    }
        
    /*public function getGetAdmin(\Segment\Model\InputOutput $admin_sql, $get = array())
    {
        if(empty(self::$admin))
            self::$admin = !empty($get)&&isset($get['admin']) ? $get['admin'] : '';
        $this->read_only = self::$admin=='delete' ? true : false;
        $this->has_original = self::$admin=='delete'||self::$admin=='update' ? true : false;
        if($get['admin']=='update'||$get['admin']=='delete'){
            self::getSelectMenuValues($admin_sql);
            self::prepareColumns(GetAdmin::getMetaData($admin_sql));
        }
    
        if(empty($this->instance))
            $this->instance = new GetAdmin();
                
        return $this->instance;
    
    }*/
    
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
        $select_multiple = $last_table[1]===key($column)//&&$last_table[0]===$column[key($column)][0]
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
            /*$temp = \Segment\utilities\Utilities::arrayCopy($select);
            $qh = \Segment\Model\QueryHandler::getQueryHandler();
            foreach($temp['select'] as $key => $value){
                if(isset($value['select_multiple'])&&$value['select_multiple']===true){
                    $cparts = explode('.', $value['columns']);
                    $payload = new \Segment\Model\InputOutput(array(
                            'columns' => $value['columns'],
                            'tables' => [0 => $cparts[0]],
                            'where' => '',
                            'where_values' => null,
                            'addendum' => ' GROUP BY ' . $key . ' ORDER BY ' . $key
                        ));
                    $select['select'][$key] = $select['select'][$key] + array(
                        'values' => $qh->query($statement->initialize($payload))
                    );
                    unset($temp['select'][$key]);
//                    for($i = 0, $max = count($cparts); $i < $max; ++$i){
//                        unset($cparts[$i]);
//                    }
                }
            }*/
        }
        return $select;
    }
        
    /*public function getDataTables(InputOutput $admin_sql)
    {
        if(empty(self::$dtables))
            self::getSelectMenuValues($admin_sql);
        return self::$dtables;
    }*/
        
    public function getMetaData(array $descriptions)
    {
        /*
        if(empty(self::$dtables))
            self::getSelectMenuValues($admin_sql);

        $description = array();
        $qh = \Segment\Model\QueryHandler::getQueryHandler();
        $data = array();
        foreach(self::$dtables as $dtable){
            $description[] = $qh->describeTable($dtable);
            unset(self::$dtables[$dtable]);
        }
        */
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