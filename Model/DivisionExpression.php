<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class DivisionExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $dividend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $divisor Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param boolean $parenthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($dividend, $divisor, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($dividend, '\Segment\Model\production\IntegerValue')||
is_a($dividend,'\Segment\Model\production\Variable'))&&
                (is_a($divisor,'\Segment\Model\production\IntegerValue')||
is_a($divisor, '\Segment\Model\production\Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $dividend->getValues()[0];
            $var2 = $divisor->getValues()[0];
            $this->values[] = "{$lp}{$var1}/{$var2}{$rp}";
            $this->count++;
        }
    }
}