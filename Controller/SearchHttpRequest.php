<?php

namespace Segment\Controller\Testing;

class SearchHttpRequest extends TestRestRequest implements \Segment\utilities\ForIterable
{
    use \Segment\Controller\SearchRestRequest,
        \Segment\Controller\Testing\ReturnRestRequest;

    private $conjunctive;
    private $row_max;
    private $row_min;
    private $row_increment;
    private $index_passed_first_return_position;

    /**
     * Only Search has Return, but Put and Delete also have Search.
     * {
            "SEARCH":{
                "AND|OR|NOCONJ":[
                    {
                        "EQUAL...NBETWEEN" : {
                            "field_name" : (doesn't have to point to array, depends on search function) [
                                (mixed, can be an associative array that starts a new SEARCH)"val1",
                                "val2"
                            ],
                            "exclusive_range":(bool)true
                        }
                    }
                ],
                "return":{
                    "fields":[
                        {
                            "NORMAL...THIRDQ" : (string)"field_name",
                            "ALIAS" : (string)"string"
                        }
                    ],
                    "limit_start" : (float)0.0,
                    "limit_amount" : (float)1.0,
                    "limit_start_abs" : (bool)false,
                    "limit_amount_abs" : (bool)false,
                    "order":[
                        {
                            "field_name" : (number>0:ASC,number<0:DESC)-1
                        }
                    ]
                }
            }
        }
     */
    
    /**
     * SearchHttpRequest Constructor
     * @param array $rest Associative array that represents the REST function parameters
     * @test
     */
    public function __construct(array $rest)
    {
        $field;
        $value;
        $operator;
        $required;
        $conjunctive;
        $exclusive_range;
        if (isset($rest['row_increment'])) {
            $row_increment = $rest['row_increment'];
            unset($rest['row_increment']);
        } else if (isset ($rest["ROW_INCREMENT"]))
            $row_increment = $rest["ROW_INCREMENT"];
        $row_min = $rest['row_min'] ?? 0;
        $row_min = $rest["ROW_MIN"] ?? $row_min;
        if(isset($row_increment) || $row_min>0){
            $this->addReturnLimitCell($row_min, TRUE, $row_increment, TRUE, \Segment\Controller\RestReturn::LIMIT);
        }
        
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
            if(strtolower(trim($key))==="and" || strtolower(trim($key))==="or"
                    || strtolower(trim($key))==="noconj"){
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
            } else if(strtolower(trim($key))==="return"){
                // need to determine RETURN equivalent fo addSearchCell, and how does that impact ::next()
                // Change the numbers for the ReturnParameter, ReturnLimitParameter, and ReturnOrderParameter
                // so they are unique, then use them to populates the keys of an $options array
                // to be passed to the three different addReturnCell equivalents.
                // ********COMPLETED THE ABOVE*******
                // Now need to do the same for Search and Set and Delete
                // Need to ensure all the RestRequest objects are using ::get and ::next
                // such that multiple traits are accommodated.
                if(isset($array[$key]["fields"])){
                    $fields_key = "fields";
                } else if (isset($array[$key]["FIELDS"])){
                    $fields_key = "FIELDS";
                }
                foreach ($array[$key][$fields_key] as $fields_obj):
                    if(isset($array[$key][$fields_key]["alias"]))
                        $alias = isset($array[$key][$fields_key]["alias"]);
                    else if(isset($array[$key][$fields_key]["ALIAS"]))
                        $alias = isset($array[$key][$fields_key]["ALIAS"]);
                    $this->addReturnCell($fields_key, NULL, $key, $alias);
                endforeach;
                /**
                 * "return":{
                    "fields":[
                        {
                            "NORMAL...THIRDQ" : (string)"field_name",
                            "ALIAS" : (string)"string"
                        }
                    ],
                    "limit_start" : (float)0.0,
                    "limit_amount" : (float)1.0,
                    "limit_start_abs" : (bool)false,
                    "limit_amount_abs" : (bool)false,
                    "order":[
                        {
                            "field_name" : (number>0:ASC,number<0:DESC)-1
                        }
                    ]
                }
                 */
                if(isset($array[$key]["limit_start"]) || isset($array[$key]["limit_amount"])){
                    $lsn = $array[$key]["limit_start"] ?? (float)NULL;
                    $lan = $array[$key]["limit_amount"] ?? (float)NULL;
                    $lsa = $array[$key]["limit_start_abs"] ?? TRUE;
                    $laa = $array[$key]["limit_amount_abs"] ?? TRUE;
                    $this->addReturnLimitCell($lsn, $lsa, $lan, $laa, \Segment\Controller\RestReturn::LIMIT);
                } else if(isset($array[$key]["LIMIT_START"]) || isset($array[$key]["LIMIT_AMOUNT"])){
                    $lsn = $array[$key]["LIMIT_START"] ?? (float)NULL;
                    $lan = $array[$key]["LIMIT_AMOUNT"] ?? (float)NULL;
                    $lsa = $array[$key]["LIMIT_START_ABS"] ?? TRUE;
                    $laa = $array[$key]["LIMIT_AMOUNT_ABS"] ?? TRUE;
                    $this->addReturnLimitCell($lsn, $lsa, $lan, $laa, \Segment\Controller\RestReturn::LIMIT);
                }
                if(isset($array[$key]["order"]))
                    $order_key = "order";
                else if(isset($array[$key]["ORDER"]))
                    $order_key = $array[$key]["ORDER"];
                if(isset($array[$key][$order_key])){
                    $order_field = key($array[$key][$order_key]);
                    $this->addReturnOrderCell(
                            $order_field, (int)$array[$key][$order_key][$order_field],
                            \Segment\Controller\RestReturn::ORDER
                    );
                }
            }
        endforeach;
        
    }

    /**
     * Returns RestSearch::RS_AND or RestSearch::RS_OR indicating how the parameters in this
     * search should be conjoined
     * @return string
     */
    public function getConjunctive()
    {
        return $this->conjunctive;
    }

    public function rewind()
    {
        $this->searchRewind();
        $this->returnRewind();
    }

    /**
     * Returns either a SearchParameter or ReturnParameter object, which is a tuple of search field, value, type.
     * @return {\Segment\Controller\Parameter|NULL}
     */
    public function get(): \Segment\Controller\Parameter
    {
        $answer = $this->getSearchTuple();
        $answer = is_null($answer)||$answer===FALSE ? $this->getReturnTuple() : $answer;
        return $answer;
    }

    public function next(): bool
    {
        if(!$answer = $this->searchNext()){
            if($this->index_passed_first_return_position)
                $answer = $this->returnNext ();
            else
                $answer = (bool) $this->getSearchTuple ();
        }
        return $answer;
    }

    /**
     * @deprecated since version 1
     * @return int
     */
    public function getRowMin()
    {
        return $this->row_min;
    }

    /**
     * @deprecated since version 1
     * @return int
     */
    public function getRowIncrement()
    {
        return $this->row_increment;
    }

    /**
     * @deprecated since version 1
     * @return int
     */
    public function getRowMax()
    {
        return $this->row_max;
    }

}