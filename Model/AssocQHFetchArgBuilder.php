<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

/**
 * DAO implementation that returns arrays of Record objects
 */
class AssocQHFetchArgBuilder implements \Segment\Model\QHFetchArgBuilder
{
    public function __construct()
    {}

    /**
     * Fetches all returned DB rows with column names as keys formatted as array of Rest
     * @param \Segment\Model\Segment\Model\Statement $stmt
     * @param string $call_id
     * @return array<\Segment\utilities\Rest>
     */
    public function fetch(\PDOStatement $stmt, $call_id)
    {
        $answer = array();
        $i = 0;
        while($entry = $stmt->fetch(\PDO::FETCH_NAMED | \PDO::FETCH_CLASS)){
            $new_record = new \Segment\utilities\Record($call_id);
            
            foreach($entry as $k => $v){
                $new_record->addend($k, ($value = \is_array($v) ? $v : [$v]));
                if(\strpos($k, '_id')!==FALSE && \strpos($k, '_id')+3 === \strlen($k)){
                    $id_key_name = $k;
                    $id_key_value = $v;
                }
            }
            if(isset($id_key_name)&&$id_key_name&&isset($id_key_value)&&$id_key_value&& \count($answer)>0){
                $found_match = FALSE;
                for(\end($answer), $i=\key($answer); $i>-1;$i--){
                    if($answer[$i]->getRowNumField()===$id_key_name&&$answer[$i]->getRowNum()===$id_key_value){
                        $found_match = TRUE;
                        for($new_record->reset(); $new_record->next();){
                            $current_value = $answer[$i]->item($clm = $new_record->key());
                            $answer[$i]->addend($clm, $cell = \array_merge($current_value,$new_record->current()));
                        }
                    }
                }
                if(!$found_match)
                    $answer[] = $new_record;
            } else {
                $answer[] = $new_record;
            }
        }
        if(\count($answer)===0)
            $answer[] = new \Segment\utilities\Record ($call_id);
        return $answer;
    }
}