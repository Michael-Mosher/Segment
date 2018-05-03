<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class InsertSetClauseBuilder extends DelayedBuilder implements SetClauseBuilder
{
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
}