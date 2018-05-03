<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class LesserExpression implements Expression
{
    use search_value;

    const ALL = -1;
    const ANY = 1;
    const NONE = 0;
    const FIELD = 1000;
    const VALUES = 1001;

    /**
     * Ordinal "less than" expression
     * @param Column|Expression $field The column or expression to be tested
     * @param SingleValue|AnyAllValues|Statement|Variable $values The values, whether
     *  static or dynammic, to be tested against
     */
    public function __construct($field, $values, $options = self::NONE)
    {
        \Segment\utilities\Utilities::areArgumentsValid(
                __NAMESPACE__.'\\'.__CLASS__, __METHOD__, func_get_args()
                );
        $this->values = new \SplFixedArray(2);
        $this->values->offsetSet(self::FIELD, $field);
        $this->values->offsetSet(self::VALUES, $values);
        $this->count = $this->values->count;
    }
}