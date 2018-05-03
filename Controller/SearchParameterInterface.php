<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface SearchParameterInterface extends Parameter
{    
    /**
     * Constructor
     * @param string $field Search target
     * @param mixed $value Search operand
     * @param string $operator Constant of RestSearch
     */
    public function __construct($field, $value, $operator, $conjunctive = 'noconj');
    
    public function getField():string;
    
    public function getValue();
    
    public function getOperator():string;
    
    public function getConjunctive():string;
    
}