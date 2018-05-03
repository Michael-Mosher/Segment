<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

trait SearchRestRequest
{
    /**
     * @var array<array<array>> assoc. array key: search type, value:
     *         assoc. array key: search field, value: array of values sought
     */
    private $search_fields = [];
    private $search_tuples = [];
    private $search_count = 0;
    private $search_index = NULL;
    private $search_queue;
    
    /**
     * Add elements from the REST request
     * @param string $field
     * @param mixed $value
     * @param string $type RestSearch constant. Default RestSearch::EQUAL
     * @param string $conjunctive The type of conjuction for handling of multiple search
     *         criteria. The permitted values are AND, OR, and NOCONJ. Default NOCONJ.
     * @param bool $exclusive_range Whether a range's endpoints should be considered
     *         exclusively, TRUE, or inclusively, FALSE. Default FALSE.
     * @return bool TRUE if addition successful, FALSE otherwise.
     */
    protected function addSearchCell(string $field, $value, string $type,
            string $conjunctive, bool $exclusive_range = FALSE): bool
    {
        $result = FALSE;
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if((array_search($type, RestRequest::SEARCH_ARRAY)===FALSE&&!is_array($value))
                || (array_search($type, RestRequest::SEARCH_ARRAY)!==FALSE&&is_array($value))){
            $rest_search_reflection = new \ReflectionClass('\Segment\Controller\RestSearch');
            $const_list = $rest_search_reflection->getConstants();
            if(!isset($this->search_fields[$type])){
                $this->search_fields[$type] = [];
            }
            if(array_search($type, $const_list)!==FALSE&&
                    !isset($this->search_fields[$type][$field])){
                $options = [];
                $options[\Segment\Model\production\ModelCaller::EXCLUSIVERANGE] = $exclusive_range;
                $options[\Segment\Model\production\ModelCaller::ANDORNOCONJ] = $conjunctive;
                $this->search_tuples[] = new production\SearchParameter(
                        $field,$value,$type, $options
                );
                $this->search_fields[$type][$field] = $value;
                $this->search_index = is_null($this->search_index) ? 0 : $this->search_index;
                $this->search_count++;
                $result = TRUE;
            }
        }
        return $result;
    }
    
    /**
     * Remove elements
     * @param string $field
     * @param mixed $value
     * @param string $type RestSearch constant
     */
    protected function removeSearchCell(string $field, $value, string $type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        unset($this->search_fields[$type][$field]);
    }
    
    /**
     * Remove column, and all associated elements.
     * @param string $field
     * @param string $type Must be RestSearch constant
     */
    protected function unsetSearchColumn($field, $type = RestSearch::EQUAL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        unset($this->search_fields[$type][$field]);
    }
    
    /**
     * Sets SearchParameter tuple index to beginning
     */
    protected function searchRewind()
    {
        $this->search_index = 0;
    }
    
    /**
     * Returns current search field (0), value (1), and type (2) in SearchParameter
     * @return SearchParameter|FALSE
     * @throws \OutOfBoundsException|\Exception
     */
    protected function getSearchTuple()
    {
        if(isset($this->set_tuples[$this->set_index]))
            return $this->set_tuples[$this->set_index];
        else
            return FALSE;
    }
    
    /**
     * Increments search index
     * @return boolean TRUE if there is another valid entry. FALSE if past end.
     */
    protected function searchNext()
    {
        $this->search_index++;
        $answer = $this->search_index>$this->search_count ? FALSE : TRUE;
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
        foreach($this->search_fields as $operator => $array){
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
        for($i=count($this->search_tuples)-1;$i>-1;$i--){
            if($this->search_tuples[$i]->getField()===$field){
                $answer[] = $this->search_tuples[$i];
            }
        }
        return $answer;
    }   
    
    public function hasOperator($operator)
    {
        $temp_arr = array_keys($this->search_fields);
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
        for($i=count($this->search_tuples)-1;$i>-1;$i--){
            if($this->search_tuples[$i]->getOperator()===$field){
                $answer[] = $this->search_tuples[$i];
            }
        }
        return $answer;
    }
}