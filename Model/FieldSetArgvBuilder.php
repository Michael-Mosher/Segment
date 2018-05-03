<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class FieldSetArgvBuilder extends InputOutputArgvBuilder
{
    public function setAddendum($addendum, $row_current, $row_increment)
    {
        if(!is_int($row_current)||!$row_current>-1)
            $row_current = FALSE;
        if(!is_int($row_increment)||!$row_increment>0||!$row_increment<=__ROW_MAXIMUM__)
            $row_increment = __ROW_INCREMENT__;
        if(!(is_string($addendum)&&strlen($addendum)>1))
            throw new \InvalidArgumentException(
                    'setAddendum function argument must be of type text string.'
                    . ' Argument: '. print_r($addendum,true));
        if(strpos($addendum, 'LIMIT')===FALSE){
            if(!$row_current)
                $addendum = $addendum;
            else if($row_current)
                $addendum .= ' LIMIT ' . $row_current . ', ' . $row_increment;
        }
        $this->addendum = $addendum;
        return $this;
    }
}