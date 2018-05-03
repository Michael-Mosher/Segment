<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlAliasExpression extends \Segment\Model\AliasExpression
{
    public function __construct($value, \Segment\Model\production\StringValue $alias, $parenthesis = FALSE)
    {
        if(is_a($value, 'Column')||is_a($value, 'Expression')||is_a($value, 'Statement')){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            parent::__construct($lp . $value->getValues()[0] . "{$rp} AS {$alias}", $alias);
        }
    }
}