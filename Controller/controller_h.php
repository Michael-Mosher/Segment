<?php

namespace Segment\Controller;

session_start();
$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
header("HTTP/1.1 404 File Not Found");
header("Content-Type: text/plain");
header("Content-Length: 0");

/*
abstract class PutModelCaller extends ModelCaller
{
    protected function purgePutFields(array $requests)
    {
        
    }
    
    protected function purgePostFields(PostRestRequest $post)
    {
        foreach($post as $pfield => $pvalue){
            $found = FALSE;
            foreach ($this->insert_fields as $table => $columns){
                if(array_search($columns, $pfield)){
                        $found = TRUE;
                        break;
                }
            }
            if(!$found){
                $post->unsetColumn($pfield);
                $this->purgePostFields($post);
                break;
            }
        }
    }
}*/

/*abstract class ModelCallerOption
{
    private $options = array();
    private $type = NULL;
    
    /**
     * Construct
     * @param integer $option_type
     * @param mixed $options Variable length.
    
    abstract public function __construct($option_type, ...$options);
    
    /**
     * Returns the 
     * @return integer
     
    public function getType()
    {
        return $this->type;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
}*/

//abstract class ControllerAbstract implements Controller
//{
//    protected $security;
//    protected $id;
//    protected $rest;
//    protected $records;
//    protected $crest;
//    protected $crecords;
//    private $db_description_fetch;
//    protected $namespace = __NAMESPACE__;
//    /* array of Permeator, traverse calling each to fill the array of ModelOut
//     *  pass by reference
//     * Array for ModelOut
//     * Run SegmentCreator over each ModelOut array entry
//    */
//    
//    public function __construct(production\Security $security)
//    {
//        $this->security = $security;
//        $temp_rest = $this->security->getRest();
//        $this->id = $temp_rest->hasKey('x') ? $temp_rest->getValue('x') : $this->id;
//        $fetch_name = strlen(__PROJECT_NAME__)>0&&stripos($this->namespace, __PROJECT_ACRONYM__)
//                ? $this->namespace . ucfirst(strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch'
//                : $this->namespace . ($s = '\\')
//                . ($temp = strlen(__PROJECT_ACRONYM__)>0 ? ucfirst(__PROJECT_ACRONYM__) . $s : '')
//                . ucfirst(strtolower(__PROJECT_ACRONYM__)) . 'DbDescripFetch';
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
//        if(!is_string($id)){
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
//     * @return DbDescription
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
//        if(is_integer($index))
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
//        if(isset($this->records[$index]))
//                unset($this->records[$index]);
//    }
//    
//    /**
//     * @return Array<\Segment\utilities\Record> The collected database records
//     */
//    public function getRecords()
//    {
//        $this->records;
//    }
//}