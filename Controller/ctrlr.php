<?php

namespace Segment\Controller\production;

session_start();
$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
header("HTTP/1.1 404 File Not Found");
header("Content-Type: text/plain");
header("Content-Length: 0");


/*
  class ModelCallerOptionSearch extends \Segment\Controller\ModelCallerOption
  {
  const equal = 1;
  const nequal = 3;
  const equalany = 5;
  const greater = 7;
  const greateq = 11;
  const greatany = 13;
  const greatall = 17;
  const lesser = 19;
  const lesseq = 23;
  const lessany = 29;
  const lessall = 31;
  const between = 37;
  const nbetween = 41;


  }

  class ModelCallerOptionReturn extends \Segment\Controller\ModelCallerOption
  {
  const field_set = 100;
  const field_count = 1000;
  const field_avg = 10000;
  const field_mode = 100000;
  const field_median = -100;
  const field_firstq = -1000;
  const field_thirdq = -10000;
  } */


//abstract class ControllerAbstract implements \Segment\Controller\Controller
//{
//
//    protected $security;
//    protected $id;
//    protected $rest;
//    protected $records;
//    protected $crest;
//    protected $crecords;
//    private $db_description_fetch;
//    protected $namespace = __NAMESPACE__;
//
//    /* array of Permeator, traverse calling each to fill the array of ModelOut
//     *  pass by reference
//     * Array for ModelOut
//     * Run SegmentCreator over each ModelOut array entry
//     */
//
//    public function __construct(Security $security)
//    {
//        $this->security = $security;
//        $this->rest = $this->security->getRest();
//        $this->id = $this->rest->hasKey('x') ? $this->rest->getValue('x') : $this->id;
//        $fetch_name = \strlen(__PROJECT_NAME__) > 0 && \stripos($this->namespace, __PROJECT_ACRONYM__) ?
//                $this->namespace . \ucfirst(\strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch'
//                : $this->namespace . ($s = '\\')
//                . ($temp = \strlen(__PROJECT_ACRONYM__) > 0 ? \ucfirst(__PROJECT_ACRONYM__) . $s : '')
//                . \ucfirst(\strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch';
//        $this->db_description_fetch = new $fetch_name();
//    }
//
//    abstract public function prepareRest();
//
//    /**
//     * JSON-formatted list of columns to be used in Wildcard search.
//     * @return string JSON-formatted string.
//     * @throws \InvalidArgumentException
//     */
//    private function getSearchFields()
//    {
//        $id = $this->getId();
//        if (!is_string($id)) {
//            throw new \InvalidArgumentException('getSearchFields first argument must be text string.'
//                    . ' First argument given ' . $id);
//        }
//        $string_location = 'Segment\'' . '__SEARCHFIELDS_' . strtoupper($id) . '__';
//        $answer = json_decode($string_location, TRUE);
//        return $answer;
//    }
//
//    /**
//     * Get DbDescription object for columns of one or all tables in source database.
//     * @param string $table Optional. Name of desired DB table description
//     * @return \Segment\utilities\DbDescription
//     */
//    public function getDescription($table = FALSE)
//    {
//        return $this->db_description_fetch->getDescription($table);
//    }
//
//    public function getRest()
//    {
//        return $this->rest;
//    }
//
//    public function setRest(\Segment\utilities\Rest $rest)
//    {
//        $this->rest = $rest;
//    }
//
//    public function getUser()
//    {
//        return $this->security->getUser();
//    }
//
//    public function getViewClass()
//    {
//        return $this->security->getViewClass();
//    }
//
//    public function isAuthorizationNeeded()
//    {
//        return $this->security->requiresAuthentication();
//    }
//
//    public function getId()
//    {
//        return $this->id;
//    }
//
//    public function setRecord(\Segment\utilities\Record $record, $index = NULL)
//    {
//        if (is_integer($index))
//            $this->records[$index] = $record;
//        else
//            $this->records[] = $record;
//    }
//
//    /**
//     * @param integer $index
//     */
//    public function unsetRecord($index)
//    {
//        if (isset($this->records[$index]))
//            unset($this->records[$index]);
//    }
//
//    /**
//     * @return Array<\Segment\utilities\Record> The collected database records
//     */
//    public function getRecords()
//    {
//        $this->records;
//    }
//
//    /**
//     *
//     * @param \Segment\Controller\ControllerRestReceipt $crr
//     */
//    public function addControllerRestReceipt(ControllerRestReceipt $crr)
//    {
//        if ($crr->setWrapper($this))
//            $this->crest = $this->crest ?? $crr;
//    }
//
//    /**
//     *
//     * @param \Segment\Controller\ControllerRestReceipt $crr
//     */
//    public function addControllerRecordReceipt(ControllerRecordReceipt $crr)
//    {
//        if ($crr->setWrapper($this))
//            $this->crecords = $this->crecords ?? $crr;
//    }
//
//}
