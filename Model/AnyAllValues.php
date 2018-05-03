<?php
namespace Segment\Model\production;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class AnyAllValues extends \Segment\Model\SearchValues
{
    
    /**
     * @param SingleValue $values Variable-length variable.
     */
    public function __construct(SingleValue ...$values)
    {
        $this->values = $values;
        $this->count = isarray($values) ? count($values) : 1;
    }
}