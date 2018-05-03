<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class DbToRecordsCallable implements \Segment\Controller\Controller
{

    /**
     * Returns a Closure that receives a scalar array of non-scalar arrays from database output and converts them to a scalar array of \Segment\utilities\Record objects.
     * To be bound to a \Segment\Controller\Controller object.
     * @return \Closure
     * @var $this \Segment\Controller\Controller
     */
    public static function getDbToRecordCallable()
    {
        $add_main_cluster = self::getAddMainCluster();
        $process_id_cluster = self::getProcessIdCluster();
        $process_nonid_cluster = self::getProcessNonIdCluster();
        $combine_rows = self::getCombineRows();
        $answer = function() use ($add_main_cluster, $process_id_cluster, $process_nonid_cluster, $combine_rows) 
        {
            // get Records from wrapper
            /* $records is a scalar array of Record objects */$records = $this->getRecords();
            /* The $ctrlr_id is the Controller identification derived from the HTTP REST function */
            $ctrlr_id = $this->getId();
            // Remove Controller records so only revised will be there
            $temp_source_records = \array_merge($records, []);
            foreach ($records as $key => $value) {
                $this->unsetRecord($key);
            }
            \sort($records, \SORT_NATURAL);
            $main_cluster = [
                $ctrlr_id => []
            ];
            $other_clusters = [
                []
            ];
            $incum_record_id;
            $incumbent_key;
            $clusters;

            // identify call clusters
            for ($i = 0, $max = count($records); $i < $max; $i++) {
                $record = $records[$i];

    // determine if primary call record
                $clusters = $record->getId() === $ctrlr_id ? $clusters = &$main_cluster : $clusters = &$other_clusters;

    // prepare '_id' clusters
                if ($record->getRowNum() !== FALSE) {
                    if (isset($clusters[$record->getId()][$record->getRowNum()])) {
                        $record = $this->combineRows($clusters[$record->getId()][$record->getRowNum()], $record);
                    }
                    $clusters[$record->getId()][$record->getRowNum()] = $record;

    // prepare non-id clusters
                } else if ($record->count === 1) {
                    foreach ($record as $column => $cell) {
                        if (isset($clusters[$record->getId()][$column]))
                            $record = $this->combineRows($clusters[$record->getId()][$column], $record);
                        $clusters[$record->getId()][$column] = $record;
                        break;
                    }
                }
            }
            $add_main_cluster($this, ...$main_cluster[$ctrlr_id]);
            foreach ($other_clusters as $other_ids => $cluster_array) {
                foreach ($cluster_array as $field => $record) {
                    if (is_int($field))
                        $process_id_cluster($this, ...$cluster_array);
                    else
                        $process_nonid_cluster($this, ...array_values($cluster_array));
                }
            }
        };
        
        
        return $answer;
    }


    protected static function getCombineRows()
    {
        return function (\Segment\utilities\Record $rec_1, \Segment\utilities\Record $rec_2)
        {
            foreach($rec_1 as $column => $cell){
                if(count(array_diff_assoc($cell,$rec_2[$column]))!==0||count(array_diff_assoc($rec_2[$column]), $cell)!==0)
                    $rec_1->addend($column, $cell + $rec_2[$column]);
            }
            return $rec_1;
        };
    }
    protected static function getAddMainCluster()
    {
        return function (\Segment\Controller\Controller $self, \Segment\utilities\Record ...$cluster)
        {
            foreach ($cluster as $record) {
                $self->setRecord($record, $record->getId());
            }
            
        };
    }

    protected static function getProcessIdCluster()
    {
        return function(\Segment\Controller\Controller $self, \Segment\utilities\Record ...$cluster)
        {
            /* scalar array of Record objects */$records = $self->getRecords();
            $sample_main_rec = $records[0];
            \end($cluster);
            for ($i = \key($cluster); $i > -1; $i--) {
                $non_main_record = $cluster[$i];
                if(\count($records)===0){
                    $self->setRecord($non_main_record, 0);
                } else {
                    foreach ($records as $id => $a_main_record) {
                        /* Need to verify both that the row_num_name and row_num match */
                        if ($sample_main_rec->getRowNumField() === $non_main_record->getRowNumField() &&
                                $sample_main_rec->getRowNum() === $non_main_record->getRowNum()) {
                            for ($non_main_record->reset(),
                                        $clmn = $non_main_record->key(),
                                        $cell = $non_main_record->current(),
                                        $first_time = $non_main_record->valid();
                                $first_time || $non_main_record->next();
                                $first_time = $first_time ? FALSE : $first_time,
                                        $clmn = $non_main_record->key(),
                                        $cell = $non_main_record->current(),
                                        $a_main_record->addend($clmn, $cell)
                            ) // consolidated FOR statement
                            $self->setRecord($a_main_record, $id);
                        }
                    }
                }
            }
        };
    }

    protected static function getProcessNonIdCluster()
    {
        /**
         * Adds every Record in cluster array to every Record in Controller with the
         *         cluster call ID as the index.
         * @param \Segment\Controller\Controller $self The bound $this.
         * @param \Segment\utilities\Record $cluster Variable-length variable. Records all same call ID
         */
        return function (\Segment\Controller\Controller $self, \Segment\utilities\Record ...$cluster)
        {
            /* scalar array of Record objects */$records = $self->getRecords();
            \end($cluster);
            for ($i = key($cluster); $i > -1; $i--) {
                $non_id_record = $cluster[$i];
                $non_id_type = $non_id_record->getId();
                $temp_array = explode('_', $non_id_type);
                $first_entry = array_slice($temp_array, 0, 1);
                unset($temp_array[0]);
                \array_walk($temp_array, '\Segment\utilities\Utilities::callableUCFirst');
                $potential_field = implode('', array_merge($first_entry, $temp_array));
                if(\count($records)===0){
                    if (isset($incum_rec->$potential_field)) {
                        $non_id_record->reset();
                        $incum_rec->$potential_field = $non_id_record->current();
                    } else {
                        $self->setRecord($non_id_record, 0);
                    }
                } else {
                    foreach ($records as $k => $incum_rec) {
                        if (isset($incum_rec->$potential_field)) {
                            $non_id_record->reset();
                            $incum_rec->$potential_field = $non_id_record->current();
                        } else {
                            for ($non_id_record->reset(),
                                        $clmn = $non_id_record->key(),
                                        $cell = $non_id_record->current(),
                                        $first_time = $non_id_record->valid();
                                $first_time || $non_id_record->next();
                                $first_time = $first_time ? FALSE : $first_time,
                                        $clmn = $non_id_record->key(),
                                        $cell = $non_id_record->current(),
                                        $incum_rec->addend($clmn, $cell)
                            ){} // consolidated FOR statement
                        }
                        $self->setRecord($incum_rec, $k);
                    }
                }
            }
        };
    }

    /*
      private function combineRows(Record $rec_1, Record $rec_2)
      {
      foreach($rec_1 as $column => $cell){
      if(count(array_diff_assoc($cell,$rec_2[$column]))!==0||
      count(array_diff_assoc($rec_2[$column]), $cell)!==0)
      $rec_1->addend($column, $cell + $rec_2[$column]);
      }
      return $rec_1;
      }

      /**
     * Updates single row of records, based on $postition, in $this->rows
     * @throws {InvalidArgumentException}
     * @param {Array<Object<string,(string|integer|float|Boolean|array)>>} $cluster
     * @param {integer} $position

      private function processCluster(array $cluster, $position)
      {
      $keys = array();
      foreach($cluster[key($cluster)] as $k=>$v){
      $keys[] = $k;
      }
      $row = array();
      $name = $cluster[key($cluster)]->getId();
      $first_time_through = TRUE;
      end($cluster);
      for($i=key($cluster);$i>-1;$i--){
      foreach($cluster[$i] as $column => $cell){
      if(!isset($row[$name . $column]))
      $row[$name . $column] = array();
      $row[$name . $column][] = $cell;
      }
      }
      $r = new \Segment\utilities\Record($name);
      foreach ($row as $key => $value) {
      $value = array_unique($value);
      $r->addend($key, $value);
      }
      $this->setRecord($r, $position);
      }

      /**
     * Updates all rows of records in $this->rows
     * @param array $rows
     * @param string $name name of ModelCall to be concatenated to beginning of index
     * @throws {InvalidArgumentException}

      private function consolidateRows(array $rows, $name)
      {
      Utilities::areArgumentsValid(__NAMESPACE__ . __CLASS__, __METHOD__, func_get_args());
      $new_records = array_fill(0, $rows[key($rows)]->count(), array());
      $columns = array_fill(0, $rows[key($rows)]->count(), array());
      foreach($rows[key($rows)] as $cname => $cvalue){
      $columns[$name . $cname] = array();
      }
      end($rows);
      for($i=key($rows);$i>-1;$i--){
      $row = $rows[$i];
      foreach($row as $clmn => $cell){
      $columns[$name . $clmn][] = $cell;
      }
      }
      foreach($columns as $key => $value){
      $new_records[$key] = array_unique($value);
      }
      $records = $this->getRecords();
      end($records);
      for($i=key($records);$i>-1;$i--){
      foreach($new_records as $k => $set){
      $records[$i]->addend($k, $set);
      }
      $this->setRecord($records[$i], $i);
      }
      }
     */

    public function execute()
    {
        $this->invoke();
    }

    public function getId()
    {
        return $this->wrapper->getId();
    }

    public function getRecords()
    {
        return $this->wrapper->getRecords();
    }

    public function getRest()
    {
        return $this->wrapper->getRest();
    }

    public function getUser()
    {
        return $this->wrapper->getUser();
    }

    public function getViewClass()
    {
        return $this->wrapper->getViewClass();
    }

    public function isAuthorizationNeeded()
    {
        return $this->wrapper->isAuthorizationNeeded();
    }

    public function setRecord(\Segment\utilities\Record $record, $index = NULL)
    {
        $this->wrapper->setRecord($record, $index);
    }

    public function setRest(\Segment\utilities\Rest $rest)
    {
        $this->wrapper->setRest($rest);
    }

    public function unsetRecord($index)
    {
        $this->wrapper->unsetRecord($index);
    }

    public function getDescription($table = FALSE)
    {
        return $this->wrapper->getDescription($table);
    }

}