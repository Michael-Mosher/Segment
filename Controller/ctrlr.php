<?php
namespace Segment\Controller\production;
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > __SESSION_EXPIRATION__) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp


abstract class ControllerAbstract implements \Segment\Controller\Controller
{
    protected $security;
    protected $id;
    protected $rest;
    protected $records;
    protected $crest;
    protected $crecords;
    private $db_description_fetch;
    protected $namespace = __NAMESPACE__;
    /* array of Permeator, traverse calling each to fill the array of ModelOut
     *  pass by reference
     * Array for ModelOut
     * Run SegmentCreator over each ModelOut array entry
    */
    
    public function __construct(Security $security)
    {
        $this->security = $security;
        $temp_rest = $this->security->getRest();
        $this->id = $temp_rest->hasKey('x') ? $temp_rest->getValue('x') : $this->id;
        $fetch_name = strlen(__PROJECT_NAME__)>0&&stripos($this->namespace, __PROJECT_ACRONYM__)
                ? $this->namespace . ucfirst(strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch'
                : $this->namespace . ($s = '\\')
                . ($temp = strlen(__PROJECT_ACRONYM__)>0 ? ucfirst(__PROJECT_ACRONYM__) . $s : '')
                . ucfirst(strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch';
        $this->db_description_fetch = new $fetch_name();
    }
    
    abstract public function prepareRest();
    
    /**
     * JSON-formatted list of columns to be used in Wildcard search.
     * @return string JSON-formatted string.
     * @throws \InvalidArgumentException
     */
    private function getSearchFields()
    {
        $id = $this->getId();
        if(!is_string($id)){
            throw new \InvalidArgumentException('getSearchFields first argument must be text string.'
                    . ' First argument given ' . $id);
        }
        $string_location = 'Segment\'' . '__SEARCHFIELDS_' . strtoupper($id) . '__';
        $answer = json_decode($string_location, TRUE);
        return $answer;
    }
    
    /**
     * Get DbDescription object for columns of one or all tables in source database.
     * @param string $table Optional. Name of desired DB table description
     * @return \Segment\utilities\DbDescription
     */
    public function getDescription($table = FALSE)
    {
        return $this->db_description_fetch->getDescription($table);
    }
    
    public function getRest()
    {
        return $this->rest;
    }
    
    public function setRest(\Segment\utilities\Rest $rest)
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
    
    /**
     * @return Array<\Segment\utilities\Record> The collected database records
     */
    public function getRecords()
    {
        $this->records;
    }
    
    /**
     * 
     * @param \Segment\Controller\ControllerRestReceipt $crr
     */
    public function addControllerRestReceipt(\Segment\Controller\ControllerRestReceipt $crr)
    {
        if($crr->setWrapper($this))
            $this->crest = $this->crest ?? $crr;
    }
    
    /**
     * 
     * @param \Segment\Controller\ControllerRestReceipt $crr
     */
    public function addControllerRecordReceipt(\Segment\Controller\ControllerRecordReceipt $crr)
    {
        if($crr->setWrapper($this))
            $this->crecords = $this->crecords ?? $crr;
    }
}

class Security
{
    use \Segment\utilities\AbstractClassNamesGetter;
    private $model;
    private $rest;
    private $rest_type;
    private $destination;
    private $user;
    private $view_class;
    private $token;
    private $id;
    private $osmosis_chain;
    private $requires_authentication_eval = FALSE;
    private $requires_authentication;
    
    /**
     * 
     * @param \Segment\Controller\production\Rest $rest
     * @param \Segment\Controller\production\User $user
     * @param string $token
     * @param string $view_class
     * @param string $id
     * @param string $destination Optional. Default value NULL
     * @param \Segment\Controller\production\Osmosis $chain Optional. Default value NULL
     * @throws \InvalidArgumentException
     */
    public function __construct(\Segment\utilities\Rest $rest, \Segment\utilities\User $user, $token, $view_class, $id,
            $destination = NULL, Osmosis $chain = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->rest = $rest;
        $this->rest_type = $_SERVER['REQUEST_METHOD'];
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
            $t = new \Segment\utilities\Rest('test');
            //$t->
            for($this->rest->rewind(); $this->rest->next();){
                $key = $this->rest->key();
                $value = $this->rest->current();
                if(!$value)

                if($key==='admin'||
                        ($key==='x'&&
                        array_search($key, $auth_req_mcalls = json_decode(__AUTH_REQ_MODEL_CALLS__, TRUE)))){
                    $answer = TRUE;
                    break;
                }
            }
            $this->requires_authentication_eval = TRUE;
            $this->requires_authentication = $answer;
        } else
            $answer = $this->requires_authentication;
        
        return $answer;
    }
    
    /**
     * Attempts to authorize user, if required by model call, and returns confirmation.
     * @return boolean TRUE if authorization requirements met, FALSE otherwise.
     */
    public function authorize()
    {
        $answer = FALSE;
        // Determine if authentication already obtained
        $authenticated_user = $this->user->whoIs();

        // Determine if credentials for authenticating have been provided
        if($this->requires_authentication&&strlen($authenticated_user)==0){
            if($this->rest->hasKey('user_name')&&
                    ($this->rest->hasKey('password')||$this->rest->hasKey('password1'))){

                $uname = $this->rest->getValue('user_name');
                $pword = $this->rest->hasKey('password') ? $this->rest->getValue('password')
                        : $this->rest->getValue('password1');

                // Test credentials
                $auth_rest = new Rest([
                    'user_name' => $uname
                ]);
                $authentication = new Authenticate($auth_rest, $user, $view_class,
                        $this->rest->getId(), $this->destination);
                $db_reply = json_decode($authentication->permeate(), TRUE);
                if(hash_equals(
                        $db_reply[0]->getValue('password_hash'), crypt(
                                $pword, $db_reply[0]->getValue('password_salt')
                                )
                        )){
                    $this->rest->setValue('authentication_status', TRUE);
                    $answer = TRUE;
//                    $answer = $this->passToController(
//                        $this->rest,
//                        $this->user,
//                        $this->view_class,
//                        $this->id,
//                        $this->destination,
//                        $this->osmosis_chain
//                    );
                } else {
                    $this->rest->setValue('authentication_status', "false");
                    
//                    $this->rest->setValue('x', $this->id);
//                    $payload = new \Segment\utilities\Record($this->rest->getId());
//                    $rest = $this->rest->toAssocArray();
//                    foreach($rest as $column => $values){
//                        $payload->addend($column, $values);
//                    }
//                    $payload->addend('user', [
//                            $this->user
//                    ]);
//                    $payload->addend('view_class', [
//                            $this->view_class
//                    ]);
//                    $payload->
                    sleep(0.75); // For attacks so failure has similar time to success
                }
            }
        } else {
            $this->rest->setValue('authentication_status', TRUE);
            $answer = isset($this->requires_authentication) ? !$this->requires_authentication : FALSE;
//            $answer = $this->passToController(
//                $this->rest,
//                $this->user,
//                $this->view_class,
//                $this->id,
//                $this->destination,
//                $this->osmosis_chain
//            );
        }
    }

    /**
     * @return boolean
     */
    public function isSufficientUser()
    {
        // Is a particular view class required and does the user have it
                    /*
                     * This is where querying of user_access table would take place in more complex site
                     */
                    // Prepare and send reply
    }

    /**
     * 
     * @param \Segment\Controller\production\Rest $rest
     * @param \Segment\Controller\production\User $user
     * @param string $view_class
     * @param string $id
     * @param string $destination
     * @param \Segment\Controller\production\Osmosis $chain
     */
    private function passToController($rest, $user, $view_class, $id, $destination, $osmosis_chain)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__CLASS__, __METHOD__, func_get_args());
        $target = $destination ? $destination : $id;
        if(strlen($target)===0)
            $target = __DEFAULT_MODEL_CALL_ORCHESTRATOR__;
        $view_class = $this->getViewClass($target, $session);
        $class_name = explode('_', $target);
        $class_name = $this->getClassName($class_name, '\Segment\Controller\production');
        $class_name = array_merge(array(strtolower($request_type)), $class_name);
        \Segment\utilities\Utilities::traversableArrayWalk($class_name, '\Segment\utilities\Utilities::callableUCFirst');
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
    
    public function getRestType()
    {
        return $this->rest_type;
    }
    
    public static function generateHash()
    {
        return crypt(__PROJECT_NAME__ . __DOMAIN__, date('l jS \of F Y h:i:s A'));
    }
    
    public static function encrypt($str)
    {
        if(!is_string($str))
            throw new \InvalidArgumentException('Security::encrypt requires first argument to be text string.'
                    . ' Provided: ' . print_r($str, TRUE));
        return crypt($str, '$1$' . \Segment\utilities\Utilities::getRandomString(8) . '$');
    }
    
    /*public static function getViewClass($target, array $server_array_copy)
    {
         //pending
        return '';
    }*/
}


class ControllerFactory implements \Segment\utilities\Factory
{
    use \Segment\utilities\AbstractClassNamesGetter;
    
    const OUTPUT_HTML = 'html';
    const OUTPUT_ANDROID = 'android';
    const OUTPUT_IOS = 'ios';
    
    /**
     * @param string $output The platform the request is for, e.g. HTML, Android, iOS
     * @param \Segment\Controller\production\Security $input The REST arguments
     * @param string $rest_type The REST type, e.g. GET, PUT, POST, DELETE, HEADER
     * @return \Segment\Controller\Controller Factory generated instance of Controller
     * @throws \InvalidArgumentException
     */
    public static function getInstance($output, $input)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if(!static::isOutput($output))
            throw new \InvalidArgumentException(__METHOD__.' expects zeroeth argument, base 0, '
                    . 'to be of one of the OUTPUT ControllerFactory constants. Provided: '
                    .print_r($output, TRUE));
        $rest_type = func_get_arg(2);
        if(!static::isRestType($rest_type))
            throw new \InvalidArgumentException(__METHOD__.' expects second argument, base 0, '
                    . 'to be a REST type. Provided: ' . print_r($rest_type, TRUE));
        $instance = class_exists('TestController') ? new TestController($input) : new class($input) extends ControllerAbstract
                {
                    use \Segment\utilities\FunctionSetter;
                    private $scrubbed_rest;
                    protected $namespace = __NAMESPACE__;

                    public function __construct(Security $security)
                    {
                        parent::__construct($security);
                    }

                    /**
                     * @return Segment success returns Segment, else FALSE
                     * @var $this Osmosis 
                     */
                    public function execute()
                    {
                        $answer = FALSE;
                        $this->scrubbed_rest = $this->prepareRest();
                        if($this->crest->execute())
                            $answer = $this->crecords->execute();
                        return $answer;
                    }

                    /**
                     * @return \Segment\utilities\RestRequest
                     * @var $this Osmosis 
                     */
                    public function getRest()
                    {
                        if(is_null($this->scrubbed_rest)||!isset($this->scrubbed_rest))
                            $this->prepareRest();
                        return $this->scrubbed_rest;
                    }

                    /**
                     * 
                     * @param string $func_name
                     * @param callable|\Closure $function
                     */
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
                        $type = ucfirst($type) . 'HttpRequest';
                        $x_request = $this->rest->getValue('x');
                        if(is_string($x_request)){
                            try{
                                $x_request = json_decode($x_request, TRUE);
                            } catch (\Exception $ex) {
                                error_log(__CLASS__.'::'.__METHOD__." attempted json_decode()"
                                        . " on {$x_request}");
                                $x_request = [];
                            }
                        }
                        reset($x_request);
                        return $answer = new $type($x_request[key($x_request)]);
                    }

                };
        if(\Segment\utilities\Utilities::isRestAction($rest_type)){
            $c_rest_r_n = $input->getClassName('ControllerRestReceipt', __NAMESPACE__);
            $c_rec_r_n = $input->getClassName('ControllerRecordReceipt', __NAMESPACE__);
            $controller_rest_receipt = new $c_rest_r_n();
            $controller_record_receipt = new $c_rec_r_n();
            $temp = self::getCallModelFunc();
            $controller_rest_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getMCNamesFunc();
            $controller_rest_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getMCArgsFunc($rest_type, $input->getRest());
            $controller_rest_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getInstantiateMCFunc();
            $controller_rest_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getOrganizeRecords($instance);
            $controller_record_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getSegTypeFunc();
            $controller_record_receipt->setInstanceFunction(key($temp), current($temp));
            $temp = self::getInitSegFunc();
            $controller_record_receipt->setInstanceFunction(key($temp), current($temp));
            
            $instance->addControllerRestReceipt($controller_rest_receipt);
            $instance->addControllerRecordReceipt($controller_record_receipt);
        }
        return $instance;
    }
    
    /**
     * @param string $output The platform the request is for
     * @return boolean Whether the platform is recognized
     * @throws \InvalidArgumentException
     */
    public static function isOutput($output)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
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
            throw new \InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' '
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
     * Returns function getModelCallArgs()
     * @param string $rest_type the REST call for this operation
     * @param \Segment\utilities\Rest $rest
     * @return callable
     * @throws \InvalidArgumentException
     */
    private static function getMCArgsFunc($rest_type, \Segment\utilities\Rest $rest)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $answer;
        $temp = $rest->getValue('x');
        $temp = is_string($temp) ? json_decode($temp, TRUE) : $temp;
        $id = is_array($temp) ? key($temp) : '';
        switch(strtoupper(trim($rest_type))){
            case 'GET':
                $id = $rest->getId();
                if((strpos($id, '_search')&&strpos($id, '_search')+7===strlen($id))||
                        (strpos($id, '_field_set')&&strpos($id, '_field_set')+10===strlen($id))||
                        (strpos($id, '_field_count')&&strpos($id, '_field_count')+12===strlen($id))||
                        (strpos($id, '_field_avg')&&strpos($id, '_field_avg')+10===strlen($id))||
                        (strpos($id, '_field_mode')&&strpos($id, '_field_mode')+11===strlen($id))||
                        (strpos($id, '_field_median')&&strpos($id, '_field_median')+13===strlen($id))||
                        (strpos($id, '_field_firstq')&&strpos($id, '_field_firstq')+10===strlen($id))||
                        (strpos($id, '_field_thirdq')&&strpos($id, '_field_thirdq')+10===strlen($id))){
                    /**`
                     * @return \Segment\utilities\RestRequest
                     * @var \Segment\Controller\Controller $this
                     */
                    $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $x = $this->getRest();
                            $answer = new \Segment\utilities\SearchHttpRequest($n =
                                    $x->hasKey('x') ? $x->getValue('x') : '');
                            return $answer;
                        };
                } else if(strpos($id, '_wild')&&strpos($id, '_wild')+6===strlen($id)){
                    /**
                     * @return \Segment\utilities\RestRequest
                     * @var \Segment\Controller\Controller $this
                     */
                    $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $x = $this->getRest();
                            $answer = new \Segment\utilities\WildHttpRequest($n =
                                    $x->hasKey('x') ? $x->getValue('x') : '');
                            return $answer;
                        };
                }
                
                break;
            case 'POST':
                
                /**
                     * @return \Segment\utilities\RestRequest
                     * @var \Segment\Controller\Controller $this
                     */
                    $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $x = $this->getRest();
                            $answer = new \Segment\utilities\PostHttpRequest($n =
                                    $x->hasKey('x') ? $x->getValue('x') : '');
                            return $answer;
                        };
                        break;
            case 'PUT':
                /**
                     * @return \Segment\utilities\RestRequest
                     * @var \Segment\Controller\Controller $this
                     */
                    $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $x = $this->getRest();
                            $answer = new \Segment\utilities\PutHttpRequest($n =
                                    $x->hasKey('x') ? $x->getValue('x') : '');
                            return $answer;
                        };
                        break;
            case 'DELETE':
                /**
                     * @return \Segment\utilities\RestRequest
                     * @var \Segment\Controller\Controller $this
                     */
                    $answer = function()
                        {
                            $answer = array();
                            if($this->isAuthorizationNeeded())
                                return $answer;
                            $x = $this->getRest();
                            $answer = new \Segment\utilities\DeleteHttpRequest($n =
                                    $x->hasKey('x') ? $x->getValue('x') : '');
                            return $answer;
                        };
                        break;
                // assign $answer a function
                break;
        }
        return ['getModelCallArgument' => $answer];
    }
    
    
    /**
     * @param \Segment\Controller\Controller $wrapper
     * @return callable
     */
    private static function getOrganizeRecords(\Segment\Controller\Controller $wrapper)
    {
        /**
         * @returns ArrayAccess<\Segment\utilities\Record>
         * @var \Segment\Controller\Controller $this
         */
        return ['organizeRecords' => new DBToRecordsCallable($wrapper)];
    }
    
    
    protected static function getInstantiateMCFunc()
    {
        /**
         * @param callable $model_call
         * @param \Segment\utilities\RestRequest $args
         * @return \Segment\Model\production\ModelCaller
         * @var \Segment\Controller\Controller $this
         */
        return [
            'instantiateModelCall' => function(callable $model_call, \Segment\utilities\RestRequest $args)
            {
                $answer = new $model_call();
                $name = 'set';
                foreach($args as $key => $value){
                    $name_parts = explode("_", $key);
                    \Segment\utilities\Utilities::traversableArrayWalk(
                            $name_parts, "\Segment\utilities\Utilities::callableUCFirst");
                    $name .= implode("", $name_parts);
                    $answer->$name($value);
                }
                return $answer;
            }
        ];
    }
    
    protected static function getMCNamesFunc()
    {
        /**
     * @return \ArrayAccess<string> queue of ModelCaller string names
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
                for($i=count($array_rows)-1;$i>-1;--$i){
                    $row = str_getcsv($array_rows[$i]);
                    if($id===$row[0]){
                        $family_found = TRUE;
                        $answer->push($row[1]);
                    } else if($family_found)
                        return $answer;
                }
            }
        ];
    }
    
    protected static function getCallModelFunc()
    {
        /**
     * Calls passed ModelCallOrchestrator invocable with supplied $args. Returned
     * Record is added to Controller wrapper.
     * @param \Segment\Controller\ModelCallOrchestrator $call
     * @param \Segment\utilities\RestRequest $args Associative array, object with string keys.
     * @var \Segment\Controller\Controller $this
     */
        return [
            'callModel' => function(\Segment\Controller\ModelCallOrchestrator $call, \Segment\utilities\RestRequest $args)
            {
                $this->setRecords(new \Segment\utilities\Record($call->execute($args, $this)));
            }
        ];
    }
    
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
    
}

class ControllerCollection implements \Iterator, \ArrayAccess
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

class DBToRecordsCallable implements \Segment\Controller\Controller
{
    private $rows = array();
    private $position = 0;
    private $wrapper;
    public function __construct(\Segment\Controller\Controller $wrapper)
    {
        $this->wrapper = $wrapper;
    }
    
    public function __invoke()
    {
        // get Records from wrapper
        /* $records is a scalar array of Record objects */$records = $this->getRecords();
        /* The $ctrlr_id is the Controller identification derived from the HTTP REST function*/$ctrlr_id = $this->getId();
        // Remove Controller records so only revised will be there
        foreach($records as $key => $value){
            $this->unsetRecord($key);
        }
        sort($records, \SORT_NATURAL);
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
        for($i=0, $max=count($records);$i<$max;$i++){
            $record = $records[$i];
            
            // determine if primary call record
            $clusters = $record->getId()===$ctrlr_id ? $clusters = &$main_cluster : $clusters = &$other_clusters;
            
            // prepare '_id' clusters
            if($record->getRowNum()!==FALSE){
                if(isset($clusters[$record->getId()][$record->getRowNum()])){
                    $record = $this->combineRows($clusters[$record->getId()][$record->getRowNum()], $record);
                }
                $clusters[$record->getId()][$record->getRowNum()] = $record;
                
                // prepare non-id clusters
            } else if($record->count===1){
                foreach($record as $column => $cell){
                    if(isset($clusters[$record->getId()][$column]))
                        $record = $this->combineRows($clusters[$record->getId()][$column], $record);
                    $clusters[$record->getId()][$column] = $record;
                    break;
                }
            }
        }
        $this->addMainCluster($main_cluster[$ctrlr_id]);

        foreach($other_clusters as $other_ids => $cluster_array){
            foreach($cluster_array as $field => $record){
                if(is_int($field))
                    $this->processIdCluster($cluster_array);
                else
                    $this->processNonIdCluster ($cluster_array);
            }
        }
    }
    

    private function addMainCluster(array $cluster)
    {
        foreach($cluster[$this->getId()] as $id => $record){
            $this->setRecord($id, $record);
        }
    }
    
    private function processIdCluster(array $cluster)
    {
        /* scalar array of Record objects */$records = $this->getRecords();
        $sample_main_rec = $records[0];
        end($cluster);
        for($i=key($cluster);$i>-1;$i--){
            $non_main_record = $cluster[$i];
            foreach($records as $id => $a_main_record){
                /* Need to verify both that the row_num_name and row_num match */
                if($sample_main_rec->getRowNumField()===$non_main_record->getRowNumField()&&
                        $sample_main_rec->getRowNum()===$non_main_record->getRowNum()){
                    $non_main_record->reset();
                    $clmn = $non_main_record->currentKey();
                    for(;$cell = $non_main_record->getNext();$clmn = $non_main_record->currentKey()){
                        $a_main_record->addend($clmn, $cell);
                    }
                    $this->setRecord($id, $a_main_record);
                }
            }
        }
        
    }
    
    /**
     * Adds every Record in cluster array to every Record in Controller with the 
     *     cluster call ID as the index.
     * @param \Segment\utilities\Record $cluster Variable-length variable. Records all same call ID
     */
    private function processNonIdCluster(\Segment\utilities\Record ...$cluster)
    {
        /* scalar array of Record objects */$records = $this->getRecords();
        end($cluster);
        for($i=key($cluster);$i>-1;$i--){
            $non_id_record = $cluster[$i];
            $non_id_type = $non_id_record->getId();
            $temp_array = explode('_', $non_id_type);
            $first_entry = array_slice($temp_array, 0, 1);
            unset($temp_array[0]);
            array_walk($temp_array, \Segment\utilities\Utilities::callableUCFirst);
            $potential_field = implode('', array_merge($first_entry, $temp_array));
            foreach($records as $k => $incum_rec){
                if(isset($incum_rec->$potential_field)){
                    $non_id_record->reset();
                    $incum_rec->$potential_field = $non_id_record->getNext();
                } else {
                    $non_id_record->reset();
                    $clmn = $non_id_record->currentKey();
                    for(;$cell = $record->getNext();){
                        $incum_rec->addend($clmn, $cell);
                        $clmn = $record->currentKey();
                    }
                }
                $this->setRecord($k, $incum_rec);
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