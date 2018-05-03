<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class TableLink
{
    private $table1;
    private $table2;
    private $link;
    
    public function __construct(\Segment\Model\Table $table1, \Segment\Model\Table $table2, \Segment\Model\Column $link)
    {
        $this->table1 = $table1;
        $this->table2 = $table2;
        $this->link = $link;
    }
}