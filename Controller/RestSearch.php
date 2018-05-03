<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface RestSearch
{
    const EQUAL = 'equal';
    const NEQUAL = 'nequal';
    const EQUALANY = 'equalany';
    const EQUALALL = 'equalall';
    const GREATER = 'greater';
    const GREATEQ = 'greateq';
    const GREATANY = 'greatany';
    const GREATALL = 'greatall';
    const LESSER = 'lesser';
    const LESSEQ = 'lesseq';
    const LESSANY = 'lessany';
    const LESSALL = 'lessall';
    const BETWEEN = 'between';
    const NBETWEEN = 'nbetween';
    //const RS_AND = 'and';
    //const RS_OR = 'or';
}