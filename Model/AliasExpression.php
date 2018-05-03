<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class AliasExpression implements Expression
{
    use search_value;
    protected $parenthesis;
    const VALUE = 0;
    const ALIAS = 1;

    /**
     * Only the first value will be used.
     * @param Column|Statement $value
     * @param production\StringValue $alias
     * @param boolean $parenthesis
     */
    public function __construct($value, \Segment\Model\production\StringValue $alias, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(2);
        $this->parenthesis = $parenthesis;
        $this->values->offsetSet(self::VALUE, $value);
        $this->values->offsetSet($index, $alias);
        $this->count = $this->values->count();
    }

    public function usesParenthesis()
    {
        return $this->parenthesis;
    }
}