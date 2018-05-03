<?php
namespace Segment\Model\production;
session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class DbTableConsolidatedRows
{
    private $rows = array();
    public function consolidate(array $table, $limit = 0)
    {
        //if(count($table)!==$limit){
            $incumbent_key;
            $cluster = array();
            $position = 0;
            for($max = count($table); $max>-1; ){
                --$max;
                if(!isset($incumbent_key)||$incumbent_key===$table[$max][key($table[$max])]){
                    $cluster[] = $table[$max];
                    $incumbent_key = $table[$max][key($table[$max])];
                } else {
                    $incumbent_key = $table[$max][key($table[$max])];
                    if(count($cluster)===1)
                        $this->rows[$position++] = $cluster;
                    else if(count($cluster)>1){
                        $this->processCluster($cluster, $position++);
                        $cluster = [
                            $table[$max]
                        ];
                    }
                }
            }
        /*} else
            $this->rows = $table;*/
            return \Segment\utilities\Utilities::arrayCopy($this->rows);
    }
    
    private function processCluster(array $cluster, $position)
    {
        $keys = array_keys($cluster[0]);
        $row = array();
        foreach($keys as $val){
            $row[$val] = array();
        }
        $first_time_through = TRUE;
        for($i=count($cluster) - 1;$i>-1;$i--){
            if($first_time_through){
                $cluster[$i] = array_reverse($cluster[$i]);
                $first_time_through = FALSE;
            }
            for($clmn = count($cluster[0])-1; $clmn>-1; --$clmn){
                list($column, $cell) = each($cluster[$i]);
                $row[$column][] = $cell;
            }
        }
        foreach ($row as $key => $value) {
            $value = array_unique($value);
            if(count($value)===1)
                $row[$key] = $value[0];
            else
                $row[$key] = $value;
        }
    }
}