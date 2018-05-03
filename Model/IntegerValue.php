<?php
namespace Segment\Model\production;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class IntegerValue extends SingleValue
{
    /**
     * 
     * @param integer $value
     */
    public function __construct($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        parent::__construct($value);
    }
}