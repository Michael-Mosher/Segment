<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MultiplicationExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $multiplier Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $multiplicand Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param boolean $paranthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($multiplier, $multiplicand, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($multiplier, '\Segment\Model\production\IntegerValue')||
is_a($multiplier,'\Segment\Model\production\Variable'))&&
                (is_a($multiplicand,'\Segment\Model\production\IntegerValue')||
is_a($multiplicand, '\Segment\Model\production\Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $multiplier->getValues()[0];
            $var2 = $multiplicand->getValues()[0];
            $this->values[] = "{$lp}{$var1}*{$var2}{$rp}";
            $this->count++;
        }
    }
}