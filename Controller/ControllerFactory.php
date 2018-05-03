<?php

namespace Segment\Controller\production;


class ControllerFactory
{
    
    const OUTPUT_HTML = 'html';
    const OUTPUT_ANDROID = 'android';
    const OUTPUT_IOS = 'ios';
    
    /**
     * @param string $output The platform the request is for, e.g. HTML, ANDROID, iOS
     * @param \Segment\Controller\production\Security $input The security object the client's arguments are wrapped in.
     * @return \Segment\Controller\Controller Factory generated instance of Controller
     * @throws  \InvalidArgumentException
     */
    public static function getInstance($output, $input)
    {
        //\Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        error_log("Inside " . __METHOD__);
        if(!static::isOutput($output))
            throw new \InvalidArgumentException(__METHOD__.' expects zeroeth argument, base 0, '
            . 'to be of one of the OUTPUT ControllerFactory constants. Provided: '
            .print_r($output, TRUE));
        /**
         * @var string $rest_type The REST type, e.g. GET, PUT, POST, DELETE, OPTIONS
         */
        $rest_type = $input->getRestType();

        $instance = class_exists('\Segment\Controller\production\TestController') ? new \Segment\Controller\production\TestController($input) : new ControllerShell($input);
        /*extends ControllerAbstract
                {
                    use \Segment\utilities\FunctionSetter;
                    private $scrubbed_rest;
                    protected $namespace = __NAMESPACE__;

                    public function __construct(\Segment\Controller\production\Security $security)
                    {
                        parent::__construct($security);
                    }

                    /**
                     * @return Segment success returns Segment, else FALSE
                     * @var $this \Segment\Controller\production\ControllerAbstract
                     /
                    public function execute()
                    {
                        $answer = FALSE;
                        //$this->scrubbed_rest = $this->prepareRest();
                        if($this->crest->execute())
                            $answer = $this->crecords->execute();
                        return $answer;
                    }

                    /**
                     * @return \Segment\Controller\RestRequest
                     * @var $this \Segment\Controller\production\ControllerAbstract
                     /
                    public function getRest()
                    {
                        if(is_null($this->scrubbed_rest)||!isset($this->scrubbed_rest))
                            //$this->prepareRest();
                        return $this->scrubbed_rest;
                    }

                    /**
                     *
                     * @param string $func_name
                     * @param callable|\Closure $function
                     /
                    public function setInstanceFunction($func_name, $function)
                    {
                        if(!is_string($func_name)||(!is_callable($function)&&!is_a($function, '\Closure'))){
                            if(!is_string($func_name))
                                throw new \InvalidArgumentException(__METHOD__
                                        . " expects first argument to have key of type text string. Provided: "
                                        . print_r($func_name, TRUE));
                            else
                                throw new \InvalidArgumentException(__METHOD__
                                        . " expects first argument to have value of type Callable. Provided: "
                                        . print_r($function, TRUE));
                        }
                        $ctrlr_rest = new \ReflectionClass("\Segment\Controller\ControllerRestReceipt");
                        $ctrlr_record = new \ReflectionClass("\Segment\Controller\ControllerRecordReceipt");
                        if(isset($this->crest)&&$ctrlr_rest->hasMethod($func_name)){
                            $property_n = \Segment\utilities\Utilities::convertFunctionNameToProperty($func_name);
                            $this->crest->$property_n = $function;
                        } else if(isset($this->crecord)&&$ctrlr_record->hasMethod($func_name)){
                            $property_n = \Segment\utilities\Utilities::convertFunctionNameToProperty($func_name);
                            $this->crecord->$func_name = $function;
                        } else {
                            parent::setInstanceFunction($func_name, $function);
                        }
                    }

                    public function __call($name, $arguments)
                    {
                        if(function_exists(\Segment\Controller\ControllerRecordReceipt::$name)){
                            return $this->crecords->$name(...$arguments);
                        } else if(function_exists(\Segment\Controller\ControllerRestReceipt::$name)){
                            return $this->crest->$name(...$arguments);
                        } else
                            return parent::$name(...$arguments);
                    }

                    public function prepareRest()
                    {
                        $type = $this->security->getRestType();
                        $type = ControllerFactory::getRestIdType($type, $this->rest);
                        if(class_exists($temp = __NAMESPACE__ . '\\' .ucfirst(strtolower($type)) . 'HttpRequest')){
                            $type = $temp;
                        } else {
                            $class_n_getter = new class (){
                                use \Segment\utilities\AbstractClassNamesGetter;
                            };
                            $type = $class_n_getter->getClassName($temp, '');
                        }
                        $x_request = $this->rest->getValue('x');
                        if(is_string($x_request)){
                            try{
                                $x_request = json_decode($x_request, TRUE);
                            } catch (\Exception $ex) {
                                error_log(__METHOD__." attempted json_decode()"
                                        . " on {$x_request}");
                                        $x_request = [];
                            }
                        } else {
                            $x_request = [];
                        }
                        reset($x_request);
                        return $answer = new $type(current($x_request));
                    }

                };*/
        /**$c_rest_r_n = $input->getClassName('ControllerRestReceipt', __NAMESPACE__);
        $c_rec_r_n = $input->getClassName('ControllerRecordReceipt', __NAMESPACE__);

        $instance->setInstanceFunction('execute', self::getControllerExecute());
        
        $temp_call_model_func = self::getCallModelFunc();
        $temp_mc_names_func = self::getMCNamesFunc();
        $temp_mc_args_func = self::getMCArgsFunc($rest_type, $input->getRest());
        $temp_instantiate_mc_func = self::getInstantiateMCFunc();
        $controller_rest_receipt = new $c_rest_r_n(current($temp_call_model_func), current($temp_mc_names_func), current($temp_mc_args_func), current($temp_instantiate_mc_func), $instance);
        
        $temp_organize_records = self::getOrganizeRecords();
        $temp_seg_type_func = self::getSegTypeFunc();
        $temp_init_seg_func = self::getInitSegFunc();
        $controller_record_receipt = new $c_rec_r_n(current($temp_organize_records), current($temp_seg_type_func), current($temp_init_seg_func), $instance);

        $instance->addControllerRestReceipt($controller_rest_receipt);
        $instance->addControllerRecordReceipt($controller_record_receipt);**/
        return $instance;
    }
    
    /**
     * @param string $output The platform the request is for
     * @return boolean Whether the platform is recognized
     * @throws \InvalidArgumentException
     */
    public static function isOutput($output)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        switch (trim(strtolower($output))){
            case 'html':
            case 'android':
            case 'ios':
                return TRUE;
            default :
                return FALSE;
        }
    }
    
    /**
     * @param string $rest_type The REST type
     * @return boolean Whether the REST type is recognized
     * @throws \InvalidArgumentException
     */
    public static function isRestType($rest_type)
    {
        if(!is_string($rest_type))
            throw new \InvalidArgumentException(__METHOD__ . ' '
                    . 'requires first argument to be string of characters. Provided: '
                    . print_r($rest_type, TRUE));
        switch(trim(strtoupper($rest_type))){
            case 'GET':
            case 'PUT':
            case 'POST':
            case 'DELETE': return TRUE;
            default:
                return FALSE;
        }
    }

    
    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the getModelCallArgument function.
     * @param string $rest_type the REST call for this operation
     * @param \Segment\utilities\Rest $rest
     * @return array<\Closure> Implementation of the getModelCallArgument function. The key is 'getModelCallArgument'
     * @throws \InvalidArgumentException
     */
    private static function getMCArgsFunc($rest_type = 'GET', \Segment\utilities\Rest $rest)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $answer;
        $request_type = self::getRestIdType($rest_type, $rest);
        switch(strtoupper($request_type)){
            case 'SEARCH':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = function()
                {
                    $answer = array();
                    if($this->isAuthorizationNeeded()){
                        $answer = function(){ /* An empty \Closure */};
                    } else {
                        $x = $this->getRest();
                        $answer = new SearchHttpRequest($n = $x->hasKey('x') ? $x->getValue('x') : []);
                    }
                    return $answer;
                };
                break;
            case 'WILD':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = function()
                {
                    $answer = array();
                    if($this->isAuthorizationNeeded())
                        return $answer;
                    $x = $this->getRest();
                    $answer = new WildHttpRequest($n = $x->hasKey('x') ? $x->getValue('x') : []);
                    return $answer;
                };
                break;
            case 'POST':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = function()
                {
                    $answer = array();
                    if($this->isAuthorizationNeeded())
                        return $answer;
                    $x = $this->getRest();
                    $answer = new \Segment\utilities\PostHttpRequest($n = $x->hasKey('x') ? $x->getValue('x') : []);
                    return $answer;
                };
                break;
            case 'PUT':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = function()
                {
                    $answer = array();
                    if($this->isAuthorizationNeeded())
                        return $answer;
                    $x = $this->getRest();
                    $answer = new \Segment\utilities\PutHttpRequest($n = $x->hasKey('x') ? $x->getValue('x') : []);
                    return $answer;
                };
                break;
            case 'DELETE':
                /**
                 * @return \Segment\Controller\RestRequest
                 * @var \Segment\Controller\Controller $this
                 */
                $answer = function()
                {
                    $answer = array();
                    if($this->isAuthorizationNeeded())
                        return $answer;
                    $x = $this->getRest();
                    $answer = new \Segment\utilities\DeleteHttpRequest($n = $x->hasKey('x') ? $x->getValue('x') : []);
                    return $answer;
                };
                break;
            default:
                $answer = new \Segment\utilities\SearchHttpRequest([]);
                break;
        }
        return ['getModelCallArgument' => $answer];
    }

    
    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the organizeRecords function.
     * @return array<\Closure> Implementation of the organizeRecords function. The key is 'organizeRecords'
     */
    private static function getOrganizeRecords()
    {
        $temp = DbToRecordsCallable::getDbToRecordCallable();
        
        /**
         * @returns ArrayAccess<\Segment\utilities\Record>
         * @var \Segment\Controller\Controller $this
         */
        return ['organizeRecords' => $temp];
    }

    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the instantiateModelCall function.
     * @return array<\Closure> Implementation of the instantiateModelCall function. The key is 'instantiateModelCall'
     */
    protected static function getInstantiateMCFunc()
    {
        /**
         * @param callable $model_call
         * @param \Segment\Controller\RestRequest $args
         * @return \Segment\Model\production\ModelCaller
         * @var \Segment\Controller\Controller $this
         */
        return [
                'instantiateModelCall' => function(callable $model_call, \Segment\Controller\RestRequest $args)
                {
                    $answer = new $model_call($args);
                    return $answer;
                }
            ];
    }

    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the getModelCallNames function.
     * @return array<\Closure> Implementation of the getModelCallNames function. The key is 'getModelCallNames'
     */
    protected static function getMCNamesFunc()
    {
        /**
         * @return \ArrayAccess<callable> queue of ModelCaller string names
         * @throws \InvalidArgumentException
         * @var \Segment\Controller\Controller $this
         */
        return [
                'getModelCallNames' => function()
                {
                    $id = $this->getId();
                    $auth_needed = $this->isAuthorizationRequired();
                    $answer = new \SplDoublyLinkedList();
                    if($auth_needed){
                        $id .= "NonSecure";
                        $answer->push($id);
                        return $answer;
                    }
                    $array_rows = file(__MODEL_CALL_NAME_TABLE__);
                    $family_found = FALSE;
                    for($i = count($array_rows)-1;
                            $i>-1;
                            --$i){
                        $row = str_getcsv($array_rows[$i]);
                        if($id === $row[0]){
                            $family_found = TRUE;
                            $answer->push($row[1]);
                        } else if($family_found)
                            return $answer;
                    }
                }
            ];
    }

    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the callModel function.
     * @return array<\Closure> Implementation of the callModel function. The key is 'callModel'
     */
    protected static function getCallModelFunc()
    {
        /**
         * Calls passed ModelCallOrchestrator invocable with supplied $args. Returned
         *         Record is added to Controller wrapper.
         * @param \Segment\Controller\ModelCallOrchestrator $call
         * @param \Segment\Controller\RestRequest $args Associative array, object with string keys.
         * @var \Segment\Controller\Controller $this
         */
        return [
                'callModel' => function(
                        \Segment\Controller\ModelCallOrchestrator $call, \Segment\Controller\RestRequest $args
                )
                {
                    $records = $call->execute();
                    reset($records);
                    for($i = 0, $max = count($records);
                            $i<$max;
                            $i++){
                        $this->setRecords($records[$i]);
                    }

                }
            ];
    }

    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the getSegmentType function.
     * @return array<\Closure> Implementation of the getSegmentType function. The key is 'getSegmentType'
     */
    protected static function getSegTypeFunc()
    {
        /**
         * @return \Segment\View\Segment
         * @var \Segment\Controller\Controller $this
         */
        return [
                'getSegmentType' => function()
                {
                    $name = $this->getId() . "Segment";
                    return new $name();
                }
            ];
    }

    /**
     * Static function to return a function to add to an object, per the Decorator pattern.
     *     Called to get the initializeSegment function.
     * @return array<\Closure> Implementation of the initializeSegment function. The key is initializeSegment
     */
    protected static function getInitSegFunc()
    {
        /**
         * Initializes Segment with Records from business logic
         * @param \Segment\Controler\Segment $segment passed by value
         * @var \Segment\Controller\Controller $this
         * @return \Segment\View\Segment
         */
        return [
                'initializeSegment' => function(\Segment\View\Segment $segment)
                {
                    $records = $this->getRecords();
                    foreach($records as $num => $record){
                        $segment->add($num, $record);
                    }
                    return $segment;
                }
            ];
    }

    /**
     * Returns the string name of the request type.
     * @param string $rest_type The REST request method. Default: GET.
     * @param \Segment\utilities\Rest $rest An object representing the parsed REST query string.
     * @return string
     */
    public static function getRestIdType($rest_type = 'GET', \Segment\utilities\Rest $rest)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $answer = 'SEARCH';
        switch(strtoupper($rest_type)){
            case 'GET':
                $id = $rest->getId();
                if(empty($id)||(strpos($id, '_search')&&strpos($id, '_search')+7 === strlen($id))||
                        (strpos($id, '_field_set')&&strpos($id, '_field_set')+10 === strlen($id))||
                        (strpos($id, '_field_count')&&strpos($id, '_field_count')+12 === strlen($id))||
                        (strpos($id, '_field_avg')&&strpos($id, '_field_avg')+10 === strlen($id))||
                        (strpos($id, '_field_mode')&&strpos($id, '_field_mode')+11 === strlen($id))||
                        (strpos($id, '_field_median')&&strpos($id, '_field_median')+13 === strlen($id))||
                        (strpos($id, '_field_firstq')&&strpos($id, '_field_firstq')+10 === strlen($id))||
                        (strpos($id, '_field_thirdq')&&strpos($id, '_field_thirdq')+10 === strlen($id))){
                    $answer = 'SEARCH';
                } else if(strpos($id, '_wild')&&strpos($id, '_wild')+6 === strlen($id)){
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
     * @return \Segment\utilities\Record database records
     * @var \Segment\Controller\Controller $this
     */
    public static function getControllerExecute()
    {
        error_log(__METHOD__ . " fetching Controller's execute()");
        return function(){
            $__answer = FALSE;
            //new \ReflectionMethod($__answer, __METHOD__);
            //$this->scrubbed_rest = $this->prepareRest();
            if($this->crest->execute())
                $__answer = $this->crecords->execute();
            return $__answer;
        
        };
    }
}