<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time()- (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");


trait search_value
{
    protected $values = [];
    protected $count = 0;

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     *
     * @return integer
     */
    public function getValuesQuantity()
    {
        return $this->count;
    }

    public function __toString()
    {
        $values = $this->getValues();
        $answer = '';
        for($i=0,$max=count($values);$i<$max;$i++){
            if(empty($answer))
                $answer = $values[$i] . "";
            else
                $answer .= ", $values[$i]";
        }
        return (string)$answer;
    }
}