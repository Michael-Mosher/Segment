<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class TestController extends \Segment\Controller\ControllerAbstract
{
    /**
     * Returns arguments in a \Segment\Controller\RestRequest object for request to Model.
     * @param string $request_method
     * @param \Segment\Controller\production\Rest $x
     * @return \Segment\Controller\RestRequest
     */
    public function getModelCallArgs($request_method = 'GET', \Segment\utilities\production\Rest $x)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $answer;
        
        switch(strtoupper($request_method)){
            case 'SEARCH':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = $this->isAuthorizationNeeded() ? new class extends \Segment\Controller\RestRequest{
                    public function next(){}
                    public function rewind(){}
                    public function get(){ return new class implements \Segment\Controller\Parameter{}; }
                }
                        : new SearchHttpRequest(
                                $n = is_a($x,
                                        '\Segment\utilities\production\Rest', TRUE
                                        ) && $x->hasKey('args')
                                ? is_string($x->getValue('args'))&& strcmp(substr($x->getValue('args'),0,1),"{")===0
                                        ? json_decode($x->getValue('args'), TRUE)
                                        : $x->getValue('args')
                                : []
                            );
                break;
            case 'WILD':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = $this->isAuthorizationNeeded() ? new class extends \Segment\Controller\RestRequest{
                    public function next(){}
                    public function rewind(){}
                    public function get(){ return NULL; }
                }
                        : new WildHttpRequest($n = is_a($x,
                                        '\Segment\utilities\production\Rest', TRUE
                                        ) && $x->hasKey('args')
                                ? is_string($x->getValue('args'))&& strcmp(substr($x->getValue('args'),0,1),"{")===0
                                        ? json_decode($x->getValue('args'), TRUE)
                                        : $x->getValue('args')
                                : []);
                break;
            case 'POST':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = $this->isAuthorizationNeeded() ? new class extends \Segment\Controller\RestRequest{
                    public function next(){}
                    public function rewind(){}
                    public function get(){ return NULL; }
                }
                        : new PostHttpRequest(($n = is_a($x,
                                        '\Segment\utilities\production\Rest', TRUE
                                        ) && $x->hasKey('args')
                                ? is_string($x->getValue('args'))&& strcmp(substr($x->getValue('args'),0,1),"{")===0
                                        ? json_decode($x->getValue('args'), TRUE)
                                        : $x->getValue('args')
                                : []) + $_POST);
                break;
            case 'PUT':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = $this->isAuthorizationNeeded() ? new class extends \Segment\Controller\RestRequest{
                    public function next(){}
                    public function rewind(){}
                    public function get(){ return NULL; }
                }
                        : new PutHttpRequest(($n = is_a($x,
                                        '\Segment\utilities\production\Rest', TRUE
                                        ) && $x->hasKey('args')
                                ? is_string($x->getValue('args'))&& strcmp(substr($x->getValue('args'),0,1),"{")===0
                                        ? json_decode($x->getValue('args'), TRUE)
                                        : $x->getValue('args')
                                : []) + $_POST);
                break;
            case 'DELETE':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = $this->isAuthorizationNeeded() ? new class extends \Segment\Controller\RestRequest{
                    public function next(){}
                    public function rewind(){}
                    public function get(){ return NULL; }
                }
                        : new DeleteHttpRequest(($n = is_a($x,
                                        '\Segment\utilities\production\Rest', TRUE
                                        ) && $x->hasKey('args')
                                ? is_string($x->getValue('args'))&& strcmp(substr($x->getValue('args'),0,1),"{")===0
                                        ? json_decode($x->getValue('args'), TRUE)
                                        : $x->getValue('args')
                                : []) + $_POST);
                break;
            default:
                $answer = new SearchHttpRequest([]);
                break;
        }
        return $answer;
    }

    
    /**
     * @returns ArrayAccess<\Segment\utilities\Record>
     * @var \Segment\Controller\Controller $this
     */
    public function organizeRecords(\Segment\utilities\Observer ...$observers)
    {
        $temp = DbToRecordsCallable::getDbToRecordCallable();
        $record_organizer = \Closure::bind($temp, $this);
        $array_of_rec = $record_organizer();
        foreach($observers as $observer){
            for($i = $array_of_rec->length -1; $i>-1; $i--){
                $array_of_rec[$i]->register($observer);
            }
        }
    }

    /**
     * @param string $model_call Class name of \Segment\Controller\ModelCallOrchestrator to be instantiated
     * @param \Segment\Controller\RestRequest $args
     * @return \Segment\Controller\ModelCallOrchestrator
     * @var \Segment\Controller\Controller $this
     */
    public function instantiateModelCall(string $model_call, \Segment\Controller\RestRequest $args, \Segment\utilities\Observer ...$observers)
    {
        $mc_name = $this->security->getClassName($model_call, __CONTROLLER_PRODUCTION_NS__);
        $answer = new $mc_name($args, $this->rest);
        foreach ($observers as $key => $observer) {
            $answer->register($observer);
        }
        return $answer;
    }

    /**
     * @return \ArrayAccess<callable> queue of ModelCaller string names
     * @throws \InvalidArgumentException
     * @var \Segment\Controller\Controller $this
     */
    public function getModelCallNames()
    {
        $id = \Segment\utilities\Utilities::convertFunctionNameToProperty($this->getId());
        $auth_needed = $this->isAuthorizationRequired();
        $answer = new \SplDoublyLinkedList();
        if($auth_needed){
            $id .= "NonSecure";
            $answer->push($id);
            return $answer;
        }
        $array_rows = str_getcsv(__MODEL_CALL_NAME_TABLE__,"\n", '"', '"');
        array_walk($array_rows,
                function(&$v, $k){
            $v = str_getcsv($v, ',', '"', '"');
                }
        );
        $family_found = FALSE;
        foreach($array_rows as $row){
            if(isset($row[0]) && $id === $row[0]&&!empty($row[0])){
                $family_found = TRUE;
                $answer->push($row[1]);
            } else if($family_found)
                return $answer;
        }
        if($answer->isEmpty()){
            $answer->push(__DEFAULT_MODEL_CALL_ORCHESTRATOR__);
        }
        $answer->rewind();
        return $answer;
    }

    /**
     * Calls passed ModelCallOrchestrator invocable with supplied $args. Returned
     *         Record is added to Controller wrapper.
     * @param \Segment\Controller\ModelCallOrchestrator $call
     * @param \Segment\Controller\RestRequest $args Associative array, object with string keys.
     * @var \Segment\Controller\Controller $this
     */
    public function callModel(\Segment\Controller\ModelCallOrchestrator $call)
    {
        $records = $call->execute();
        if($this->getId()===''){
            $this->setId($records[0]->getId());
        }
        \reset($records);
        for($i = 0, $max = \count($records);$i<$max;$i++){
            $this->setRecord($records[$i]);
        }

    }

    /**
     * @return \Segment\View\Segment
     * @var \Segment\Controller\Controller $this
     */
    public function getSegmentType()
    {
        $name = \implode('', \array_walk(\explode('_', $this->getId()), "\Segment\utilities\Utilities::callableUCFirst")) . "Payload";
        $class_name = $this->getClassName($name, __VIEW_PRODUCTION_NS__);
        return new $class_name();
    }

    
    protected function initializeSegment(\Segment\View\Segment $segment)
    {
        $records = $this->getRecords();
        foreach($records as $num => $record){
            $segment->add($num, $record);
        }
        return $segment;
    }

    /**
     * Returns the string name of the request type.
     * @param string $rest_type The REST request method.
     * @param string $rest_id The REST function name.
     * @return string
     */
    public static function getRestIdType($rest_type, $rest_id)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $answer = 'SEARCH';
        $switch_arg = $rest_type ?? $this->security->getRestType();
        switch(strtoupper($switch_arg)){
            case 'GET':
                if(empty($rest_id)||(strpos($rest_id, '_search')&&strpos($rest_id, '_search')+7 === strlen($rest_id))||
                        (strpos($rest_id, '_field_set')&&strpos($rest_id, '_field_set')+10 === strlen($rest_id))||
                        (strpos($rest_id, '_field_count')&&strpos($rest_id, '_field_count')+12 === strlen($rest_id))||
                        (strpos($rest_id, '_field_avg')&&strpos($rest_id, '_field_avg')+10 === strlen($rest_id))||
                        (strpos($rest_id, '_field_mode')&&strpos($rest_id, '_field_mode')+11 === strlen($rest_id))||
                        (strpos($rest_id, '_field_median')&&strpos($rest_id, '_field_median')+13 === strlen($rest_id))||
                        (strpos($rest_id, '_field_firstq')&&strpos($rest_id, '_field_firstq')+10 === strlen($rest_id))||
                        (strpos($rest_id, '_field_thirdq')&&strpos($rest_id, '_field_thirdq')+10 === strlen($rest_id))){
                    $answer = 'SEARCH';
                } else if(strpos($rest_id, '_wild')&&strpos($rest_id, '_wild')+6 === strlen($rest_id)){
                    $answer = 'WILD';
                }
                break;
            case 'POST':
                $answer = 'POST';
                break;
            case 'PUT':
                $answer = 'PUT';
                break;
            case 'DELETE':
                $answer = 'DELETE';
                break;
            default:
                $answer = 'SEARCH';
                break;
        }
        return $answer;
    }
    
     /**
     * The ::execute() method for the bound Controller.
     * @var array $model_call_observers Array (scalar) of \Segment\utilities\Observer
     *         to be triggered when model call completes.
     * @var array $db_record_observers Array (scalar) of \Segment\utilities\Observer
     *         to be triggered when DB records are consolidated.
     * @return array <\Segment\utilities\Record> database records
     * @var \Segment\Controller\Controller $this
     */
    public function execute(array $model_call_observers, array $db_record_observers)
    {
        $__answer = FALSE;
        $rest = $this->getRest();
        //new \ReflectionMethod($__answer, __METHOD__);
        //$this->scrubbed_rest = $this->prepareRest();
        $mc_names = $this->getModelCallNames();
        $mc_args = $this->getModelCallArgs(
                $this->getRestIdType(
                        $this->security->getRequestType(), $x = $rest->hasKey('x') ?
                        $rest->getValue('x') :
                        \Segment\utilities\Utilities::convertClassNameToProperty(__DEFAULT_MODEL_CALL_ORCHESTRATOR__)
                ),
                $rest
        );
        $i =1;
        $first_try = TRUE;
        for($mc_names->rewind(); $first_try || $mc_names->next(); ){
            $mc_orchestrator = $this->instantiateModelCall($mc_names->current(), $mc_args);
            $mc_orchestrator->init($this->rest);
            $this->callModel($mc_orchestrator);
            $first_try = FALSE;
            $i++;
        }
        $security_record = new \Segment\utilities\Record($this->id);
        $security_record->addend('ip', [$this->getIpAddress()]);
        // browser type
        // browser version
        // session ID needs to be collected if available, omitted if not
        $this->organizeRecords($this->getRecords());
        return $this->getRecords();//$this->initializeSegment($this->getSegmentType());

    }

    /**
     * Adds Record to collection of Record in Controller.
     * @param \Segment\Model\production\Record $record
     * @param int $index
     */
    public function setRecord(\Segment\utilities\Record $record, $index = NULL)
    {
        if(is_integer($index))
            $this->records[$index] = $record;
        else
            $this->records[] = $record;
    }

    /**
     * @param integer $index
     */
    public function unsetRecord($index)
    {
        if(isset($this->records[$index]))
                unset($this->records[$index]);
    }
    
    public function prepareRest()
    {
        
    }
    
    protected function isAuthorizationRequired()
    {
        return $this->security->isAuthenticationRequired();
    }
    
    private function setId(string $request_name)
    {
        if($this->getId()===''&&!empty($request_name)){
            $this->id = $request_name;
        }
    }

}