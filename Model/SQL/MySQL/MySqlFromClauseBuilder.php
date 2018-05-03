<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlFromClauseBuilder implements \Segment\Model\FromClauseBuilder
{
    public function __construct(){    }
    
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
    
    /**
     * Composes FROM clause string based on the tables in scope for query
     * @param \Segment\Model\Table $tables Array of Table objects, the first, index zero, being the primary.
     * @return string
     */
    public function getClause(\Segment\Model\Table ...$tables)
    {
        $answer = ''; $temp = array();
        if (count($tables)>0) {
            $primary_table = $tables[0]->getTable();
            if (count($tables)>1) {
                $links = json_decode(__DB_TABLE_LINK__, TRUE)['links'];
                $connections = json_decode(__DB_TABLE_LINK__, TRUE)['connections'];
                foreach ($links as $key => $value) {
                    $combined_links[json_encode($value)] = $connections[$key];
                }
                uasort($combined_links, "\Segment\utilities\Utilities::callableCollectionCountSort");
                
                $test_tables = \Segment\utilities\Utilities::arrayCopy($tables);
                $unmatched_tables = array();
                while(count($test_tables)>1){
                    foreach($combined_links as $connection => $link){
                        if($test_tables===$link||(count($test_tables)===2&&first($test_tables)->getTalbe()===first($link)
                                &&end($test_tables)->getTable()===end($link))){
                            $answer .= $this->processConnection(json_decode($connection, TRUE));
                            $test_tables = array();
                        }
                    }
                    if(count($test_tables)===2){
                        $index = array_search($test_tables[1]->getTable(), $links, TRUE);
                        if($index!==FALSE){
                            $temp = array_values(rksort($test_tables));
                            foreach($combined_links as $c => $l){
                                if($temp===$l ||
                                        (first($temp)->getTable()===first($l) && end($temp)->getTable()===end($l))
                                        ){
                                    $answer .= $this->processConnection($c);
                                    $primary_table = $temp[0]->getTable();
                                    unset($temp[1]);
                                    $test_tables = $temp;
                                }
                            }
                        }
                        if(isset($test_tables[1])){
                            $answer .= ", " . $test_tables[1]->getTable();
                            unset($test_tables[1]);
                        }
                    }
                    if(count($test_tables)>1){
                        $unmatched_tables = array_merge(array_slice($test_tables, -1, 1, TRUE), $unmatched_tables);
                        unset($test_tables[count($test_tables)-1]);
                    }
                    if(count($test_tables)<2&&count($unmatched_tables)>0){
                        $test_tables = array_merge(array($primary_table), $unmatched_tables);
                        $unmatched_tables = array();
                    } else
                        break;
                }
            }
            return $primary_table . $answer;
        }
        return $answer;
    }
    
    /**
     * Makes FROM clause string inner join connections in MySQL format.
     * @param array<string> $connection Array of strings in the following format:
     *     [DB_NAME1, TABLE_NAME1, DB_NAME2, TABLE_NAME2, COLUMN_NAME2, DB_NAME3, TABLE_NAME3, COLUMN_NAME3]
     *     So that equivalency will be sought between a first and second table based on the second column.
     *     Thus the the second column must be present in both tables.
     * @return string
     */
    private function processConnection($connection)
    {
        $incumbent_db = $connection[0];
        $incumbent_table = $connection[1];
        $answer = '';
        for($i=2, $max = count($connection);$i<$max;$i=$i+3){
            $db = $connection[$i];
            $table = $connection[$i+1];
            $column = $connection[$i+2];
            if($incumbent_db===$db){
                $answer .= " INNER JOIN {$table}.{$column} ON {$incumbent_table}.{$column} = "
                    . " {$table}.{$column}";
                $incumbent_db = $db;
                $incumbent_table = $table;
            }
        }
        return $answer;
    }
}