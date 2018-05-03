<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class AssignmentExpression implements Expression
{
    use search_value;

    const VALUE = 0;
    const VARIABLE = 1;

    /**
     *
     * @param SearchValues|Statement|Expression $value
     * @param Variable $var
     */
    public function __construct($value, Variable $var)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::VALUE, $value);
        $this->values->offsetSet(self::VARIABLE, $var);
        $this->count = $this->values->count();
    }
}