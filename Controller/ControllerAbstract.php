<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

abstract class ControllerAbstract implements Controller
{
    protected $security;
    protected $id;
    protected $rest;
    protected $records;
    public $crest;
    public $crecords;
    private $db_description_fetch;
    protected $namespace = __NAMESPACE__;
    /* array of Permeator, traverse calling each to fill the array of ModelOut
     *  pass by reference
     * Array for ModelOut
     * Run SegmentCreator over each ModelOut array entry
    */

    public function __construct(\Segment\Controller\production\Security $security)
    {
        $this->security = $security;
        $this->rest = $this->security->getRest();
        $this->id = is_a($this->rest, '\Segment\utilities\Rest', TRUE)&&$this->rest->hasKey('x') ? $this->rest->getValue('x') : $this->id;
        if(isset($_SESSION['meta_db'])){
            $this->db_description_fetch = \unserialize($_SESSION['meta_db']);
        } else {
            $fetch_name = $this->security->getClassName('DbDescripFetch', __CONTROLLER_PRODUCTION_NS__);
            $this->db_description_fetch = new $fetch_name();
            $_SESSION['meta_db'] = \serialize($this->db_description_fetch);
        }
    }

    abstract public function prepareRest();

    /**
     * JSON-formatted list of columns to be used in Wildcard search.
     * @return string JSON-formatted string.
     * @throws \InvalidArgumentException
     */
    private function getSearchFields()
    {
        $id = $this->getId();
        if(!is_string($id)){
            throw new \InvalidArgumentException('getSearchFields first argument must be text string.'
                    . ' First argument given ' . $id);
        }
        $string_location = 'Segment\'' . '__SEARCHFIELDS_' . strtoupper($id) . '__';
        $answer = json_decode($string_location, TRUE);
        return $answer;
    }

    /**
     * Get DbDescription object for columns of one or all tables in source database.
     * @param string $table Optional. Name of desired DB table description
     * @return \Segment\utilities\DbDescription
     */
    public function getDescription($table = FALSE)
    {
        return $this->db_description_fetch->getDescription($table);
    }

    public function getRest()
    {
        return $this->rest;
    }

    public function setRest(\Segment\utilities\Rest $rest)
    {
        $this->rest = $rest;
    }

    public function getUser()
    {
        return $this->security->getUser();
    }

    public function getViewClass()
    {
        return $this->security->getViewClass();
    }

    public function isAuthorizationNeeded()
    {
        return $this->security->isAuthenticationRequired();
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getBrowserType() : string
    {
        return $this->security->getBrowserType();
    }
    
    /**
     * Returns the client's browser version as float.
     * @return float
     */
    public function getBrowserVersion() : float
    {
        return $this->security->getBrowserVersion();
    }
    
    public function getIpAddress() : string
    {
        return $this->security->getClientIp();
    }
    
    
    public function isCookiesAccepted() : bool
    {
        return $this->security->isCookiesAccepted();
    }
    
    public function isJavaScriptAccepted() : bool
    {
        return $this->security->isJavaScriptAccepted();
    }

    public function setRecord(\Segment\utilities\Record $record, $index = NULL)
    {
        if(is_integer($index))
            $this->records[$index] = $record;
        else
            $this->records[] = $record;
    }

    /**
     * @param integer $index
     */
    public function unsetRecord($index)
    {
        if(isset($this->records[$index]))
                unset($this->records[$index]);
    }

    /**
     * @return Array<\Segment\utilities\Record> The collected database records
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     *
     * @param \Segment\Controller\production\ControllerRestReceipt $crr
     */
    public function addControllerRestReceipt(production\ControllerRestReceipt $crr)
    {
        if($crr->setWrapper($this))
            $this->crest = $this->crest ?? $crr;
    }

    /**
     *
     * @param \Segment\Controller\production\ControllerRecordReceipt $crr
     */
    public function addControllerRecordReceipt(production\ControllerRecordReceipt $crr)
    {
        if($crr->setWrapper($this))
            $this->crecords = $this->crecords ?? $crr;
    }
}