<?php

namespace Segment\Model;
session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class ModuloExpression implements Expression
{
    use search_value;

    const DIVIDEND = 0;
    const DIVISOR = 1;
    protected $parenthesis;

    /**
     *
     * @param IntegerValue $dividend Only the first value will be used.
     * @param IntegerValue $divisor Only the first value will be used.
     * @param boolean $parenthesis TRUE will wrap the expression in parenthesis
     */
    public function __construct($dividend, $divisor, $parenthesis = FALSE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args());
        if((is_a($dividend, '\Segment\Model\production\IntegerValue')||
                is_a($dividend,'\Segment\Model\production\Variable'))&&
                (is_a($divisor,'\Segment\Model\production\IntegerValue')||
                is_a($divisor, '\Segment\Model\production\Variable'))){
            $this->parenthesis = $parenthesis;
            $this->values = new \SplFixedArray(2);
            $this->values->offsetSet(self::DIVIDEND, $dividend);
            $this->values->offsetSet(self::DIVISOR, $divisor);
            $this->count = $this->values->count;
        }
    }

    public function usesParenthesis()
    {
        return $this->parenthesis;
    }
}