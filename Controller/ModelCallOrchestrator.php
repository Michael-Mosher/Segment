<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface ModelCallOrchestrator
{
    /**
     * 
     * @param \Segment\Controller\RestRequest $request
     * @param string $tbl_n The name of an instantiatable \Segment\Model\Table
     * @param string $clmn_n The name of an instantiatable \Segment\Model\Column
     * @param \Segment\utilities\DbDescription $db_descrip
     * @param \Segment\Model\production\ModelCaller $mc By reference.
     * @param \Segment\Controller\CacheWrapper $cache_handler By reference.
     */
    public function __construct(
            RestRequest $request, \Segment\utilities\DbDescription $db_descrip,
            \Segment\Model\production\ModelCaller &$mc, string $options_arg_getter_func, CacheWrapper &$cache_handler);
    
    public function getRestRequest():TestRestRequest;


    /**
     * Calls DAO as needed for business logic and returns resulting rows in array of Record objects.
     * @return array Those rows returned from DB query <\Segment\utilities\Record>
     */
    public function execute(): array;
}