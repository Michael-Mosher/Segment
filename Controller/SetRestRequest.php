<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

trait SetRestRequest
{
    private $set_fields = [];
    private $set_tuples = [];
    private $set_count = 0;
    private $set_index = NULL;
    
    /**
     * Add tuple of field, new value, and old value to object.
     * @param string $field Name of field to be set
     * @param mixed $value_new Represents new value for the field of the record.
     * @param string $operator The type of DB function
     * @return boolean
     * @throws \Exception
     */
    protected function addSetCell(string $field, $value_new, string $operator)
    {
        $result = FALSE;
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->set_tuples[] = new production\SetParameter($field,$value_new,$operator);
        if(!isset($this->set_fields[$operator]))
            $this->set_fields[$operator] = [];
        $this->set_fields[$operator][$field] = $value_new;
        $this->set_index = is_null($this->set_index) ? 0 : $this->set_index;
        $this->set_count++;
        $result = TRUE;
        return $result;
    }
    
    protected function removeSetCell($index)
    {
        if(isset($this->set_fields[$index])){
            unset($this->set_fields[$index]);
            unset($this->set_tuples[$index]);
            $this->set_count--;
        }
    }
    
    /**
     * Returns the SetParameter the index is currently pointing to, or FALSE if index not valid.
     * @return SetParameter|FALSE
     */
    protected function getSetTuple()
    {
        if(isset($this->set_tuples[$this->set_index]))
            return $this->set_tuples[$this->set_index];
        else
            return FALSE;
    }
    
    /**
     * Returns the internal index to beginning.
     */
    protected function setRewind()
    {
        $this->set_index = 0;
    }
    
    /**
     * Increments index and returns whether it is a valid position.
     * @return bool If new index is valid.
     */
    protected function setNext()
    {
        return ++$this->set_index<$this->set_count;
    }
    
    /**
     * 
     * @param type $index
     * @return int
     */
    public function getSetCount($index = NULL)
    {
        if(is_null($index))
            return $this->set_count;
        else if(isset($this->set_fields[$index]))
            return count($this->set_fields[$index]);
    }
    
    
    /**
     * Checks presence of named field in contained SearchParameter objects
     * @param string $field The name of a field
     * @return boolean TRUE if there is at least one match, FALSE otherwise.
     */
    public function hasField($field)
    {
        $answer = FALSE;
        foreach($this->set_fields as $operator => $array){
            if(array_search($field, array_keys($array))!==FALSE){
                $answer = TRUE;
                break;
            }
        }
        return $answer;
    }
    
    /**
     * Returns an array of SetParameter that correspond to the provided field
     * @param string $field The name of a field
     * @return array<SplFixedArray> Those Parameters that have $field for their field values
     */
    public function getFromField($field)
    {
        $answer = array();
        for($i=count($this->set_tuples)-1;$i>-1;$i--){
            if(strtolower(trim($this->set_tuples[$i]->getField()))=== strtolower(trim($field))){
                $answer[] = clone $this->set_tuples[$i];
            }
        }
        return $answer;
    }
}