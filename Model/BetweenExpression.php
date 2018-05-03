<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class BetweenExpression implements Expression
{
    use search_value;

    const EXCL = 2;
    const NOT = 4;
    protected $exclusive_between;
    protected $not_between;
    const FIELD = 0;
    const VALUES = 1;

    /**
     * Boolean equality expression
     * @param Expression|Column $field
     * @param \Segment\Model\production\BetweenValues $values
     * @param integer $options
     */
    public function __construct($field,  production\BetweenValues $values, $options = 0)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUES, $values);
        $this->exclusive_between = $options | ~self::EXCL;
        $this->not_between = $options | ~self::NOT;
        $this->count = $this->values->count;
    }

    public function isExclusive()
    {
        return $this->exclusive_between;
    }

    public function isInvertedSet()
    {
        return $this->not_between;
    }
}