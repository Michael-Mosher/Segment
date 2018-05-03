<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlConcatenateExpression extends \Segment\Model\ConcatenateExpression
{
    public function __construct($value1, ...$values)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $answer = 'CONCAT(';
        $value_array = $value1->getValues();
        for($i=0, $max=$value1->getValuesQuantity();$i<$max;
                $answer.=strlen($value)>7?', '.$value_array[$i]:$value_array[$i],$i++)
        if(is_array($values)){
            foreach($values as $k => $v){
                if(is_a($v, '\Segment\Model\SearchValues')||is_a($v, '\Segment\Model\Expression')){
                    $value_array = $v->getValues();
                    for($i=0, $max=$v->getValuesQuantity();$i<$max;$i++){
                        $answer .= strlen($value)>7 ? ', ' . $value_array[$i] : $value_array[$i];
                    }
                }
            }
        }
        $answer .= ')';
        parent::__construct(new \Segment\Model\production\StringValue($value1));
    }
}