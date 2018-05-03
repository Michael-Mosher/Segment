<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


interface DbDescripitonFetcher //Short name DbDescripFetch. Use short name as root name of concrete implementations.
{
    /**
     * Return Records of administrative descriptions of all of the database columns
     *     via the DbDescripton class.
     * @param string $table Optional. Name of a DB table. Will limit description to that table's columns
     * @return DbDescription
     */
    public function getDescription($table = FALSE);
}