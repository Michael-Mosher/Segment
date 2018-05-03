<?php

namespace Segment\Controller\production;
if(!isset($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN']!==substr(__DOMAIN_SANS_WWW__, 0, strpos(__DOMAIN_SANS_WWW__, "/", 8))){
    \Segment\Controller\production\SessionManager::regenerateSession();
}

class Security
{
    private $model;
    private $rest;
    private $rest_type;
    private $destination;
    private $user;
    private $view_class;
    private $req_method;
    private $id;
    private $osmosis_chain;
    private $requires_authentication_eval = TRUE;
    private $requires_authentication = TRUE;
    private $path_requires_auth = ['test/OnSet/user/index.php'];
    private $client_ip;
    private $client_browser;
    private $browser_version;
    private $accepts_cookies;
    private $accepts_javascript;

    use \Segment\utilities\AbstractClassNamesGetter;
    
    /**
     *
     * @param \Segment\Controller\production\Rest $rest
     * @param \Segment\Controller\production\User $user
     * @param string $req_method
     * @param string $view_class
     * @param string $id
     * @param string $destination Optional. Default value NULL
     * @param \Segment\Controller\production\ControllerAbstract $chain Optional. Default value NULL
     * @throws \InvalidArgumentException
     */
    public function __construct(\Segment\utilities\Rest $rest, \Segment\utilities\User $user,
            $req_method, $view_class, $id, $destination = NULL,
            ControllerAbstract $chain = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        error_log(__METHOD__ . " origin: " . ($origin = $_SERVER['HTTP_ORIGIN']) ?? "");
        error_log(__METHOD__ . " the request method: " . $req_method);
        if(($origin = $_SERVER['HTTP_ORIGIN'] ?? '')=== \substr(__DOMAIN_INSECURE__, 0, 30)
                || $origin===\substr(__DOMAIN_INSECURE_SANS_WWW__, 0, 26)){
            header("Location: " . __DOMAIN__);
        } else if(($origin = $_SERVER['HTTP_ORIGIN'] ?? '')===""||$origin===__DOMAIN__
                ||$origin===__DOMAIN_SANS_WWW__
                ||$origin===\substr(__DOMAIN__, 0, 31)
                || $origin===\substr(__DOMAIN_SANS_WWW__, 0, 27)){
            if($origin===\substr(__DOMAIN_SANS_WWW__, 0, 27)){
                header('Access-Control-Allow-Origin: ' . \substr(__DOMAIN_SANS_WWW__, 0, 27));
            } else if($origin===substr(__DOMAIN__, 0, 31)){
                header('Access-Control-Allow-Origin: ' . \substr(__DOMAIN__, 0, 31));
            }
            if($req_method==='OPTIONS'){
                if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])){
                    error_log(__METHOD__ . " HTTP_ACCESS_CONTROL_REQUEST_METHOD is set");
                    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                }
                if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_CREDENTIALS'])){
                    error_log(__METHOD__ . " HTTP_ACCESS_CONTROL_REQUEST_CREDENTIALS is set");
                    header('Access-Control-Allow-Credentials: true');
                }
                header('Access-Control-Max-Age: 1800');
                if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
                    error_log(__METHOD__ . " HTTP_ACCESS_CONTROL_REQUEST_HEADERS is set");
                    header('Access-Control-Allow-Headers: Content-Type');
                }
                header('Content-Type: text/plain');
                header('Content-Length: 0');
                $this->requires_authentication = FALSE;
            } else if($req_method==='GET'){

            } else if($req_method==='PUT'){
            } else if($req_method==='POST'){
            } else if($req_method==='DELETE'){

            }
            if($req_method!=='OPTIONS'){
                $payload_ip = inet_pton($payload_ip) ? inet_pton($payload_ip) : "127.0.0.1";
                $this->client_ip = inet_pton($payload_ip);
                $user_agent_array = get_browser(NULL, TRUE);
                $this->client_browser = $user_agent_array['browser'];
                $this->browser_version = $user_agent_array['version'];
                $this->accepts_cookies = boolval($user_agent_array['cookies']);
                $this->accepts_javascript = boolval($user_agent_array['javascript']);
                $rest->setValue("session_name", $this->client_ip . $user_agent_array['browser'] . $user_agent_array['version']);
                $this->rest = $rest;
                $this->rest_type = $_SERVER['REQUEST_METHOD'];
                $this->destination = $destination;
                $this->user = $user;
                $this->view_class = $view_class;
                $this->req_method = $req_method;
                $this->id = $id;
                if(isset($chain))
                    $this->osmosis_chain = $chain;
                // Still need to replace '.0' with '.'
                if(strpos($_SERVER['REQUEST_ADDR'], "/")!==FALSE){
                    $valid_ip = inet_pton(substr($_SERVER['REQUEST_ADDR'], 0, strpos($_SERVER['REQUEST_ADDR'], "/")));
                } else {
                    $valid_ip = inet_pton($_SERVER['REQUEST_ADDR']);
                }
                if($valid_ip)
                    $this->client_ip = inet_pton ($_SERVER['REQUEST_ADDR']);
                else {
                    $split_ip = \explode('.0', $_SERVER['REQUEST_ADDR']);
                    if(count($split_ip)>1){
                        $payload_ip = implode('.', $split_ip);
                    } else {
                        $payload_ip = $split_ip[0];
                    }
                }
            }
        } else {
            header("HTTP/1.1 403 Access Forbidden");
            header("Content-Type: text/plain");
            header("Content-Length: 0");
        }
    }

    // Determine if authentication is required
    public function isAuthenticationRequired()
    {
        if($this->requires_authentication_eval){
            $auth_req_model_calls = json_decode(__AUTH_REQ_MODEL_CALLS__, TRUE);
            error_log(__METHOD__ . " rest to string: " . print_r($this->rest->toString(), TRUE));
            $answer = TRUE;
            $r = strlen($this->rest->toString())===0 ? new \Segment\utilities\Rest('{"test":"test"}') : $this->rest;
            error_log(__METHOD__ . " rest object: " . print_r($r, TRUE));
            for($r->rewind(); $r->valid();$r->next()){
                $key = $r->key();
                $value = $r->current();
                if(!$value)
                    error_log(__METHOD__. ' '.print_r($description));
                if($key==='admin'||
                        ($key==='x'&&
                        (array_search($value, $auth_req_model_calls)||
                        array_search($value, $auth_req_mcalls = array_map(
                                "\Segment\utilities\Utilities::convertPropertyNameToFunction",
                                $auth_req_model_calls))))){
                    $answer = TRUE;
                    break;
                } else if($key==='x'&&$value!=FALSE){
                    $answer = FALSE;
                    break;
                }
            }
            // Ensure rest is empty and the destination doesn't require authentication
            if(strlen($this->rest->toString())<3 && !array_search($this->destination, $this->path_requires_auth)){
                $answer = FALSE;
                $this->requires_authentication = $answer;
            }
            $this->requires_authentication_eval = FALSE;
            $this->requires_authentication = $answer;
        } else
            $answer = $this->requires_authentication;

        return $answer;
    }

    /**
     * Attempts to authorize user, if required by model call, and returns confirmation.
     * @return boolean FALSE if no additional authorization required, TRUE otherwise.
     */
    public function authorize()
    {
        $answer = FALSE;
        // Determine if authentication already obtained
        // Determine if credentials for authenticating have been provided
        if($this->isAuthenticationRequired()||!$this->isSufficientUser()){
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
            error_log(__METHOD__ . " no authoization needed.");
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
        return TRUE;
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
    
    /**
     * The HTTP request type.
     * @return String
     */
    public function getRequestType()
    {
        return (String)$this->req_method;
    }
    
    /**
     * Returns the client's browser type as string.
     * @return string
     */
    public function getBrowserType() : string
    {
        return $this->client_browser;
    }
    
    /**
     * Returns the client's browser version as float.
     * @return float
     */
    public function getBrowserVersion() : float
    {
        return $this->browser_version;
    }


    public function getClientIp() : string
    {
        return $this->client_ip;
    }
    
    
    public function isCookiesAccepted() : bool
    {
        return $this->accepts_cookies;
    }
    
    public function isJavaScriptAccepted() : bool
    {
        return $this->accepts_javascript;
    }
    
    public static function isValidRest(string $key, string $value) : bool
    {
        // to do
        return TRUE;
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
}

/*
 * class Security {

    use \Segment\utilities\AbstractClassNamesGetter;

    private $model;
    private $rest;
    private $rest_type;
    private $destination;
    private $user;
    private $view_class;
    private $req_method;
    private $id;
    private $osmosis_chain;
    private $requires_authentication_eval = FALSE;
    private $requires_authentication;


     *
     * @param \Segment\utilities\Rest $rest
     * @param \Segment\utilities\User $user
     * @param string $req_method
     * @param string $view_class
     * @param string $id
     * @param string $destination Optional. Default value NULL
     * @param \Segment\Controller\production\ControllerAbstract $chain Optional. Default value NULL
     * @throws \InvalidArgumentException

    public function __construct(\Segment\utilities\Rest $rest, \Segment\utilities\User $user,
            $req_method, $view_class, $id, $destination = NULL, ControllerAbstract $chain = NULL)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->rest = $rest;
        $this->rest_type = $_SERVER['REQUEST_METHOD'];
        $this->destination = $destination;
        $this->user = $user;
        $this->view_class = $view_class;
        $this->req_method = $req_method;
        $this->id = $id;
        if (isset($chain))
            $this->osmosis_chain = $chain;
    }

// Determine if authentication is required
    public function isAuthenticationRequired() {
        if (!$this->requires_authentication_eval) {
            $answer = FALSE;
            for ($this->rest->rewind(); $this->rest->next();) {
                $key = $this->rest->key();
                $value = $this->rest->current();
                if (!$value)
                    error_log(__METHOD__ . ' ' . print_r($this->rest));
                if ($key === 'admin' ||
                        ($key === 'x' &&
                        \array_search($key, $auth_req_mcalls = \json_decode(__AUTH_REQ_MODEL_CALLS__, TRUE)))) {
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

    
    // * Attempts to authorize user, if required by model call, and returns confirmation.
    // * @return boolean TRUE if authorization requirements met, FALSE otherwise.
    
    public function authorize()
    {
        $answer = FALSE;
// Determine if authentication already obtained
        $authenticated_user = $this->user->whoIs();

// Determine if credentials for authenticating have been provided
        if ($this->requires_authentication && strlen($authenticated_user) == 0) {
            if ($this->rest->hasKey('user_name') &&
                    ($this->rest->hasKey('password') || $this->rest->hasKey('password1'))) {

                $uname = $this->rest->getValue('user_name');
                $pword = $this->rest->hasKey('password') ? $this->rest->getValue('password')
                        : $this->rest->getValue('password1');

// Test credentials
                $auth_rest = new \Segment\utilities\Rest(json_encode([
                    'user_name' => $uname
                ]));
                $authentication = new Authenticate(
                        $auth_rest, $user, $view_class, $this->rest->getId(), $this->destination
                        );
                $db_reply = json_decode($authentication->permeate(), TRUE);
                if (\hash_equals(
                                $db_reply[0]->getValue('password_hash'), \crypt(
                                        $pword, $db_reply[0]->getValue('password_salt')
                                )
                        )) {
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

    
    // * @return boolean
    
    public function isSufficientUser() {
// Is a particular view class required and does the user have it
        
        // * This is where querying of user_access table would take place in more complex site
        
// Prepare and send reply
        return TRUE;
    }

    
//     *
//     * @param \Segment\utilities\Rest $rest
//     * @param \Segment\utilities\User $user
//     * @param string $view_class
//     * @param string $id
//     * @param string $destination
//     * @param \Segment\Controller\production\ControllerAbstract $chain
     
    private function passToController($rest, $user, $view_class, $id, $destination, $osmosis_chain) {
        \Segment\utilities\Utilities::areArgumentsValid(__CLASS__, __METHOD__, func_get_args());
        $target = $destination ? $destination : $id;
        if (strlen($target) === 0)
            $target = __DEFAULT_MODEL_CALL_ORCHESTRATOR__;
        $view_class = $this->getViewClass($target, $session);
        $cn_array = explode('_', $target);
        
//$class_name = array_merge(array(strtolower($request_type)), $class_name);
        \Segment\utilities\Utilities::traversableArrayWalk($cn_array,
                '\Segment\utilities\Utilities::callableUCFirst');
        $class_n = implode('', $cn_array);
        $class_name = $this->getClassName($class_n, '\Segment\Controller\production');
        $osmosis_handler = new $class_name($rest, $user, $view_class, $target);
        $query_result = $osmosis_handler->permeate();
        $answer = [
            $osmosis_handler->getId() => $query_result,
            'user' => $user,
            'view_class' => $view_class
                ] + $rest->getAssociativeArray();
        if ($rest->hasKey('get_row_max') && ($rest->getValue('get_row_max') === TRUE ||
                $rest->getValue('get_row_max') === 1)) {
            $osmosis_handler = new GetRowMax(
                    $rest, $user, $req_method, $view_class, 'row_max', NULL
            );
            $answer['row_max'] = $osmosis_handler->permeate();
        }
        if ($rest->hasKey('get_row_description') && ($rest->getValue('get_row_description') === TRUE
                || $rest->getValue('get_row_description') === 1 ||
                \strtolower($rest->getValue('get_row_description')) === 'true')) {
            $osmosis_handler = new GetRowDescription(
                    $rest, $user, $req_method, $view_class, 'description', NULL, $osmosis_handler
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
        return \crypt(__PROJECT_NAME__ . __DOMAIN__, date('l jS \of F Y h:i:s A'));
    }

    public static function encrypt($str)
    {
        if (!is_string($str))
            throw new \InvalidArgumentException('Security::encrypt requires first argument to be text string.'
                    . ' Provided: ' . print_r($str, TRUE));
        return \crypt($str, '$1$' . \Segment\utilities\Utilities::getRandomString(8) . '$');
    }

//    public static function getViewClass($target, array $server_array_copy)
//      {
//      //pending
//      return '';
//      }
}

 */