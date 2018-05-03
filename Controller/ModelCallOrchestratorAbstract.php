<?php

namespace Segment\Controller;

abstract class ModelCallOrchestratorAbstract implements ModelCallOrchestrator
{
    protected $observers = [];
    protected $observer_keys = [];
    protected $build_args;
    
    public function register(\Segment\utilities\Observer $o, \Segment\utilities\production\ObservableEvent $trigger)
    {
        if (isset($trigger) && !is_null($trigger)){
            $this->observers[spl_object_hash($trigger)] = $o;
            $this->observer_keys[spl_object_hash($trigger)] = $trigger;
        } else
            $this->observers[] = $o;
    }
    
    public function pushToObservers(string $file, string $method, int $observable_event)
    {
        $this_reflection = new \ReflectionClass($this);
        $const_array = $this_reflection->getConstants();
        if(array_search($observable_event, $const_array, TRUE)!==FALSE && strlen($method)>0 && strlen($file)>0){
            $oe = new \Segment\utilities\production\ObservableEvent($file, $method, $observable_event);
            foreach($this->observers as $observer){
                $observer->trigger($oe, $this);
            }
        }
    }
    
    public function getRestRequest()
    {
        return $this->build_args;
    }

    public function getRestValue(string $key)
    {
        return $this->rest->hasKey($key) ? $this->rest->getValue($key) : NULL;
    }
    
    /**
     * Will increment the REST record to the next pair and return an associative array 
     *         where the key is the key and the value is the value if there is an entry left.
     *         Otherwise, the method will return FALSE.
     * @return array|bool
     */
    public function getNextRestPair()
    {
        $this->rest->next();
        if($this->rest->valid())
            return [$this->rest->key() => $this->rest->current()];
        else {
            return FALSE;
        }
            
    }
}