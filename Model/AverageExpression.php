<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


class AverageExpression implements Expression
{
    use search_value;
    /**
     * Finds the mathematical mean of a column
     * @param Column $clmn
     */
    public function __construct(Column $clmn)
    {
        error_log(__METHOD__ . " the Column: " . print_r($clmn, TRUE));
        $this->values = new \SplFixedArray(1);
        $this->values->offsetSet(0, $clmn);
        $this->count++;
    }
}