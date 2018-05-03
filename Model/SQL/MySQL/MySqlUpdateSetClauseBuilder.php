<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlUpdateSetClauseBuilder extends \Segment\Model\UpdateSetClauseBuilder
{
    private $set = '';
    
    
    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $statement = $target->getColumn() . ' = ' . $value->getValues()[0];
        $this->set .= strlen($this->set)>0 ? ', ' . $statement : $statement;
    }

    public function getClause(\Segment\Model\Table ...$tables)
    {
        return 'SET ' . $this->set;
    }

}