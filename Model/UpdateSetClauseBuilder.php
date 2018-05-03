<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class UpdateSetClauseBuilder extends DelayedBuilder implements SetClauseBuilder
{
    public function __clone()
    {
        $this->queue = \Segment\utilities\Utilities::arrayCopy($this->queue);
    }
}