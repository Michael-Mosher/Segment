<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


interface DeleteClauseBuilder extends ClauseBuilder
{
    /**
     * Composes DELETE clause string based on the tables in scope for query
     * @param array<string> $tables Array of table names
     */
    public function getClause(array $tables = []);
}