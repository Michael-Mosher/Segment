<?php
namespace Segment\Model\production\SQL;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


class SqlColumn extends \Segment\Model\Column
{
    private $column;
    
    /**
     * SQL requires columns to be strings. Column references require table.column syntax
     * @param SqlTable $table
     * @param \Segment\Model\production\StringValue $column
     */
    public function __construct(\Segment\Model\Table $table, \Segment\Model\production\StringValue $column)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        $this->db = $table->getDb();
        $this->table = $table->getTable();
        $this->column = $column;
        $this->values[] = "{$this->table}.{$this->column}";
        $this->count++;
    }
    
    public function getColumn()
    {
        return $this->fetchColumn();
    }
    
    private function fetchColumn()
    {
        return $this->column;
    }
    
    public function getAddendumColumn()
    {
        return "{$this->table}.{$this->column}";
    }

    public function getDeleteColumn()
    {
        return "{$this->table}.{$this->column}";
    }

    public function getInsertColumn()
    {
        return "{$this->table}.{$this->column}";
    }

    public function getReturnColumn()
    {
        error_log(__METHOD__ . " the return column: {$this->table}.{$this->column}");
        return "{$this->table}.{$this->column}";
    }

    public function getUpdateColumn()
    {
        return "{$this->table}.{$this->column}";
    }

    public function getWhereColumn()
    {
        return "{$this->table}.{$this->column}";
    }

    public function getFromColumn()
    {
        return "{$this->table}.{$this->column}";
    }

}