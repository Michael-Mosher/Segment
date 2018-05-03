<?php
namespace Segment\Model\production;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class BitValue extends IntegerValue
{
    public function __construct($value)
    {
        $submission = (integer)$value;
        if((integer)$value<=0){
            $submission = 0;
        } else {
            $submission = 1;
        }
        parent::__construct($submission);
    }
}