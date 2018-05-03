<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SubtractionExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
    /**
     * 
     * @param SingleValue $minuend Either IntegerValue, FloatValue, or Variable.
     * Only the first value will be used.
     * @param SingleValue $subtrahend Either IntegerValue, FloatValue, or Variable.
     *  Only the first value will be used.
     * @param boolean $paranthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($minuend, $subtrahend, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if((is_a($minuend, 'IntegerValue')||is_a($minuend,'Variable'))&&
                (is_a($subtrahend,'IntegerValue')||is_a($subtrahend, 'Variable'))){
            $lp = $parenthesis ? '(' : '';
            $rp = $parenthesis ? ')' : '';
            $var1 = $minuend->getValues()[0];
            $var2 = $subtrahend->getValues()[0];
            $this->values[] = "{$lp}{$var1}-{$var2}{$rp}";
            $this->count++;
        }
    }
}