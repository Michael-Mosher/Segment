<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class WildcardExpression implements \Segment\Model\Expression
{
    use \Segment\Model\search_value;
    
   
    /**
     * Boolean equality expression
     * @param mixed $field
     * @param mixed $value1
     * @param mixed $value2
     * @param integer $options
     */
    public function __construct($field, $value)
    {
        $this->values[] = $field;
        $this->count++;
    }
}