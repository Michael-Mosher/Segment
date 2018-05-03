<?php
namespace Segment\Model\production;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class BetweenValues extends \Segment\Model\SearchValues
{
    public function __construct($first, $second)
    {
        $lower_bound = $first<$second ? $first : $second;
        $upper_bound = $lower_bound===$first ? $second : $first;
        $this->values = [
            $lower_bound,
            $upper_bound
        ];
        $this->count = 2;
    }
}