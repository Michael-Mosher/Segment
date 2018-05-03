<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class SqlAverageExpression extends \Segment\Model\AverageExpression
{
    public function __construct(\Segment\Model\Column $clmn)
    {
        parent::__construct("AVG({$clmn->getReturnColumn()})");
    }

}