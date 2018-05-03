<?php
namespace Segment\Model\production\SQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlBetweenExpression extends \Segment\Model\AverageExpression
{
    public function __construct($field, $value1, $value2, $options = 0)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $operator = ($options&BetweenExpression::EXCL)===BetweenExpression::EXCL ? '<' : '<=';
        $operator2 = ($options&BetweenExpression::EXCL)===BetweenExpression::EXCL ? '>' : '>=';
        $join = ($options&BetweenExpression::NOT)===BetweenExpression::NOT ? ' OR ' : ' AND ';
        if(($options&BetweenExpression::NOT)===BetweenExpression::NOT){
            $temp = $operator;
            $operator = $operator2;
            $operator2 = $temp;
        }
        parent::__construct($field.$operator.$value1.$join.$field.$operator2.$value2, '', '');
    }

}