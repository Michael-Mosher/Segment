<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface SearchModelCaller
{
    /**
     * 
     * @param RestSearch $search
     * @param RestSearch $other_searches variable length
     */
    public function __construct(RestSearch $search, RestSearch ...$other_searches);
}