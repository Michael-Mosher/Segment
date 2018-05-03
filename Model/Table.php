<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class Table
{
    protected $db;
    protected $table;

    abstract public function __construct( \Segment\Model\production\StringValue $table, \Segment\Model\production\StringValue $db);

    public function getDb()
    {
        return $this->db;
    }

    public function getTable()
    {
        return $this->table;
    }
}