<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class Variable implements Expression
{
    use search_value;

    protected $values;
    /**
     *
     * @param string $value Required to conform to DB expression syntax
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->values = new \SplFixedArray(1);
        $this->values->offsetSet(0, $value);
        $this->count++;
    }
}