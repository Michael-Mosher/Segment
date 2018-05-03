<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class PostHttpRequest extends \Segment\Controller\RestRequest
{
    use \Segment\Controller\SetRestRequest;

    /**
     *
     * @var array Associative array where the keys are fields and the values the values of the new record
     */
    private $insert_fields = array();

    /**
     * Creates an object that represents one field's values in a POST REST request.
     */
    public function __construct(array $arg)
    {
        //x=something_post,
        //args : {
        //        START HEREposts:[
        //                {
        //                        field:field_1,
        //                        value:"value0",
        //                },{
        //                        field:field_2,
        //                        value:999
        //                }
        //        ]
        //}
        
        foreach($rest as $operator => $array):
            if(strtolower(trim($operator))==="requests" || strtolower(trim($operator))==="posts"){
                foreach($array as $field => $value):
                    $this->addSetCell($field, $value, $$operator);
                endforeach;
            }

        endforeach;
    }

    public function get(): SetParameter
    {
        return $this->getSetTuple();
    }

    public function count():int
    {
        return $this->getSetCount();
    }

    public function next():bool
    {
        return $this->setNext();
    }

    public function rewind()
    {
        $this->setRewind();
    }

}