<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


interface WhereClauseBuilder extends SearchClauseBuilder
{
    public function __construct(InputOutput $parent);
    public function __invoke();
    public function getClause(array $tables = []);
    public function addend($where_targets, $operators, $value_var, $value_qt, $interstitial);
    /**
     * Returns where clause sought values
     * @return array<SearchValues> values to be searched
     */
    public function getValues();
}