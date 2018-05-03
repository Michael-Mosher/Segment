<?php

namespace Segment\Controller;

//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

interface Controller
{
    // Receive security verified input

    // Respond to NON_MODEL call
    /* Response may deviate from zone to zone
     *  zone class name w/ "NonSecure" at end
     */
    
    // Process Input REST
    
    // Determine how many, and what type, model calls
    /* High level logic, meta-wrapper around input handling and worker instances
     *  and instantiation calls
     * Check database for names of classes of ModelCaller
     */
    /**
     * @param NULL
     * @return (Object|FALSE) success returns Object, else FALSE
     */
    public function execute();
    
    /**
     * @param NULL
     * @return string returns string enumeration of an ID
     */
    public function getId();
    
    /**
     * Add, or replace, a record in array of records
     * @param \Segment\utilities\Record $record
     * @param integer $index Optional. Default NULL
     
    public function setRecord(\Segment\utilities\Record $record, $index = NULL);
    */
    /**
     * Get DbDescription object for columns of one or all tables in source database.
     * @param string $table Optional. Name of desired DB table description
     * @return \Segment\utilities\DbDescription
     */
    public function getDescription($table = FALSE);
    
    /**
     * @return array<Record>
     */
    public function getRecords();
    
    /**
     * @param (string|integer) $index
     * @throws InvalidArgumentException
     */
    public function unsetRecord($index);
    
    /**
     * @param Rest $rest
     */
    public function setRest(\Segment\utilities\Rest $rest);
    
    /**
     * @return Rest
     */
    public function getRest();
    
    /**
     * @return User
     */
    public function getUser();

    /**
     * @return String
     */
    public function getViewClass();
    
    /**
     * @return boolean
     */
    public function isAuthorizationNeeded();
    
    // Call Model
    
    //Package data returned from Model
    
    // Return Package
}