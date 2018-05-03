<?php

namespace Segment\Controller\production;

class PutHttpRequest extends \Segment\Controller\RestRequest
{
    use \Segment\Controller\SetRestRequest,
            \Segment\Controller\SearchRestRequest;

    /**
     * Has the index completed all of Set and already pointed to the first entry of 
     * Search before incrementing.
     * @var bool
     */
    private $index_passed_first_search = FALSE;
    
    /**
     * Only Search has Return, but Put and Delete also have Search.
     * {
            "PUT":{
                "requests":[
                    {
                        "field" : (string)"field_name",
                        "value" : (mixed)0
                    }
                ], "AND|OR|NOCONJ":[
                    {
                        "EQUAL...NBETWEEN":{
                            "field_name":(doesn't have to be an array, depends on search command)[
                                (mixed, can be an associative array that starts a new SEARCH)"val1",
                                "val2"
                            ],
                            "exclusive_range" : (bool)true
                        }
                    }
                ]
            }
        }
     */
    
    public function __construct(array $rest)
    {
        $rest_search_reflection = new \ReflectionClass("\Segment\Controller\RestSearch");
        $rest_return_reflection = new \ReflectionClass("\Segment\Controller\RestReturn");
        $const_list = $rest_search_reflection->getConstants();
        $rrr = $rest_return_reflection->getConstants();
        $search_const_ct = \count($const_list);
        $return_const_ct = \count($rrr);
        for(;$r_value = array_shift($rrr);):
            $const_list[] = $r_value;
        endfor;
        $this->assertCount($search_const_ct+$return_const_ct,$const_list,
                __METHOD__ . " the count of REST search constants plus the countn of"
                . " REST return constants equals the combined total");
        foreach($rest as $conjunctive => $array):
            if(strtolower(trim($conjunctive))==="requests" || strtolower(trim($conjunctive))==="posts"){
                foreach($array as $field => $value):
                    $this->addSetCell($field, $value, $$conjunctive);
                endforeach;
            }
            if(strtolower(trim($conjunctive))==="and" || strtolower(trim($conjunctive))==="or"
                    || strtolower(trim($conjunctive))==="noconj"){
                for ($i = count($rest[$conjunctive]) - 1; $i > -1; $i--) :
                    $operator = key($rest[$index][$i]);
                    if(isset($array[$conjunctive][$i][$operator]["exclusive_range"])){
                        $exclusive_range = $array[$conjunctive][$i][$operator]["exclusive_range"];
                        unset($array[$conjunctive][$i]["exclusive_range"]);
                    }
                    if(isset($array[$conjunctive][$i][$operator]["EXCLUSIVE_RANGE"])){
                        $exclusive_range = $array[$conjunctive][$i][$operator]["EXCLUSIVE_RANGE"];
                        unset($array[$conjunctive][$i][$operator]["EXCLUSIVE_RANGE"]);
                    }
                    $this->assertCount(1, $array[$conjunctive][$i][$operator]);
                    if(count($array[$conjunctive][$i][$operator])===1){
                        $field = key($array[$conjunctive][$i][$operator]);
                        if(!is_array($array[$conjunctive][$i][$operator][$field]) ||
                                (is_array($array[$conjunctive][$i][$operator][$field]) &&
                                is_numeric(key($array[$conjunctive][$i][$operator][$field])))){
                            $value = $array[$conjunctive][$i][$operator][$field];
                        } else if(is_array ($array[$conjunctive][$i][$operator][$field])){
                            $value = new SearchHttpRequest($array[$conjunctive][$i][$operator][$field]);
                        } else {
                            throw new \InvalidArgumentException(__METHOD__ . " badly formatted argument"
                                    . ". Was given: " . print_r($array, TRUE));
                        }
                        $this->addSearchCell($field, $value, $operator, $conjunctive, $exclusive_range);// conjunctive and exclusive_range
                    } else {
                        throw new \InvalidArgumentException(__METHOD__ . " badly formatted argument"
                                . ". Only exclusive_range and the fields name can be keys"
                                . " in the search function object. Was given: " . print_r($array, TRUE));
                    }
                endfor;
            }
        endforeach;
    }

    public function get()
    {
        if(!$answer = $this->getSetTuple())
            $answer = $this->getSearchTuple ();
        return $answer;
    }

    /**
     * 
     * @return int
     * @deprecated since version 1
     */
    public function count()
    {
        return $this->getSetCount();
    }

    public function next()
    {
        if(!$answer = $this->setNext()){
            if($this->index_passed_first_search)
                $answer = $this->searchNext ();
            else
                $answer = (bool) $this->getSearchTuple ();
        }
        return $answer;
    }

    public function rewind()
    {
        $this->setRewind();
        $this->searchRewind();
    }

}