<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlDeleteClauseBuilder implements \Segment\Model\DeleteClauseBuilder
{
    public function __construct()
    {    }
    
    /**
     * 
     * @param array $tables
     * @return type
     */
    public function getClause(array $tables = [])
    {
        return 'DELETE ' . $tables[0];
    }
}