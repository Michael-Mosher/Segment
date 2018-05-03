<?php

namespace Segment\Controller\production;

/**
 * Description of MCOrchestratorClassNamesGetter
 *
 * @author Michael-Mosher
 */
class MCOrchestratorClassNamesGetter implements \Segment\Controller\MCOrchestratorClassNamesGetterInterface
{
    private $mc_names = [];
    private $iterator = 0;
    
    /**
     * \ArrayAccess<string> queue of proto-ModelCallOrchestrator string names
     *         that still require AbstractNamesGetter.
     * @throws \InvalidArgumentException
     */
    public function __construct(string $rest_func_name, bool $auth_required, string $model_call_name_csv, string $request_type)
    {
        $answer = new \SplDoublyLinkedList();
        if($auth_required){
            $rest_func_name .= "NonSecure";
            $answer->push($rest_func_name);
            return $answer;
        }
        $array_rows = str_getcsv($model_call_name_csv,"\n", '"', '"');
        array_walk($array_rows,
                function(&$v, $k){
            $v = str_getcsv($v, ',', '"', '"');
                }
        );
        $family_found = FALSE;
        foreach($array_rows as $row){
            if(isset($row[0]) && $rest_func_name === $row[0] && !empty($row[0])){
                $family_found = TRUE;
                $answer->push($row[1]);
            } else if($family_found)
                break;
        }
        if($answer->isEmpty()){
            $answer->push(__DEFAULT_MODEL_CALL_ORCHESTRATOR__);
        }
        $answer->push($request_type);
        $answer->rewind();
        $this->mc_names = $answer;
    }
    

    public function next(): boolean
    {
        $this->iterator++;
        return isset($this->mc_names[$this->iterator]);
    }

    public function rewind()
    {
        $this->iterator = 0;
    }

    public function getModelCallNames(string $x, bool $auth_required, string $model_call_name_csv): \ArrayAccess {
        
    }

}
