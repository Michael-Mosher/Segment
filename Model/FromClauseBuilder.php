<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


interface FromClauseBuilder extends ClauseBuilder
{

    /**
     * Composes FROM clause string based on the tables in scope for query
     * @param \Segment\Model\Table $tables Array of table names
     */
    public function getClause(\Segment\Model\Table ...$tables);
}