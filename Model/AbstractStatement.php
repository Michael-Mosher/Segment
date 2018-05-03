<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class AbstractStatement implements Statement
{
    use search_value;

    protected $input_output;

    const GET = 'get';
    const DELETE = 'delete';
    const PUT = 'put';
    const POST = 'post';


    public function getInputOutput()
    {
        return clone $this->input_output;
    }

    public function getDbs()
    {
        return $this->input_output->getDbs();
    }

    public function getTables()
    {
        return $this->input_output->getTables();
    }

    public function getStatement()
    {
        $answer = $this->getValues()[0];
        error_log(__METHOD__ . " the answer $answer");
        return $answer;
    }

    abstract public function getGetStatement();
    abstract public function getPostStatement();
    abstract public function getPutStatement();
    abstract public function getDeleteStatement();
}