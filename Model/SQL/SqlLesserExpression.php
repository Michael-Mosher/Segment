<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlLesserExpression extends \Segment\Model\AverageExpression
{
    
    public function __construct($field, $values, $exclusive = FALSE, $options = 0)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $equals = $exclusive ? '' : '=';
        $modifier = $options>0 ? ' ANY ' : $options<0 ? ' ALL ' : '';
        parent::__construct("{$field} <{$equals} {$modifier}({$values})", '');
    }
}