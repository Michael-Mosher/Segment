<?php

namespace Segment\Controller\production;

use Segment\Controller\Controller;
use Segment\Controller\ModelCallOrchestrator;
use Segment\utilities\DbDescription;
use Segment\utilities\FunctionSetter;
use Segment\utilities\production\Rest;
use Segment\utilities\User;
use Segment\View\production\ViewClass;

class ControllerRestReceipt implements Controller
{
    use FunctionSetter;
    
    private $ready = FALSE;
    
    /**
     * to be filled with Controller wrapper
     */
    protected $wrapper;
    
    /**
     * public  function getModelCallArgs
     * @return \Segment\utilities\production\RestRequest
     * @var Controller $this
     */
    //abstract public  function getModelCallArgs();
    
    /**
     * public function instantiateModelCall
     * @param \Callable $model_call name of ModelCallOrchestrator class
     * @param \Segment\utilities\production\RestRequest $args
     * @return ModelCallOrchestrator
     * @var Controller $this
     */
    //abstract public function instantiateModelCall();
    
    /**
     * public function getModelCallNames()
     * @return ArrayAccess<string> queue of ModelCallOrchestrator string names
     * @var Controller $this
     */
    //abstract public function getModelCallNames();
    
    /**
     * public function callModel
     * @param ModelCallOrchestrator $call
     * @var Controller $this
     */
    //abstract public function callModel(ModelCallOrchestrator $call);
    
    public function getId()
    {
        return $this->wrapper->getId();
    }

    /**
     * Adds Record to collection of Record in Controller.
     * @param \Segment\Model\production\Record $record
     * @param int $index
     */
    public function setRecord(\Segment\Model\production\Record $record, $index = NULL)
    {
        $this->wrapper->setRecord($record, $index);
    }
    
    /**
     * Defines parent Controller for this instance to which methods will call upon.
     * @param Controller $wrapper
     * @return boolean
     */
    public function setWrapper(Controller $wrapper)
    {
        $answer = FALSE;
        if(!$this->ready&&($wrapper instanceof Controller)){
            $this->wrapper = $wrapper;
            $answer = TRUE;
        }
        return $answer;
    }

    public function execute()
    {
        if($this->ready && $this->isInstanceFunction('getModelCallNames') && $this->isInstanceFunction('callModel')
                && $this->isInstanceFunction('getModelCallArgs') && $this->isInstanceFunction('instantiateModelCall')){
            $mc_names = $this->getModelCallNames();
            $mc_args = $this->getModelCallArgs();
            for($i=0, $max = count($mc_names)-1; $i<$max; $i++){
                $mc_orchestrator = $this->instantiateModelCall($mc_names, $mc_args);
                $this->callModel($mc_orchestrator);
            }
        }
    }

    public function getDescription($table = FALSE): DbDescription
    {
        return $this->wrapper->getDescription($table);
    }

    public function getRecords(): array
    {
        return $this->wrapper->getRecords();
    }

    public function getRest(): Rest
    {
        return $this->wrapper->getRest();
    }

    public function getUser(): User
    {
        return $this->wrapper->getUser();
    }

    public function getViewClass(): ViewClass
    {
        return $this->wrapper->getViewClass();
    }

    public function isAuthorizationNeeded(): boolean
    {
        return $this->wrapper->isAuthorizationNeeded();
    }

    public function setRest(\Segment\utilities\production\Rest $rest)
    {
        $this->wrapper->setRest($rest);
    }

    public function unsetRecord($index)
    {
        $this->wrapper->unsetRecord($index);
    }

}
