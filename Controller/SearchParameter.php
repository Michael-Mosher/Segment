<?php
namespace Segment\Controller\production;

class SearchParameter implements \Segment\Controller\SearchParameterInterface
{
    private $tuple;
    
    const conjunctive = 3;
    const exclusivity = 4;

    /**
     * Constructor
     * @param string $field Search target
     * @param mixed $value Search operand
     * @param string $operator Constant of RestSearch
     * @param string $conjunctive
     * @param bool $exclusive_range
     */
    public function __construct($field, $value, $operator, $conjunctive = 'noconj',
            $exclusive_range = FALSE) {
        if(!isset($conjunctive)||!is_string($conjunctive)||strlen($conjunctive)===0){
            $conjunctive = 'noconj';
        }
        //\Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->tuple = new \SplFixedArray(5);
        $this->tuple[self::field] = $field;
        $this->tuple[self::value] = $value;
        $this->tuple[self::operator] = $operator;
        $this->tuple[self::conjunctive] = $conjunctive;
        $this->tuple[self::exclusivity] = $exclusive_range;
    }

    public function getField()
    {
        $answer = NULL;
        try {
            $answer = $this->tuple[self::field];
        } catch (\OutOfBoundsException $out) {
            throw new \OutOfBoundsException(__METHOD__ .
            " out of bounds. Be kind, call " . __CLASS__ . "::rewind()");
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        } finally {
            return $answer;
        }
    }

    public function getValue()
    {
        $answer = NULL;
        try {
            $answer = $this->tuple[self::value];
        } catch (\OutOfBoundsException $out) {
            throw new \OutOfBoundsException(__METHOD__ .
            " out of bounds. Be kind, call " . __CLASS__ . "::rewind()");
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        } finally {
            return $answer;
        }
    }

    public function getOperator()
    {
        $answer = NULL;
        try {
            $answer = $this->tuple[self::operator];
        } catch (\OutOfBoundsException $out) {
            throw new \OutOfBoundsException(__METHOD__ .
            " out of bounds. Be kind, call " . __CLASS__ . "::rewind()");
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        } finally {
            return $answer;
        }
    }

    public function getConjunctive()
    {
        return $this->tuple[self::conjunctive];
    }
    
    public function isExclusiveRange(): bool
    {
        return $this->tuple[self::exclusivity];
    }

}
