<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class WildHttpRequest extends \Segment\Controller\RestRequest
{
    use \Segment\Controller\SearchRestRequest;

    public function rewind()
    {
        $this->searchRewind();
    }

    public function get()
    {
        return $this->getSearchTuple();
    }

    public function next()
    {
        return $this->searchNext();
    }

}