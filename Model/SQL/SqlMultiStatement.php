<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");
//require_once ('Model.php');
//require_once ('source_h.php');


class SqlMultiStatement extends SQLStatement implements \Segment\Model\MultiStatement
{
    /**
     * Used when there are multiple statements in same request
     * @param \Segment\Model\Statment $stmt1
     * @param \Segment\Model\Statment $stmt_multi Variable-length variable
     */
    public function addStatements(\Segment\Model\Statement $stmt1, \Segment\Model\Statement ...$stmt_multi)
    {
        $this->values[0] = $stmt1->getStatement();
        foreach ($stmt_multi as $stmt){
            $this->values[0] .= $stmt->getStatement();
        }
    }
}