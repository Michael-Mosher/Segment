<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface ClauseBuilder
{
    /**
     * Returns the clause as a string for querying model
     * @param array<string> $tables Array of table names
     * @return string
     */
    public function getClause(/*array $tables = array()*/);
}