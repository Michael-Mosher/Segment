<?php

namespace Segment\Model;
//$_SESSION['CREATED'] = time() - (__SESSION_EXPIRATION__*31);
//$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
//header("HTTP/1.1 403 Access Forbidden");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class DelayedBuilder
{
    protected $queue = [];

    /**
     * Add the name of a StatementBuilder public method to the builder queue.
     * @param string|callable $func_n
     * @param mixed $args Variable length argument
     */
    public function setCallQueue($func_n, ...$args)
    {
        error_log(__METHOD__ . " before method exists check. Does it: " . print_r(method_exists($this, $func_n), TRUE)
                . " is args an array, what size, and if so and size at least one, is the first arg an array"
                . " " . print_r(is_array($args), TRUE) . " " . print_r(count($args), TRUE));
        if(is_array($args) && count($args)>0){
            error_log(__METHOD__ . " " . print_r(is_array($args[0]), TRUE));
        }
        if(method_exists($this, $func_n)){
            $this->queue[] = array_merge([$func_n], \Segment\utilities\Utilities::flattenArray($args));
        } else {
            throw new \InvalidArgumentException(__METHOD__
                    . " zeroeth argument required to be either string or callable. Was provided: "
                    . print_r($func_n)
            );
        }
    }

    /**
     * 
     * @return array First one or two indices are callable, last is array of arguments for callable.
     */
    protected function getCallQueue()
    {
        return \Segment\utilities\Utilities::arrayCopy($this->queue);
    }

    public function callQueue()
    {
        $queue = $this->getCallQueue();
        try{
            foreach($queue as $entry){
                $call = $entry[0];
                $args = array_slice($entry, 1);
                yield [$call => $args];
            }
        } finally {
            $this->queue = array();
        }
    }
}