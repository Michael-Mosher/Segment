<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");
//require_once ('Model.php');
//require_once ('source_h.php');



/*class SqlWildcardExpression extends \Segment\Model\production\WildcardExpression
{
    public function __construct($field, $value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $field = "{$field} LIKE %{$value}%";
        parent::__construct($field, $value);
    }
}*/

class SqlModuloExpression extends \Segment\Model\ModuloExpression
{
    public function getValues()
    {
        $lp = $this->parenthesis ? '(' : '';
        $rp = $this->parenthesis ? ')' : '';
        $answer = "{$lp}{$this->values[self::DIVIDEND]}%{$this->values[self::DIVISOR]}{$rp}";
        $this->values = [$answer];
        return parent::getValues();
    }
}