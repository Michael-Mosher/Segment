<?php

namespace Segment\Controller\production;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

class DeleteHttpRequest extends \Segment\Controller\RestRequest 
{
    use \Segment\Controller\SearchRestRequest;

    private $table = array();
    
    /**
     * Only Search has Return, but Put and Delete also have Search.
     * {
            "SEARCH":{
                "AND|OR|NOCONJ":[
                    {
                        "EQUAL...NBETWEEN":{
                            "field_name":(doesn't have to be an array, depends on search command)[
                                (mixed, can be an associative array that starts a new SEARCH)"val1",
                                "val2"
                            ],
                            "exclusive_range" : (bool)true
                        }
                    }
                ]
            },
            "DELETE":{
                "table" : (string) "table_name"
            }
        }
     */

    /**
     * ""
     * @param array $rest_arguments Associative array from Rest DELETE request.
     *         Should contain name of the field this deletion searches upon as key, and points to the search argument
     */
    public function __construct(array $rest_arguments)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if (count($rest_arguments) === 1)
            $this->addSearchCell(key($rest_arguments), $rest_arguments[key($rest_arguments)]);
        else
            throw new \LogicException(__METHOD__ .
                    " requires a size one associative array with a non-empty string"
                    . " key and a non-object, non-array, non-null value. Provided {$rest_arguments}");
    }

    /**
     * Set table row to be deleted from.
     * @param string $table
     */
    public function setTable($table)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if ($this->count === 1 && count($this->table) === 0) {
            $this->table[0] = $table;
        }
    }

    public function get()
    {
        return $this->getSearchTuple();
    }

    public function next()
    {
        return $this->searchNext();
    }

    public function rewind()
    {
        $this->searchRewind();
    }

}