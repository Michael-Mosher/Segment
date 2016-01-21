<?php

class RestFilters
{
    public static function scrubRestPair(&$value, $key, $invokable_descr_filter = 'DescriptionFilter')
    {
        $description = json_decode(file_get_contents(__ROOT__ . __DESCRIPTIONS__), TRUE);
        error_log('/test/controller/controller.php scrubGetPair $key: ' . print_r($key, TRUE)
                . ' $value: ' . print_r($value, TRUE));
        
        switch($key){
            case 'admin':
                if($_SERVER['REQUEST_METHOD']=='POST'||$_SERVER['REQUEST_METHOD']=='PUT'||
                    $_SERVER['REQUEST_METHOD']=='DELETE'){
                        $admin = new Admin();
                        $value = filter_var($value, FILTER_CALLBACK, array(
                            'options' => $admin
                            ));
                    } else {
                        $value = strtolower($_SERVER['REQUEST_METHOD']);
                    }
                    break;
            case 'row_current': 
            case 'row_min': $row_min = new RowMin();
                $value = (int)filter_var($value, FILTER_CALLBACK, array(
                    'options' => $row_min
                    ));
                break;
            case 'row_max': $row_max = new RowMax();
                $value = filter_var($value, FILTER_CALLBACK, array(
                    'options' => $row_max
                    ));
                break;
            case 'get_row_max':
                $value = (is_bool($value)||is_int($value)) ? $value : '';
                break;
            case 'get_row_description':
                $value = (is_bool($value)||is_int($value)) ? $value : '';
                break;
            case 'user_pref':
                if((is_string($value)&&  strtolower($value)==="true")||(is_bool($value)&&$value===TRUE)
                        ||(is_int($value)&&$value===1))
                    $value = TRUE;
                break;
            case 'authorization_status':
                if(is_integer($value))
                    $value = $value===1 ? TRUE : ($value===0 ? FALSE : $value);
                $value = is_bool($value) ? $value : '';
                break;
            case 'x': error_log('controller.php case x value: ' . print_r($value, TRUE)
                    . ' and is string: ' . print_r(is_string($value),TRUE));
                if($value==='admindescription')
                    $value = 'admin_description';
                if($value==='admingetvalues')
                    $value = 'admin_get_values';
                break;
            case 'field_set': $value = isset($description[explode('.', $value)[1]]) ? $value : '';
                break;
            case 'z': settype($value, 'string');
                $value = $value;
                break;
            default: error_log('/test/controller/controller.php ScrubGetPair case default');
                if(isset($description[$key])){
                    error_log(' $key: ' . $key . ' and $value: ' . $value);
                    $callback = new $invokable_descr_filter($description[$key]['field_data']);
                    $value = filter_var($value, FILTER_CALLBACK, array(
                        'options' => $callback
                    ));
                }  else {
                    $value = NULL;
                }
                break;
        }
    }
    
    public static function scrubRestPairLiberal(&$value, $key, $invokable_descr_filter = 'DescriptionFilter')
    {
        $description = json_decode(file_get_contents(__ROOT__ . __DESCRIPTIONS__), true);
        switch($key){
            case 'admin':
                if($_SERVER['REQUEST_METHOD']=='POST'||$_SERVER['REQUEST_METHOD']=='PUT'||
                    $_SERVER['REQUEST_METHOD']=='DELETE'){
                        $admin = new Admin();
                        $value = filter_var($value, FILTER_CALLBACK, array(
                            'options' => $admin
                            ));
                    } else {
                        $value = strtolower($_SERVER['REQUEST_METHOD']);
                    }
                    break;
            case 'column': $collection[$key] = '';
                if(is_string($value)
                        &&(is_array(json_decode($value))&&is_string(key(json_decode($value),TRUE))))
                    $collection['columns'] = $value;
                else
                    $collection['columns'] = '';
                break;
            case 'columns': if(is_string($value)
                    &&(is_array(json_decode($value))&&is_string(key(json_decode($value),TRUE))))
                    $value = $value;
            else
                $value = '';
                break;
            case 'table': $collection[$key] = '';
                if(is_string($value)&&(is_array(json_decode($value))
                        &&is_string(key(json_decode($value),TRUE))))
                    $collection['tables'] = $value;
                else
                    $collection['tables'] = '';
                break;
            case 'tables': 
                if(is_string($value)&&is_array(json_decode($value))
                        &&!Utilities::checkArrayEmpty(key(json_decode($value),TRUE)))
                    $value = $value;
                else
                    $value = '';
                break;
            case 'row_current': 
            case 'row_min': $row_min = new RowMin();
                $value = (int)filter_var($value, FILTER_CALLBACK, array(
                    'options' => $row_min
                    ));
                break;
            case 'row_max': $row_max = new RowMax();
                $value = filter_var($value, FILTER_CALLBACK, array(
                    'options' => $row_max
                    ));
                break;
            case 'get_row_max':
                $value = (is_bool($value)||is_int($value)) ? $value : '';
                break;
            case 'get_row_description':
                $value = (is_bool($value)||is_int($value)) ? $value : '';
                break;
            case 'user_pref':
                if((is_string($value)&&  strtolower($value)==="true")||(is_bool($value)&&$value===TRUE)
                        ||(is_int($value)&&$value===1))
                    $value = TRUE;
                break;
            case 'authorization_status':
                if(is_integer($value))
                    $value = $value===1 ? TRUE : ($value===0 ? FALSE : $value);
                $value = is_bool($value) ? $value : '';
                break;
            case 'x': 
                if($value==='admindescription')
                    $value = 'admin_description';
                if($value==='admingetvalues')
                    $value = 'admin_get_values';
                break;
            case 'field_set': $value = isset($description[explode('.', $value)[1]]) ? $value : '';
                break;
            case 'z': settype($value, 'string');
                $value = $value;
                break;
            default: 
                if(isset($description[$key])){
                    error_log(' $key: ' . $key . ' and $value: ' . $value);
                    $callback = new $invokable_descr_filter($description[$key]['field_data']);
                    $value = filter_var($value, FILTER_CALLBACK, array(
                        'options' => $callback
                    ));
                }  else {
                    $value = NULL;
                }
                break;
        }
    }
}

class Security
{
    private $model;
    private $rest;
    private $destination;
    private $user;
    private $view_class;
    private $token;
    private $id;
    private $osmosis_chain;
    private $requires_authentication_eval = FALSE;
    private $requires_authentication;
    
    public function __construct(Rest $rest, User $user, $request_method, $id,
            $destination = NULL, Osmosis $chain = NULL)
    {
        if(!is_string($token))
            throw new InvalidArgumentException(
                    'Osmosis constructor expects second argument to be a text string. Provided '
                    . print_r($token, true)
                    );
        if(!is_string($view_class))
            throw new InvalidArgumentException(
                    'Osmosis constructor expects fourth argument to be a text string. Provided '
                    . print_r($view_class, true)
                    );
        if(!is_string($id))
            throw new InvalidArgumentException(
                    'Osmosis constructor expects fifth argument to be a text string. Provided '
                    . print_r($id, true)
                    );
        if(!is_string($destination))
            throw new InvalidArgumentException(
                    'Osmosis constructor expects sixth argument to be a text string. Provided '
                    . print_r($destination, true)
                    );
        $this->model = new Model();
        $this->rest = $rest;
        $this->destination = $destination;
        $this->user = $user;
        $this->view_class = $view_class;
        $this->token = $token;
        $this->id = $id;
        if(isset($chain))
            $this->osmosis_chain = $chain;
    }
    
    // Determine if authentication is required
    public function isAuthenticationRequired()
    {
        if(!$this->requires_authentication_eval){
            $answer = FALSE;
            foreach($this->rest as $key => $value){
                if($key==='admin'||($key==='x'&&($value==='admin_description'||$value==='admin_get_values'))){
                    // Determine if authentication already obtained
                    $authenticated_user = $this->user->whoIs();

                    // Determine if credentials for authenticating have been provided
                    if(strlen($authenticated_user===0)){
                        if($this->rest->hasKey('user_name')&&
                                ($this->rest->hasKey('password')||$this->rest->hasKey('password1'))){

                            $uname = $this->rest->getValue('user_name');
                            $pword = $this->rest->hasKey('password') ? $this->rest->getValue('password')
                                    : $this->rest->getValue('password1');

                            // Test credentials
                            $auth_rest = new Rest([
                                'user_name' => $uname
                            ]);
                            $authentication = new Authenticate($auth_rest, $user, $view_class);
                            $db_reply = json_decode($authentication->permeate(), TRUE);
                            if(hash_equals(
                                    $db_reply[0]['password_hash'], crypt(
                                            $pword, $db_reply[0]['password_salt']
                                            )
                                    )){
                                $this->rest->setValue('authentication_status', TRUE);
                                $answer = $this->passToController(
                                    $this->rest,
                                    $this->user,
                                    $this->view_class,
                                    $this->id,
                                    $this->destination,
                                    $this->osmosis_chain
                                );
                            } else {
                                $this->rest->setValue('authentication_status', "false");
                                $this->rest->setValue('x', $this->id);
                                $answer = [
                                    'rest' => $this->rest,
                                    'user' => $this->user,
                                    'view_class' => $this->view_class
                                ];
                                sleep(0.75); // For attacks so failure has similar time to success
                            }
                        }
                    } else {
                        $this->rest->setValue('authentication_status', TRUE);
                        $answer = $this->passToController(
                            $this->rest,
                            $this->user,
                            $this->view_class,
                            $this->id,
                            $this->destination,
                            $this->osmosis_chain
                        );
                    }
                    // Is a particular view class required and does the user have it
                    /*
                     * This is where querying of user_access table would take place in more complex site
                     */
                    // Prepare and send reply
                }
            }
            $this->requires_authentication_eval = TRUE;
            $this->requires_authentication = $answer;
        } else
            $answer = $this->requires_authentication;
        return $answer;
    }
    
    private function passToController()
    {
        $view_class = $this->getViewClass($target, $session);
        $class_name = explode('_', $target);
        $class_name = array_merge(array(strtolower($request_type)), $class_name);
        Utilities::traversableArrayWalk($class_name, 'Utilities::callableUCFirst');
        $class_name = implode('', $class_name);
        $osmosis_handler = new $class_name($rest, $user, $view_class, $target);
        $query_result = $osmosis_handler->permeate();
        $answer = [
                $osmosis_handler->getId() => $query_result,
                'user' => $user,
                'view_class' => $view_class
        ] + $rest->getAssociativeArray();
        if($rest->hasKey('get_row_max')&&($rest->getValue('get_row_max')===TRUE||
                $rest->getValue('get_row_max')===1)){
            $osmosis_handler = new GetRowMax(
                    $rest, $token, $user, $view_class, 'row_max', NULL
            );
            $answer['row_max'] = $osmosis_handler->permeate();
        }
        if($rest->hasKey('get_row_description')&&($rest->getValue('get_row_description')===TRUE||
                $rest->getValue('get_row_description')===1||
                strtolower($rest->getValue('get_row_description'))==='true')){
            $osmosis_handler = new GetRowDescription(
                    $rest, $token, $user, $view_class, 'description', NULL, $osmosis_handler
            );
            $answer['description'] = $osmosis_handler->permeate();
        }
    }

    public function getRest()
    {
        return $this->rest;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function getViewClass()
    {
        return $this->view_class;
    }
    
}

interface Permeator
{
    public function permeate();
}

interface Controller
{
    // Receive security verified input

    // Respond to NON_MODEL call
    /* Response may deviate from zone to zone
     *  zone class name w/ "NonSecure" at end
     */
    
    // Process Input REST
    
    // Determine how many, and what type, model calls
    /* High level logic, meta-wrapper around input handling and worker instances
     *  and instantiation calls
     * Check database for names of classes of ModelCall
     */
    /**
     * @param {NULL}
     * @return {(Object|FALSE)} success returns Object, else FALSE
     */
    public function execute();
    
    /**
     * @param {NULL}
     * @return {string} returns string enumeration of an ID
     */
    public function getId();
    
    /**
     * @param {(string|integer|RecordKey)} $index
     * @param {Record} $record
     */
    public function setRecord($index, Record $record);
    
    /**
     * @return {array<Record>}
     */
    public function getRecords();
    
    /**
     * @param {(string|integer)} $index
     * @throws {InvalidArgumentException}
     */
    public function unsetRecord($index);
    
    /**
     * @param {Rest} $rest
     */
    public function setRest(Rest $rest);
    
    /**
     * @return {Rest}
     */
    public function getRest();
    
    /**
     * @return {User}
     */
    public function getUser();

    /**
     * @return {ViewClass}
     */
    public function getViewClass();
    
    /**
     * @return {boolean}
     */
    public function isAuthorizationNeeded();
    
    // Call Model
    
    //Package data returned from Model
    
    // Return Package
}

abstract class ControllerAbstract implements Controller
{
    private $security;
    private $id;
    private $rest;
    private $records;
    private $crest;
    private $crecords;
    /* array of Permeator, traverse calling each to fill the array of ModelOut
     *  pass by reference
     * Array for ModelOut
     * Run SegmentCreator over each ModelOut array entry
    */
    
    public function __construct(Security $security, ControllerFactory $factory)
    {
        $this->security = $security;
    }
    
    public function prepareRest();
    
    private function getSearchFields()
    {
        $id = $this->getId();
        if(!is_string($id)){
            throw new InvalidArgumentException('getSearchFields first argument must be text string.'
                    . ' First argument given ' . $id);
        }
        $answer = json_decode('__SEARCHFIELDS_' . strtoupper($id) . '__', TRUE);
        return $answer;
    }
    
    public function getRest()
    {
        return $this->rest;
    }
    
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }
    
    public function getUser()
    {
        return $this->security->getUser();
    }
    
    public function getViewClass()
    {
        return $this->security->getViewClass();
    }
    
    public function isAuthorizationNeeded()
    {
        return $this->security->requiresAuthentication();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setRecord($index, Record $record)
    {
        $this->records[$index] = $record;
    }
    
    /**
     * @param {(string|integer)} $index
     * @throws {InvalidArgumentException}
     */
    public function unsetRecord($index)
    {
        unset($this->records[$index]);
    }
    
    public function getRecords()
    {
        $this->records;
    }
}

class Osmosis extends ControllerAbstract implements FunctionSetter
{
    private $scrubbed_rest;
    private $functions = array();
    
    public function __construct(Security $security, ControllerFactory $factory)
    {
        $this->crest = new ControllerRestReceiptCMA(
                $factory->getMCArgsFunc(), $factory->getInstantiateMCFunc(), $factory->getMCallNamesFunc(),
                $factory->getCallModel(), $this
                );
        $this->crecords = new ControllerRecordReceiptCMA(
                $factory->getOrganizeRecords(), $factory->getGetSegmentType(),
                $factory->getInitializeSegment(), $this
                );
        parent::construct($security, $factory);
    }
    
    /**
     * @param {NULL}
     * @return {(Segment|FALSE)} success returns Segment, else FALSE
     * @var $this Osmosis 
     */
    public function execute()
    {
        $this->scrubbed_rest = $this->prepareRest();
        if($this->crest->execute()){
            if($this->crecords->execute())
                return $this;
            else
                return FALSE;
        } else
            return FALSE;
    }
    
    /**
     * @param {NULL}
     * @return {Rest}
     * @var $this Osmosis 
     */
    public function getRest()
    {
        if(is_null($this->scrubbed_rest)||!isset($this->scrubbed_rest))
            $this->prepareRest();
        return $this->scrubbed_rest;
    }

    /**
     * @param {Object<string, Callable> $functions
     * @param {Controller} $instance Passed by reference
     * @throws InvalidArgumentException
     */
    public function setInstanceFunctions(array $functions, Controller &$instance)
    {
        $ctrlr_rest = new ReflectionClass("ControllerRestReceipt");
        $ctrlr_record = new ReflectionClass("ControllerRecordReceipt");
        $rest_methods = $ctrlr_rest->getProperties();
        $record_methods = $ctrlr_record->getProperties();
        $max = count($rest_methods)>count($record_methods) ? count($record_methods) : count($rest_methods);
        foreach ($functions as $key => $value){
            if(!is_string($key)||!is_a($value, "Callable")){
                if(!is_string($key))
                    throw new InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__
                            . " expects first argument to have key of type text string. Provided: "
                            . print_r($key));
                if(!is_a($value, "Callable"))
                        throw new InvalidArgumentException(__CLASS__ . "::" . __FUNCTION__
                                . " expects first argument to have value of type Callable. Provided: "
                                . print_r($value));
            }
            $test = FALSE;
            for($index=$max;$index>-1;$index--){
                if(isset($instance->crest)&&isset($rest_methods[$i])&&$rest_methods[$i]['name']===$key){
                    $instance->crest->$key = $value;
                    $test = TRUE;
                    continue;
                } else if(isset($instance->crecord)&&$record_methods[$i]&&$record_methods[$i]['name']===$key){
                    $instance->crecord->$key = $value;
                    $test = TRUE;
                    continue;
                }
            }
            if(!$test){
                try{
                    $instance->functions[$key] = $value;
                } catch (Exception $e){
                    error_log($e->getMessage());
                }
            }
        }
    }
    
    public function __call($name, $arguments)
    {
        if(isset($this->functions[$name]))
            return $this->functions[$name]($arguments);
        
    }

}

abstract class ControllerRestReceipt
{
    /**
     * @return {Object<string, (string|integer|float|boolean|array)>}
     * @var {Controller} $this
     */
    public $getModelCallArgs;
    
    /**
     * @param {string} $model_call name of ModelCall class
     * @param {Object<string, (string|integer|float|boolean|array)>} $args
     * @return ({ModelCall|NULL)}
     * @var {Controller} $this
     */
    public $instantiateModelCall;
    
    /**
     * @return {ArrayAccess<string>} queue of ModelCall string names
     * @var {Controller} $this
     */
    public $getModelCallNames;
    
    /**
     * @param {ModelCall} $call
     * @var {Controller} $this
     */
    public $callModel;
    
    public function __construct(
            \callable $get_model_call_args, callable $instantiate_model_call, \callable $get_model_call_names,
            \callable $call_model, \Controller $wrapper
            );
    
    public function getId()
    {
        return $this->wrapper->getId();
    }

    public function setRecord($index, \Record $record)
    {
        $this->wrapper->setRecord($index, $record);
    }
}

class ControllerRestReceiptCMA extends ControllerRestReceipt implements Controller
{
    /**
     * these four properties to be filled with anonymous functions
     */
    public $getModelCallArgs;
    public $getModelCallNames;
    public $instantiateModelCall;
    public $callModel;

    /**
     * to be filled with Controller wrapper
     */
    private $wrapper;
    
    public function __construct(
            \callable $get_model_call_args, callable $instantiate_model_call, \callable $get_model_call_names,
            \callable $call_model, \Controller $wrapper
            )
    {
        $this->getModelCallArgs = $get_model_call_args;
        $this->instantiateModelCall = $instantiate_model_call;
        $this->getMCNamesFunc = $get_model_call_names;
        $this->callModel = $call_model;
        $this->wrapper = $wrapper;
    }

    /**
     * @return {Array<Array<Object<string, (string|integer|float|boolean|array)>>>}
     * @var $this ControllerRestReceiptCMA
     */
    public function execute()
    {
        $args = $this->getModelCallArgs();
        $mcs = $this->getModelCallNames();
        for($i=count($mcs)-1; $i>-1;$i++){
            $this->callModel($this->instantiateModelCall($mcs[$i]), $args);
        }
    }

    public function getRecords()
    {
        return $this->wrapper->getRecords();
    }

    public function isAuthorizationNeeded()
    {
        return $this->wrapper->isAuthorizationNeeded();
    }

    public function getRest()
    {
        return $this->wrapper->getRest();
    }
    
    public function setRest(\Rest $rest)
    {
        $this->wrapper->setRest($rest);
    }

    public function getUser()
    {
        return $this->wrapper->getUser();
    }

    public function getViewClass()
    {
        return $this->wrapper->getViewClass();
    }
    
    /**
     * @param {(string|integer)} $index
     * @throws {InvalidArgumentException}
     */
    public function unsetRecord($index)
    {
        $this->wrapper->unsetRecord($index);
    }

}

abstract class ControllerRecordReceipt
{
    /**
     * sets wrapper Records collection
     * @param {Array<Array<Object<string, (string|integer|float|boolean|array)>>>} $model_tables
     * @var {Controller} $this
     */
    public $organizeRecords;
    
    /**
     * @var {Controller} $this
     * @returns {Segment}
     */
    public $getSegmentType;
    
    /**
     * @param {Segment} $segment
     * @returns {Segment}
     * @var {Controller} $this
     */
    public $initializeSegment;
    
    public function __construct(
            callable $organize_records, callable $get_segment_type, callable $initialize_segment,
            Controller $wrapper
            );
    
    public function getId()
    {
        return $this->wrapper->getId();
    }

    public function getRecords()
    {
        return $this->wrapper->getRecords();
    }
}

class ControllerRecordReceiptCMA extends ControllerRecordReceipt implements Controller
{
    /**
     * to be filled with an anonymous function
     */
    private $organize_records;
    /**
     * to be filled with an anonymous function
     */
    private $get_segment_type;
    /**
     * to be filled with an anonymous function
     */
    private $initialize_segment;
    /**
     * to be filled with Controller
     */
    private $wrapper;
    
    public function __construct(
            \callable $organize_records, \callable $get_segment_type, \callable $initialize_segment,
            \Controller $wrapper)
    {
                $this->organize_records = $organize_records;
                $this->get_segment_type = $get_segment_type;
                $this->initialize_segment = $initialize_segment;
                $this->wrapper = $wrapper;
    }

    /**
     * after receipt of Records data from Model, organizes Records, uses them to initialize Segment
     * @param {Array<Array<Object<string, (string|integer|float|boolean|array)>>>} $model_tables
     */
    public function execute()
    {
        $this->organize_records();
        $this->initialize_segment($this->get_segment_type());
    }

    public function setRecord($index, \Record $record)
    {
        $this->wrapper->setRecord($index, $record);
    }
    
    /**
     * @param {(string|integer)} $index
     * @throws {InvalidArgumentException}
     */
    public function unsetRecord($index)
    {
        $this->wrapper->unsetRecord($index);
    }

    public function isAuthorizationNeeded()
    {
        return $this->wrapper->isAuthorizationNeeded();
    }

    public function getRest()
    {
        return $this->wrapper->getRest();
    }
    
    public function setRest(\Rest $rest)
    {
        $this->wrapper->setRest($rest);
    }

    public function getUser()
    {
        return $this->wrapper->getUser();
    }

    public function getViewClass()
    {
        return $this->wrapper->getViewClass();
    }

}

class ControllerFactory implements Factory
{
    /**
     * @param {string} $output The platform the request is for, e.g. HTML, Android, iOS
     * @param {Rest} $input The REST arguments
     * @param {string} $rest_type The REST type, e.g. GET, PUT, POST, DELETE, HEADER
     * @return {Controller} Factory generated instance of Controller
     * @throws {InvalidArgumentException}
     */
    public static function getInstance($output, Security $input, $rest_type)
    {
        if(!is_string($output))
            throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ .
                    ' requires first argument to be a string of characters. Provided: '
                    . print_r($output,TRUE));
        if(!is_string($rest_type))
            throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ .
                    ' requires third argument to be a string of characters. Provided: '
                    . print_r($rest_type, TRUE));
        $instance = new Osmosis($input, $factory);
        $instance->crest = new ControllerRestReceiptCMA($this->getMCArgsFunc($rest_type),
                $this->getMCNamesFunc(), $this->getInstantiateMCFunc(), $this->getCallModel());
        $instance->crecord = new ControllerRecordReceiptCMA();
    }
    
    /**
     * @param {string} $output The platform the request is for
     * @return {boolean} Whether the platform is recognized
     * @throws {InvalidArgumentException}
     */
    private function isOutput($output)
    {
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
     * @param {string} $rest_type The REST type
     * @return {boolean} Whether the REST type is recognized
     * @throws {InvalidArgumentException}
     */
    private function isRestType($rest_type)
    {
        if(!is_string($rest_type))
            throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' '
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
     * @param {string} $rest_type the REST call for this operation
     * @return {Callable}
     * @throws {InvalidArgumentException}
     */
    private function getMCArgsFunc($rest_type)
    {
        if(!is_string($rest_type))
            throw new InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' '
                    . 'requires first argument to be string of characters. Provided: '
                    . print_r($rest_type, TRUE));
        $answer;
        switch(strtoupper(trim($rest_type))){
            case 'GET':
                /**
                 * @return {Object<string, (string|integer|array|object)>}
                 * @var {Controller} $this
                 */
                $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $id = $this->getId();
                            $parsed_rest = $this->getRest()->getAssociativeArray();
                            $row_max; $row_increment; $search; $columns; $tables; $addendum;
                            $operators = array(); $values = array(); $targets = array();
                            if(isset($parsed_rest['admin'])){
                                $answer['admin'] = $parsed_rest['admin'];
                                unset($parsed_rest['admin']);
                            }
                            if(isset($parsed_rest['field_set'])){
                                $answer['field_set'] = $parsed_rest['field_set'];
                                unset($parsed_rest['field_set']);
                            }
                            if(isset($parsed_rest['row_current'])){
                                $row_current = $parsed_rest['row_current'];
                                unset($parsed_rest['row_current']);
                            } else
                                $row_current = 0;
                            if(isset($parsed_rest['row_increment'])){
                                $row_increment = $parsed_rest['row_increment'];
                                unset($parsed_rest['row_increment']);
                            }
                            if(isset($parsed_rest['row_max'])){
                                $row_max = $parsed_rest['row_max'];
                                unset($parsed_rest['row_max']);
                            }
                            if(isset($parsed_rest['columns'])){
                                $columns = $parsed_rest['columns'];
                                unset($parsed_rest['columns']);
                            }
                            if(isset($parsed_rest['tables'])){
                                $tables = $parsed_rest['tables'];
                                unset($parsed_rest['tables']);
                            }
                            if(isset($parsed_rest['addendum'])){
                                $addendum = $parsed_rest['addendum'];
                                unset($parsed_rest['addendum']);
                            }
                            if(isset($parsed_rest['z'])){
                                $search = '%' . $parsed_rest['z'] . '%';
                                unset($parsed_rest['z']);
                            }
                            if(isset($parsed_rest['x']))
                                unset($parsed_rest['x']);
                            if(count($parsed_rest)>0){
                                foreach($parsed_rest as $target => $value){
                                    if($target==='operator'){
                                        last($operators);
                                        $operators[key($operators)] = $value;
                                    } else {
                                        $values[] = $value;
                                        $targets[] = $target;
                                        $operators[] = '=';
                                    }
                                }
                            }
                            if(isset($search)&&$search){
                                $search_fields = $this->getSearchFields($id);
                                $values = array_merge($values, array_fill(0, count($search_fields), $search));
                                $operators[] = array_fill(0, count($search_fields), ' LIKE ');
                                $targets[] = $search_fields;
                            }
                            if(isset($values)&&isset($targets)&&isset($operators)){
                                $answer = array(
                                        'where' => array(
                                                'where_values' => $values,
                                                'where_targets' => $targets,
                                                'operators' => $operators
                                        )
                                );
                            }
                            $answer['row_current'] = $row_current;
                            if(isset($row_max))
                                $answer['row_max'] = $row_max;
                            if(isset($row_increment))
                                $answer['row_increment'] = $row_increment;
                            if(isset($columns))
                                $answer['columns'] = $columns;
                            if(isset($tables))
                                $answer['tables'] = $tables;
                            return $answer;
                        };
                break;
            case 'POST':
            case 'PUT':
            case 'DELETE':
                // assign $answer a function
                break;
        }
        return $answer;
    }
    
    /**
     * @return {Callable}
     */
    private function getInstantiateMCFunc()
    {
        /**
         * @param {string} $model_call
         * @param {Object<string,(string|integer|float|array)>} $args
         * @return {(ModelCall|NULL)}
         * @var {Controller} $this
         */
        return function(callable $model_call, array $args)
                {
                    $answer = new $model_call();
                    $name = 'set';
                    foreach($args as $key => $value){
                        $name_parts = explode("_", $key);
                        Utilities::traversableArrayWalk($name_parts, "Utilities::callableUCFirst");
                        $name .= implode("", $name_parts);
                        $answer->$name($value);
                    }
                    return $answer;
                };
    }
    
    /**
     * @return {Callable}
     */
    private function getMCNamesFunc()
    {
        /**
         * @return {ArrayAccess<string>} queue of ModelCall string names
         * @throws {InvalidArgumentException}
         * @var {Controller} $this
         */
        return function()
                {
                    $id = $this->getId();
                    $auth_needed = $this->isAuthorizationRequired();
                    $answer = new SplDoublyLinkedList();
                    if($auth_needed){
                        $id .= "NonSecure";
                        $answer->push($id);
                        return $answer;
                    }
                    $array_rows = file(__MODEL_CALL_NAME_TABLE__);
                    $family_found = FALSE;
                    for($i=count($array_rows)-1;$i>-1;--$i){
                        $row = str_getcsv($array_rows[$i]);
                        if($id===$row[0]){
                            $family_found = TRUE;
                            $answer->push($row[1]);
                        } else if($family_found)
                            return $answer;
                    }
                };
    }
    
    /**
     * @return {Callable}
     */
    private function getCallModel()
    {
        /**
         * @param {ModelCall} $call
         * @var {Controller} $this
         */
        return function (ModelCall $call)
                {
                    $this->setRecords($i, new Record($model_call->execute()));
                };
    }
    
    /**
     * @param Controller $wrapper
     * @return {Callable}
     */
    private function getOrganizeRecords(Controller $wrapper)
    {
        /**
         * @returns {ArrayAccess<Records>}
         * @var {Controller} $this
         */
        return new DBToRecordsCallable($wrapper);
    }
    
    /**
     * @return {Callable}
     */
    private function getSegType()
    {
        /**
         * @return {Segment}
         * @var {Controller} $this
         */
        return function()
                {
                    $name = $this->getID() . "Segment";
                    return new $name();
                };
    }
    
    /**
     * @return {Callable}
     */
    private function getInitializeSeg()
    {
        /**
         * Initializes Segment with Records from business logic
         * @param {Segment} $segment passed by value
         * @var {Controller} $this
         * @return {Segment}
         */
        return function(Segment $segment)
                {
                    $records = $this->getRecords();
                    foreach($records as $num => $record){
                        $segment->add($num, $record);
                    }
                    return $segment;
                };
    }

}

class ControllerCollection implements Iterator, ArrayAccess
{
    private $count = 0;
    
    public function current() {
        
    }

    public function key() {
        
    }

    public function next() {
        
    }

    public function offsetExists($offset) {
        
    }

    public function offsetGet($offset) {
        
    }

    public function offsetSet($offset, $value) {
        
    }

    public function offsetUnset($offset) {
        
    }

    public function rewind() {
        
    }

    public function valid() {
        
    }

    private $collection = [];
}

class User
{
    private $user = '';
    private $user_id;
    public function __construct($cookie_user_name, $session_user_name, $cookie_user_id = NULL,
            $session_user_id = NULL)
    {
        if(!is_string($cookie_user_name))
            throw new InvalidArgumentException(
                    'User constructor expects first argument to be a text string. Provided '
                    . print_r($cookie_user_name, true)
            );
        if(!is_string($session_user_name))
            throw new InvalidArgumentException(
                    'User constructor expects second argument to be a text string. Provided '
                    . print_r($session_user_name, true)
            );
        if(!is_null($session_user_id)&&($cookie_user_id===$session_user_id||is_null($cookie_user_id))){
            if(strlen($cookie_user_name)>0){
                if($cookie_user_name===$session_user_name)
                    $this->user = $cookie_user_name;
                else if(strlen($session_user_name)>0)
                    $this->user = '';
            } else
                $this->user = $session_user_name;
            $this->user_id = $session_user_id;
        } else {
            $this->user_id = NULL;
            $this->user = '';
        }
    }
    
    public function isValidUser()
    {
        if(is_null($this->user_id))
            return FALSE;
        else
            return TRUE;
    }
    
    public function whoIs();

    public function toArray()
    {
        return [
            'user' => $this->user
        ];
    }
    
    public function toString()
    {
        return $this->user;
    }
}

class DBToRecordsCallable implements Controller
{
    private $rows = array();
    private $position = 0;
    private $wrapper;
    public function __construct(Controller $wrapper)
    {
        $this->wrapper = $wrapper;
    }
    
    public function __invoke()
    {
        $records = $this->getRecords();
        foreach($records as $key => $value){
            $this->unsetRecord($key);
        }
        for($i=0, $max=count($records);$i<$max;$i++){
            $table = $records[$i]->toAssocArray();
            if($table['name']===$this->getId()){
                unset($table['name']);
                unset($records[$i]);
                $incumbent_key;
                $cluster = array();
                last($table);
                for($max = is_int(key($table)) ? key($table) : count($table)-1; $max>-1; --$max){                
                    if(!isset($incumbent_key)||$incumbent_key===$table[$max][key($table[$max])]){
                        $cluster[] = $table[$max];
                        $incumbent_key = $table[$max][key($table[$max])];
                    } else {
                        $incumbent_key = $table[$max][key($table[$max])];
                        if(count($cluster)===1)
                            $rows[$this->position++] = $cluster;
                        else if(count($cluster)>1){
                            $this->processCluster($cluster, $this->position++);
                        }
                        $cluster = [
                                $table[$max]
                            ];
                    }
                }
            }
                
        }
        for($i=0, $max=count($records);$i<$max;$i++){
            $record = $records[$i]->toAssocArray();
            $name = $record["name"];
            unset($record["name"]);
            $this->consolidateRows($this->rows, $record, $name);
        }
    }
    
    /**
     * Updates single row of records, based on $postition, in $this->rows
     * @throws {InvalidArgumentException}
     * @param {Array<Object<string,(string|integer|float|Boolean|array)>>} $cluster
     * @param {integer} $position
     */
    private function processCluster(array $cluster, $position)
    {
        $keys = array_keys($cluster[0]);
        $row = array();
        if(isset($cluster['name'])){
            $name = $cluster['name'];
            unset($cluster['name']);
        } else
            $name = "";
        foreach($keys as $val){
            $row[$name . $val] = array();
        }
        error_log('DbTableConsolidatedRows->processCluster $row: ' . print_r($row, TRUE)
                . ' and $position: ' . print_r($position, TRUE));
        $first_time_through = TRUE;
        for($i=count($cluster) - 1;$i>-1;$i--){
            if($first_time_through){
                $cluster[$i] = array_reverse($cluster[$i]);
                $first_time_through = FALSE;
            }
            error_log('$cluster[$i] before: ' . print_r($cluster[$i], TRUE)
                    . ' and $row before: ' . print_r($row, TRUE));
            for($clmn = count($cluster[0])-1; $clmn>-1; --$clmn){
                list($column, $cell) = each($cluster[$i]);
                $row[$name . $column][] = $cell;
            }
            error_log('$cluster[$i] after: ' . print_r($cluster[$i], TRUE)
                    . ' and $row after: ' . print_r($row, TRUE));
        }
        foreach ($row as $key => $value) {
            $value = array_unique($value);
            if(count($value)===1)
                $row[$key] = $value[0];
            else
                $row[$key] = $value;
            $records = $this->getRecords();
            if(isset($records[$position])){
                $records[$position]->addend($key, new Record($value));
                $this->setRecord($position, $records[$position]);
            } else {
                $r = new Record();
                $r->addend($key, $value);
                $this->setRecord(position, $r);
            }
        }
        
    }
    
    /**
     * Updates all rows of records in $this->rows
     * @param {Array<Object<string,(string|number|Boolean|array)>>} $rows
     * @param {string} $name name of ModelCall to be concatenated to beginning of index
     * @throws {InvalidArgumentException}
     */
    private function consolidateRows(array $rows, $name)
    {
        if(!is_string($name))
            throw new InvalidArgumentException(__CLASS__ . __METHOD__ . " requires second"
                    . " argument be a string of characters. Provided: " . print_r($name, TRUE));
        last($rows);
        $new_records = new SplFixedArray(count($rows[key($rows)]));
        $columns = new SplFixedArray(count($rows[key($rows)]));
        foreach($rows[key($rows)] as $cname => $cvalue){
            $columns[$name . $cname] = array();
        }
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
        last($records);
        for($i=key($records);$i>-1;$i--){
            foreach($new_records as $k => $set){
                $this->setRecord($i, $records[$i]->addend($k, $set));
            }
        }
    }

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

    public function setRecord($index, \Record $record)
    {
        $this->wrapper->setRecord($index, $record);
    }

    public function setRest(\Rest $rest)
    {
        $this->wrapper->setRest($rest);
    }

    public function unsetRecord($index)
    {
        $this->wrapper->unsetRecord($index);
    }

}
