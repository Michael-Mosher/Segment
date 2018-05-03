<?php

namespace Segment\Controller\Testing;

trait ReturnRestRequest
{
    /**
     * @var array assoc. array key: search type, value:
     *         assoc. array key: search field, value: array of values sought
     */
    private $return_fields = [];
    /**
     * 
     * @var array<\Segment\Controller\RestReturn> 
     */
    private $return_tuples = [];
    private $return_count = 0;
    private $return_index = NULL;
    private $return_queue;
    
    /**
     * Add elements from the REST request
     * @param string $field
     * @param mixed $value
     * @param string $type RestSearch constant. Default RestSearch::EQUAL
     * @param string $conjunctive The type of conjuction for handling of multiple search
     *         criteria. The permitted values are AND, OR, and NOCONJ. Default NOCONJ.
     * @param bool $exclusive_range Whether a range's endpoints should be considered
     *         exclusively, TRUE, or inclusively, FALSE. Default FALSE.
     * @return boolean TRUE if addition successful, FALSE otherwise.
     */
    protected function addReturnCell(string $field, $value, string $type, string $alias): bool
    {
        $result = FALSE;
        $rest_return_reflection = new \ReflectionClass("\Segment\Controller\RestReturn");
        $const_list = $rest_return_reflection->getConstants();
        if(array_search($type, $const_list)!==FALSE&&$type!== \Segment\Controller\RestReturn::LIMIT
                &&$type!== \Segment\Controller\RestReturn::ORDER
                &&(!isset($this->return_fields[$type][$field])
                        ||$this->return_fields[$type][$field]!==$alias)
                ){
            if(!isset($this->return_fields[$type])){
                $this->return_fields[$type] = [];
            }
            $this->return_fields[$type][$field] = $alias;
            $options = [];
            $options[\Segment\Model\production\ModelCaller::ALIAS] = $alias;
            $this->tuple[] = new \Segment\Controller\production\ReturnParameter($field, $value, $type, $options);
            $this->return_index = is_null($this->return_index) ? 0 : $this->return_index;
            $this->return_count++;
            $result = TRUE;
        }
        return $result;
        
    }
    
    
    protected function addReturnOrderCell(string $field, int $direction, string $operator)
    {
        $result = FALSE;
        if($operator=== \Segment\Controller\RestReturn::ORDER && strlen($field)>0 && $direction<>0
                &&!isset($this->return_fields[$operator][$field])){
            $options = [];
            $options[\Segment\Model\production\ModelCaller::ORDERDIRECTION] = $direction;
            if(!isset($this->return_fields[$operator]))
                $this->return_fields[$operator] = [];
            $this->return_fields[$operator][$field] = $direction;
            $this->tuple[] = new \Segment\Controller\production\ReturnOrderParameter(
                    $field, NULL, $operator, $options
            );
            $this->return_index = is_null($this->return_index) ? 0 : $this->return_index;
            $this->return_count++;
            $result = TRUE;
        }
        return $result;
    }
    
    /**
     * Adds a ReturnLimitParameter to the list of tuples.
     * @param float $start_amt
     * @param bool $start_abs
     * @param float $length_amt
     * @param float $length_abs
     * @param string $operator
     */
    protected function addReturnLimitCell(float $start_amt, bool $start_abs, float $length_amt,
            float $length_abs, string $operator): bool
    {
        $result = FALSE;
        if($operator=== \Segment\Controller\RestReturn::LIMIT&&!isset($this->return_fields[$operator])){
            $options = [];
            $options[\Segment\Model\production\ModelCaller::LIMITSTARTNUM] = $start_amt;
            $options[\Segment\Model\production\ModelCaller::LIMITSTARTABS] = $start_abs;
            $options[\Segment\Model\production\ModelCaller::LIMITSTARTNUM] = $length_amt;
            $options[\Segment\Model\production\ModelCaller::LIMITAMTABS] = $length_abs;
            $this->return_fields[\Segment\Controller\RestReturn::LIMIT] = [];
            $this->return_fields[$operator][\Segment\Model\production\ModelCaller::LIMITSTARTNUM]
                    = $start_amt>0 ? $start_amt : 0;
            $this->return_fields[$operator][\Segment\Model\production\ModelCaller::LIMITSTARTABS]
                    = isset($start_abs)&&is_bool($start_abs) ? $start_abs : TRUE;
            $this->return_fields[$operator][\Segment\Model\production\ModelCaller::LIMITAMTNUM]
                    = $length_amt>0 ? $length_amt : 0;
            $this->return_fields[$operator][\Segment\Model\production\ModelCaller::LIMITAMTABS]
                    = isset($length_abs)&&is_bool($length_abs) ? $length_abs : TRUE;
            $this->return_count = 1;
            $this->return_tuples[] = new \Segment\Controller\production\ReturnLimitParameter(
                    "", NULL, $operator, $options
            );
            $this->return_index = is_null($this->return_index) ? 0 : $this->return_index;
            $this->return_count++;
            $result = TRUE;
        }
        return $result;
    }
    
    /**
     * Remove elements
     * @param string $field
     * @param mixed $value
     * @param string $type RestSearch constant
     */
    protected function removeReturnCell($field, $value, $type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $rest_return_reflection = new \ReflectionClass("\Segment\Controller\RestReturn");
        $const_list = $rest_return_reflection->getConstants();
        if(array_search($type, $const_list)){
            try {
                unset($this->return_fields[$type][$field][array_search($value, $this->return_fields[$type][$field])]);
            } catch (\Exception $ex) {
                error_log($ex->getMessage());
            }
        }
    }
    
    /**
     * Remove column, and all associated elements.
     * @param string $field
     * @param string $type Must be RestSearch constant
     */
    protected function unsetReturnColumn($field, $type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $rest_return_reflection = new \ReflectionClass("\Segment\Controller\RestReturn");
        $const_list = $rest_return_reflection->getConstants();
        if(array_search($type, $const_list)){
            try {
                unset($this->return_fields[$type][$field]);
            } catch (\Exception $ex) {
                error_log($ex->getMessage());
            }
        }
        
    }
    
    /**
     * Sets SearchParameter tuple index to beginning
     */
    protected function returnRewind()
    {
        $this->return_index = 0;
    }
    
    /**
     * Returns current search field (0), value (1), and type (2) in SearchParameter
     * @return SearchParameter|NULL
     * @throws \OutOfBoundsException|\Exception
     */
    protected function getReturnTuple()
    {
        $answer = NULL;
        try {
            $answer = $this->return_tuples[$this->return_index];
        } catch (\OutOfBoundsException $out){
            throw new \OutOfBoundsException(__METHOD__.
                    " out of bounds. Be kind, call ::searchRewind()");
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }  finally {
            return $answer;
        }
    }
    
    /**
     * Increments search index
     * @return boolean TRUE if there is another valid entry. FALSE if past end.
     */
    protected function returnNext()
    {
        $this->return_index++;
        $answer = $this->return_index>$this->return_count ? FALSE : TRUE;
        return $answer;
    }
    
    /**
     * Checks presence of named field in contained SearchParameter objects
     * @param string $field The name of a field
     * @return boolean TRUE if there is at least one match, FALSE otherwise.
     */
    public function hasField($field)
    {
        $answer = FALSE;
        foreach($this->return_fields as $operator => $array){
            $this_field = key($array);
            if($field===$this_field){
                $answer = TRUE;
                break;
            }
        }
        return $answer;
    }
    
    /**
     * Returns an array of SearchParameter that correspond to the provided field
     * @param string $field The name of a field
     * @return array<SearchParameter> Those Parameters that have $field for their field values
     */
    public function getFromField($field)
    {
        $answer = array();
        for($i=count($this->return_tuples)-1;$i>-1;$i--){
            if($this->return_tuples[$i]->getField()===$field){
                $answer[] = $this->return_tuples[$i];
            }
        }
        return $answer;
    }   
    
    public function hasOperator($operator)
    {
        $temp_arr = array_keys($this->return_fields);
        return array_search($operator, $temp_arr);
    }
    
    /**
     * Returns an array of SearchParameter that correspond to the provided field
     * @param string $operator Name of one of the search operators from RestSearch constants
     * @return array<SearchParameter> Those Parameters that have $operator for their operator values
     */
    public function getFromOperator($field)
    {
        $answer = array();
        for($i=count($this->return_tuples)-1;$i>-1;$i--){
            if($this->return_tuples[$i]->getOperator()===$field){
                $answer[] = $this->return_tuples[$i];
            }
        }
        return $answer;
    }
}