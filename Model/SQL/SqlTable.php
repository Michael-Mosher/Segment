<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlTable extends \Segment\Model\Table
{
    
    /**
     * SQL requires DB and table names to be strings
     * @param \Segment\Model\production\StringValue $table
     * @param \Segment\Model\production\StringValue $db
     */
    public function __construct(\Segment\Model\production\StringValue $table, \Segment\Model\production\StringValue $db)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if(empty($table) || empty($db)){
            throw new \LogicException(__METHOD__ . ' argument strings cannot be empty, length zero. Provided: '
                    . $table . ' for first argument, and second argument is: ' . $db);
        }
        $this->table = $table;
        $this->db = $db;
    }
    
    public function __toString() {
        return $this->db . "." . $this->table;
    }
}