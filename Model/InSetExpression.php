<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class InSetExpression implements Expression
{
    use search_value;

    const ITEM = 1000;
    const SET = 1001;

    /**
     * Boolean set-membership-checking expression
     * @param Expression|Column $field
     * @param \Segment\Model\production\AnyAllValues $values
     */
    public function __construct($item, $set)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::ITEM, $field);
        $this->values->offsetSet(self::SET, $values);
        $this->count = $this->values->count;
    }
}