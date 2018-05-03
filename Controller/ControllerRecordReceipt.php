<?php

namespace Segment\Controller\production;

class ControllerRecordReceipt implements \Segment\Controller\Controller
{
    use \Segment\utilities\FunctionSetter;
    
    private $ready = FALSE;
    /**
     * to be filled with Controller wrapper
     */
    protected $wrapper;
    
    /**
     * sets wrapper Records collection
     * @param \Segment\utilities\Record $model_tables Variable-length variable.
     * @var Controller $this
     */
    //abstract public function organizeRecords(\Segment\utilities\Record ...$model_tables);
    
    /**
     * public function getSegmentType
     * @var Controller $this
     * @returns \Segment\View\Segment
     */
    //abstract public function getSegmentType();
    
    /**
     * public function initializeSegment
     * @param \Segment\View\Segment $segment
     * @returns \Segment\View\Segment
     * @var Controller $this
     */
    //abstract public function initializeSegment(\Segment\View\Segment $segment);
    
    public function getId()
    {
        return $this->wrapper->getId();
    }

    public function getRecords()
    {
        return $this->wrapper->getRecords();
    }
    
    /**
     * Sets the parent Controller object for this instance upon which methods will call.
     * @param \Segment\Controller\Controller $wrapper
     * @return boolean
     */
    public function setWrapper(\Segment\Controller\Controller $wrapper)
    {
        $answer = FALSE;
        if(!$this->ready&&($wrapper instanceof \Segment\Controller\Controller)){
            $this->wrapper = $wrapper;
            $answer = TRUE;
        }
        return $answer;
    }

    public function execute()
    {
        if($this->isInstanceFunction('organizeRecords'))
            $this->organizeRecords($this->getRecords());
        
        if($this->isInstanceFunction('getSegmentType') && $this->isInstanceFunction('initializeSegment'))
            return $this->initializeSegment($this->getSegmentType());
    }

    public function getDescription($table = FALSE): DbDescription
    {
        return $this->wrapper->getDescription($table);
    }

    public function getRest(): \Segment\utilities\production\Rest
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

