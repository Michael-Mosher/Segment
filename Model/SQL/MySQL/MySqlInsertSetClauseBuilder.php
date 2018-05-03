<?php
namespace Segment\Model\production\SQL\MySQL;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class MySqlInsertSetClauseBuilder extends \Segment\Model\InsertSetClauseBuilder
{
    private $fields = '';
    private $values = '';
    
    public function __construct()
    {
        
    }
    
    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $this->fields .= strlen($this->fields)>0 ? ', ' . $target->getColumn() : $target->getColumn();
        $this->values .= strlen($this->values)>0 ? ', ' . $value->getValues()[0] : $value->getValues()[0];
    }

    public function getClause(\Segment\Model\Table ...$tables)
    {
        return '(' . $this->fields . ') VALUES (' .$this->values . ')';
    }

}