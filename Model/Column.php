<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


abstract class Column
{
    use search_value;

    abstract public function __construct(Table $table, \Segment\Model\production\StringValue $column);
    abstract public function getColumn();

    public function getTable()
    {
        return $this->table;
    }

    public function getDb()
    {
        return $this->db;
    }

    abstract public function getReturnColumn();

    abstract public function getFromColumn();

    abstract public function getWhereColumn();

    abstract public function getInsertColumn();

    abstract public function getUpdateColumn();

    abstract public function getDeleteColumn();

    abstract public function getAddendumColumn();
}