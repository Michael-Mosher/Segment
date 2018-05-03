<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class ControllerShell extends \Segment\Controller\ControllerAbstract
{
    private $_function_decorations = [];
    protected $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function execute()
    {
        if($this->isInstanceFunction(\end(\explode('::', __METHOD__)))){
            $this->_function_decorations[\end(\explode('::', __METHOD__))](func_get_args());
        }
    }

    /**
     * Returns the database column description, if already queried.
     * @param type $table
     * @return \Segment\utilities\DbDescription
     */
    public function getDescription($table = FALSE): \Segment\utilities\DbDescription
    {
        $answer;
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $n_getter = new Segment\utilities\ClassNameGetter();
            $dbdescription_n = $n_getter->getClassName('DbDescription', __NAMESPACE__);
            $answer = new $dbdescription_n();
        }
        return $answer;
    }

    /**
     * Provides the front-end request type.
     * @return string
     */
    public function getId(): string
    {
        $answer = '';
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        return $answer;
    }

    /**
     * Returns the database records, if queried already.
     * @return array<\Segment\Controller\Record>
     */
    public function getRecords(): array
    {
        $answer;
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $answer = [new \Segment\utilities\Record($this->id)];
        }
        return $answer;
    }

    /**
     * Provides the front-end request arguments.
     * @return \Segment\Controller\Rest
     */
    public function getRest(): \Segment\Controller\Rest
    {
        $answer;
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $answer = new \Segment\utilities\Rest('=&');
        }
        return $answer;
    }

    /**
     * Provides the front-end user.
     * @return \Segment\Controller\User
     */
    public function getUser(): \Segment\Controller\User
    {
        $answer;
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $answer = new \Segment\utilities\User('', "");
        }
        return $answer;
    }

    /**
     * Provides the front-end user preferences.
     * @return string
     */
    public function getViewClass(): string
    {
        $answer = "";
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $answer = "";
        }
        return $answer;
    }

    /**
     * Answers if the front-end request for this user requires authentication.
     * @return boolean
     */
    public function isAuthorizationNeeded(): bool
    {
        $answer = FALSE;
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $answer = $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
        if(!isset($answer)){
            $answer = FALSE;
        }
        return $answer;
    }

    /**
     * Defines the front-end request arguments.
     * @param \Segment\utilities\Rest $rest
     */
    public function setRest(\Segment\utilities\Rest $rest)
    {
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }

    }

    /**
     * Will remove the specified database record.
     * @param type $index
     */
    public function unsetRecord($index)
    {
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
    }

    public function prepareRest()
    {
        if($this->isInstanceFunction(end(explode('::', __METHOD__)))){
            $this->_function_decorations[end(explode('::', __METHOD__))](func_get_args());
        }
    }

    
    /**
     * Decorate the Controller with a method.
     * @param string $func_name
     * @param callable|\Closure $function
     */
    public function setInstanceFunction($func_name, $function)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, \func_get_args());
        $bound_temp = \Closure::bind($function, $this);
        $this->_function_decorations[$func_name] = $bound_temp;
    }

    /**
     * Returns whether the supplied method name has been decorated on the Controller obj.
     * @param type $func_name
     * @return type
     */
    public function isInstanceFunction($func_name)
    {
        return isset($this->_function_decorations[$func_name]);
    }

}