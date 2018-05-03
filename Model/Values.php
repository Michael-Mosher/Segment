<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

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