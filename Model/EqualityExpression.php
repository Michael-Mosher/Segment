<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class EqualityExpression implements Expression
{
    use search_value;

    const FIELD = 0;
    const VALUE = 1;
    /**
     * Boolean equality expression
     * @param Column|Expression $field
     * @param production\SingleValue|Expression|Variable $value
     */
    public function __construct($field, $value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUE, $value);
        $this->count = 2;
    }
}