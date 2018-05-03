<?php
namespace Segment\Model\production\SQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlEqualityExpression implements \Segment\Model\Expression
{
    private $payload;
    /**
     * 
     * @param {string|\Segment\Model\production\StringValue|\Segment\Model\Column} $field
     * @param {\Segment\Model\SearchValues} $value
     */
    public function __construct($field, \Segment\Model\SearchValues $value)
    {
        //try {
            error_log(__METHOD__ . " the field: $field, and the value: $value");
            //\Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
            if(is_a($field, '\Segment\Model\production\Values')){
                    $field = print_r($field->getValues(), TRUE);
            } else if(is_a($field, '\Segment\Model\Column'))
                    $field = $field->getWhereColumn();
            if(is_a($value, '\Segment\Model\production\SearchValues'))
                    $value = print_r($value->getValues(),TRUE);
            
            $this->payload = "$field=$value";
//        } catch (\Exception $exc) {
//            error_log(__METHOD__ . $exc->getTraceAsString());
//        }
        
    }
    
    public function __toString() {
        return $this->payload;
    }
}

