<?php
namespace Segment\Model\production\SQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlInSetExpression extends \Segment\Model\AverageExpression
{
    public function __construct($field, $values)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        parent::__construct("{$field} IN ({$values})", '');
    }

}