<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface Parameter
{
    const field = 0;
    const value = 1;
    const operator = 2;
    
    public function getField(): string;
    public function getValue(): mixed;
    public function getOperator(): string;
}