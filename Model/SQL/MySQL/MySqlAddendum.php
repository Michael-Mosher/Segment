<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlAddendum implements \Segment\Model\Addendum
{
    private $order_by = array();
    private $group_by = array();
    private $limit = '';
    
    /**
     * Constructor
     * @param array $order_by
     * @param array $group_by
     * @param string $limit
     */
    public function __construct(array $order_by, array $group_by, string $limit)
    {
        $this->order_by = $order_by;
        $this->group_by = $group_by;
        $this->limit = $limit;
    }
    
    public function getAddendum()
    {
        $answer = '';
        if(count($this->group_by)>0){
            $group_by = \Segment\utilities\Utilities::arrayCopy($this->group_by);
            $gb_string = '';
            last($group_by);
            for($i= key($group_by); $i>-1; $i--){
                $gb_string .= strlen($gb_string)>0
                        ? ', ' . $group_by[$i] : $group_by[$i];
            }
            $answer .= ' GROUP BY ' . $gb_string;
        }
        
        if(count($this->order_by)>0){
            $order_by = \Segment\utilities\Utilities::arrayCopy($this->order_by);
            $ob_string = '';
            for($i=0, $max=count($order_by); $i<$max; $i++){
                $ob_string .= strlen($ob_string)>0
                        ? ', ' . $order_by[$i] : $order_by[$i];
            }
            $answer .= ' ORDER BY ' . $ob_string;
        }
        
        if(strlen($this->limit)>0){
            $answer .= ' ' . $this->limit;
        }
        
        return $answer;
    }
}