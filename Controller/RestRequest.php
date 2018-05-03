<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class RestRequest implements \Segment\utilities\ForIterable
{
    const GET = 'GET';
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = 'DELETE';
    const SEARCH_ARRAY = [
        'equalany',
        'equalall',
        'greaterany',
        'greaterall',
        'lesserany',
        'lesserall',
        'between',
        'nbetween',
        'nequal',
        'greater',
        'greatereq',
        'lesser',
        'lessereq'
    ];

    protected $call_type;


    /**
     * Defines model call type identifier for the RestRequest.
     * @param string $call_type
     */
    public function setCallType($call_type)
    {
        $this->call_type = $call_type;
    }
    
    /**
     * Returns the model call type identifier.
     * @return string
     */
    public function getCallType(): string
    {
        return $this->call_type;
    }
    
    /**
     * Put index to beginning
     */
    abstract public function rewind():void;
    
    /**
     * Moves index to next position.
     * @return boolean TRUE if new position within bounds, FALSE if not.
     */
    abstract public function next():bool;
    
    /**
     * Returns tuple index currently points to.
     * @return Parameter
     */
    abstract public function get(): Parameter;
}