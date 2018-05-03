<?php
namespace Segment\Controller\Testing;

class SearchModelCallOrchestrator extends \PHPUnit\Framework\TestCaseTest implements \Segment\Controller\ModelCallOrchestrator
{
    /**
     *
     * @var \Segment\Controller\RestRequest
     */
    private $build_args;
    /**
     * The name of an executable that will take string, that is a RestRequest operator,
     *         and \Segment\Controller\Parameter and return array for
     *         \Segment\Model\production\ModelCaller::makeClauseBlind.
     * @var string Executable string that resolves to a closure.
     */
    private $options_getter;
    /**
     * The DB's Fields Schema
     * @var \Segment\utilities\DbDescription
     */
    private $db_descrip;
    /**
     * A queue for sub-clauses that makes the \Segment\Model\production\Statement that is passed to PDO.
     * @var \Segment\Model\production\ModelCaller
     */
    private $caller;
    /**
     *
     * @var \Segment\Controller\CacheWrapper
     */
    private $cache;
    
    public function __construct(
            \Segment\Controller\RestRequest $request, \Segment\utilities\DbDescription $db_descrip,
            \Segment\Model\production\ModelCaller &$mc, \Closure $options_arg_getter_func, \Segment\Controller\CacheWrapper &$cache_handler)
    {
        $this->build_args = $request;
        $this->options_getter = $options_arg_getter_func;
        $this->db_descrip = $db_descrip;
        $this->caller = $mc;
        $this->cache = $cache_handler;
        
        
    }

    /**
     * Only Search has Return, but Put and Delete also have Search.
     * {
            "SEARCH":{
                "AND|OR|NOCONJ":[
                    {
                        "EQUAL...NBETWEEN":{
                            "field_name":[
                                (mixed)"val1",
                                "val2"
                            ],
                            "exclusive_range":(bool)true
                        }
                    }
                ],
                "limit_start":(float)0.0,
                "limit_amount":(float)1.0,
                "limit_start_abs":(bool)false,
                "limit_amount_abs":(bool)false,
                "order":{
                    "field_name":(number>0:ASC,number<0:DESC)-1
                },
                "return":{
                    "NORMAL...THIRDQ":(string)"field_name",
                    "ALIAS":(string)"string"
                }
            }
        }
     */
    
    
    /**
     * @return array Array<\Segment\utilities\production\Record>
     * @test
     */
    public function execute(): array
    {
        /**
         * @var \Segment\Controller\Parameter
         */
        $tuple;
        $this->build_args->rewind();
        for(;$tuple = $this->build_args->get(); $this->build_args->next()):
            if(!isset($tuple) || is_null($tuple))
                $tuple = $this->build_args->get();
            $this->assertTrue(is_a($tuple, "\Segment\Controller\Parameter"),
                    __METHOD__ . " the build args outputs \Segment\Controller\Parameter");
            $field = $tuple->getField();
            $operator = $tuple->getOperator();
            $value = $tuple->getValue();
            $options = $this->options_getter($operator, $tuple);
            
            $tables = $this->db_descrip->getTables($field);
            $this->assertTrue(is_array($tables));
            $this->assertGreaterThan(0, count($tables));
            for ($i=0; $i<count($tables)-1; $i++):
                $this->caller->makeClauseBlind($operator, $field, $tables[$i], $value,
                        $options);
            endfor;
        endfor;
    }

    public function getRestRequest(): \Segment\Controller\RestRequest
    {
        return $this->build_args;
    }

}

