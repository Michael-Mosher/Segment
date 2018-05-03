<?php

namespace Segment\Controller\production;

class SetParameter implements \Segment\Controller\Parameter
{
    private $tuple;
    private $count = 0;
    private $index = NULL;
    const field = 0;
    const new_value = 1;
    const old_value = 2;

    /**
     * SetParameter constructor
     * @param string $field
     * @param mixed $new_value
     * @param mixed $operator Must be either "posts" for POST, or "requests" for PUT.
     */
    public function __construct(string $field, $value, string $operator, array $options): bool
    {
        $answer = FALSE;
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->tuple = new \SplFixedArray(3);
        if(strtolower(trim($operator))==="posts" || strtolower(trim($operator))==="requests"){
            $this->tuple[SetParameter::field] = $field;
            $this->tuple[SetParameter::new_value] = $value;
            $this->tuple[SetParameter::old_value] = $operator;
            if(is_null($this->index))
                $this->index = 0;
            $this->count++;
        } else {
            $this->tuple[SetParameter::field] = NULL;
            $this->tuple[SetParameter::new_value] = NULL;
            $this->tuple[SetParameter::old_value] = NULL;
        }
        return $answer;
    }

    public function getField() {
        return $this->tuple[SetParameter::field];
    }

    public function getValue() {
        return $this->tuple[SetParameter::new_value];
    }
    
    public function getOperator()
    {
        return $this->tuple[SetParameter::old_value];
    }
    
    public function __clone()
    {
        $this->tuple = clone $this->tuple;
        $this->tuple[SetParameter::field] = $this->tuple[SetParameter::field];
        $this->tuple[SetParameter::new_value] = $this->tuple[SetParameter::new_value];
        if($this->getOldValue()!==NULL){
            $this->tuple[SetParameter::old_value] = $this->tuple[SetParameter::old_value];
        }
    }

}