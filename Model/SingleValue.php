<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SingleValue extends \Segment\Model\SearchValues
{
    public function __construct($value)
    {
        $this->values[] = $value;
        $this->count++;
    }
}