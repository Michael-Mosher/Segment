<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface RestReturn
{
    const NORMAL = "normal";
    const FIELDCOUNT = "field_count";
    const FIELDAVG = "field_avg";
    const FIELDSET = "field_set";
    const FIELDMODE = "field_mode";
    const FIELDMEDIAN = "field_median";
    const FIELDFIRSTQ = "field_firstq";
    const FIELDTHIRDQ = "field_thirdq";
    const LIMIT = "limit";
    const ORDER = "order";
}