<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class ConcatenateExpression implements Expression
{
    use search_value;

    /**
     * Constructor
     * @param \Segment\Model\SearchValues|\Segment\Model\Expression $value1
     * @param \Segment\Model\SearchValues|\Segment\Model\Expression $values Variable-length variable.
     */
    public function __construct($value1, ...$values)
    {
        $count = count($values);
        $v_arr = new \SplFixedArray($count+1);
        $v_arr->offsetSet(0, $value1);
        for($i=0;$i<$count;$i++){
            $v_arr->offsetSet($i+1, $values[$i]);
        }
        $this->values = $v_arr;
        $this->count = $v_arr->count();
    }
}