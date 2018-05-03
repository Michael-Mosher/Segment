<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class CallOrchestratorTable extends \Segment\Controller\ModelCallOrchestratorAbstract
{
    use \Segment\utilities\AbstractClassNamesGetter;
    protected function getRowQuantity()
    {
        $qty = isset($parsed_rest['row_increment']) ? $parsed_rest['row_increment'] : __ROW_INCREMENT__;
        return $qty;
    }
    
    protected function getRowStart()
    {
        $start = isset($parsed_rest['row_current']) ? $parsed_rest['row_current'] : 0;
        return $start;
    }
}