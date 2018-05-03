<?php
namespace Segment\Model\production;
//session_start();
//$_SESSION['LAST_ACTIVITY'] = time() - (__SESSION_EXPIRATION__*31);
//header("HTTP/1.1 404 File Not Found");
//header("Content-Type: text/plain");
//header("Content-Length: 0");

/**
 * Class for building Statement objects. Statement objects query the DAO.
 */
class StatementBuilder extends \Segment\Model\InputOutput implements \Segment\Model\SelectClauseBuilder,
        \Segment\Model\SearchClauseBuilder, \Segment\Model\SetClauseBuilder
{
    /**
     * The $db_type variables are used to determine class name of builders and wrappers.
     * The first is most specific, the larger numbers are less specific, so if the first
     * one doesn't yield a legitimate class name then the second should be used, and so on.
     */
    
    private $query_type;
//    private $db_type_1;
//    private $db_type_2;
//    private $return_builder;
//    private $db_list = array();
//    private $tables = array();
//    private $where_builder;
//    private $set_builder;
//    private $from_builder;
//    private $delete_builder;

    const QUERY_TYPE_GET = 2;
    const QUERY_TYPE_POST = 4;
    const QUERY_TYPE_PUT = 8;
    const QUERY_TYPE_DELETE = 16;
    
    /**
     * Set either GET, POST, PUT, or DELETE
     * @param integer $type
     */
    public function __construct($type)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if($type===self::QUERY_TYPE_GET||$type===self::QUERY_TYPE_POST||$type===self::QUERY_TYPE_PUT
                ||$type===self::QUERY_TYPE_DELETE){
            $this->db_type_1 = __RDB_SYSTEM__;
            $this->db_type_2 = __RDB_SYSTEM2__;
            $this->query_type = $type;
            //$this->return_builder = new __FROM();
            if($this->query_type!==self::QUERY_TYPE_POST){
                $class_name = __WHERE_BUILDER__;
                $this->where_builder = new $class_name($this);
                $from_class_name = $this->getFromClauseBuilder();
                $this->from_builder = new $from_class_name();
            }
            if($type===self::QUERY_TYPE_POST){
                $class_name = __INSERT_SET_BUILDER__;
                $this->set_builder =  new $class_name();
            } else if($type===self::QUERY_TYPE_PUT){
                $class_name = __UPDATE_SET_BUILDER__;
                $this->set_builder = new $class_name();
            } else if($type===self::QUERY_TYPE_DELETE) {
                $class_name = __DELETE_BUILDER__;
                $this->delete_builder = new $class_name();
            } else if($type===self::QUERY_TYPE_GET){
                $class_name = __RETURN_CLAUSE_BUILDER__;
                $this->return_builder = new $class_name($this);
            }
        } else
            throw new \LogicException(__METHOD__.
	            ' first argument, $type, must be a constant of ' . __CLASS__
		    . '. Provided: ' . print_r($type, TRUE));
    }
    
    /**
     * Construct helper that returns the name of the relevant FromClauseBuilder class.
     * @return string The class name of the From Clause Builder for the current call type
     */
    private function getFromClauseBuilder()
    {
        $class_name = "StdClass";
        switch ($this->getTypeName())
        {
            case 'GET':
                $class_name = __GET_FROM_CLAUSE_BUILDER__;
                break;
            case 'PUT':
                $class_name = __PUT_FROM_CLAUSE_BUILDER__;
                break;
            case 'POST':
                $class_name = __POST_FROM_CLAUSE_BUILDER__;
                break;
            case 'DELETE':
                $class_name = __DELETE_FROM_CLAUSE_BUILDER__;
                break;
            default:
                break;
        }
        return $class_name;
    }
    
    public function getTypeName()
    {
        $answer = FALSE;
        switch ($this->query_type)
        {
            case self::QUERY_TYPE_DELETE:
                $answer = 'DELETE';
                break;
            case self::QUERY_TYPE_GET:
                $answer = 'GET';
                break;
            case self::QUERY_TYPE_POST:
                $answer = 'POST';
                break;
            case self::QUERY_TYPE_PUT:
                $answer = 'PUT';
                break;
            
        }
        return $answer;
    }
    
    /**
     * Convert string representing request method type to an integer constant of StatementBuilder.
     * @param string $type
     * @return integer
     */
    public static function getQueryType(string $type)
    {
        $answer = -1;
        switch (strtolower($type)) {
            case 'get':
            case 'search':
            case 'wild': $answer = self::QUERY_TYPE_GET;
                break;
            case 'post':
            case 'insert': $answer = self::QUERY_TYPE_POST;
                break;
            case 'delete': $answer = self::QUERY_TYPE_DELETE;
                break;
            case 'put':
            case 'update': $answer = self::QUERY_TYPE_PUT;
            default:
                break;
        }
        return $answer;
    }
    
    public function addIntersection(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $this->setDb($first_var->getInputOutput()->getDbs());
        $this->setTable($first_var->getInputOutput()->getDbs());
        foreach($var_comp as $k => $statement){
            $this->setTable($statement->getInputOutput()->getDbs());
            $this->setTable($statement->getInputOutput()->getTables());
        }
        $this->return_builder->setCallQueue('addIntersection',...func_get_args());
    }

    public function addReturnColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnColumn',$clmn);
    }

    public function addReturnMaxColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnMaxColumn',$clmn);
    }

    public function addReturnMeanColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnMeanColumn',$clmn);
    }

    public function addReturnMedianColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnMedianColumn',$clmn);
    }

    public function addReturnMinColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnMinColumn',$clmn);
    }

    public function addReturnModeColumn(\Segment\Model\Column $clmn, \Segment\Model\production\StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnModeColumn',$clmn);
    }

    public function addReturnQuantityRows($absolute_amnt, $amnt, $absolute_start = TRUE, $start = 0)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnQuantityRows',$absolute, $amnt, $start);
    }

    public function addReturnSortColumn(\Segment\Model\Column $clmn, $asc = TRUE)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnSortColumn',$clmn, $asc);
    }

    public function addReturnUniqueColumn(\Segment\Model\Column $clmn)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnUniqueColumn',$clmn);
    }

    public function addSearchEqual(
            \Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        error_log(__METHOD__ . " just before setCallQueue on WhereClauseBuilder");
        $this->where_builder->setCallQueue('addSearchEqual',$clmn, $value, $var_comp, $required_param);
    }
    
    public function addSearchNotEqual(
            \Segment\Model\Column $clmn, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue('addSearchNotEqual',$clmn, $value, $var_comp, $required_param);
    }

    public function addSearchEqualAny(
            \Segment\Model\Column $clmn, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue('addSearchEqualAny',$clmn, $values, $var_comp, $required_param);
    }

    public function addSearchGreater(
            \Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchGreater',$clmn, $exclusive, $value, $var_comp, $required_param);
    }

    public function addSearchGreaterAll(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchGreaterAll',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchGreaterAny(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchGreaterAny',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchLesser(
            \Segment\Model\Column $clmn, $exclusive = TRUE, SingleValue $value = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchLesser',$clmn, $exclusive, $value, $var_comp, $required_param);
    }

    public function addSearchLesserAll(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchLesserAll',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchLesserAny(
            \Segment\Model\Column $clmn, $exclusive = TRUE, AnyAllValues $values = NULL,
            \Segment\Model\Statement $var_comp = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp))
            $this->setDb ($var_comp->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchLesserAny',$clmn, $exclusive, $values, $var_comp, $required_param);
    }

    public function addSearchNotRange(
            \Segment\Model\Column $clmn, $exclusive = TRUE, BetweenValues $values = NULL,
            \Segment\Model\Statement $var_comp1 = NULL, \Segment\Model\Statement $var_comp2 = NULL,
            $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp1))
            $this->setDb ($var_comp1->getInputOutput ()->getDbs());
        if(!empty($var_comp2))
            $this->setDb ($var_comp2->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchNotRange',$clmn, $exclusive, $values, $var_comp1, $var_comp2);
    }

    public function addSearchRange(\Segment\Model\Column $clmn, $exclusive = TRUE,
            BetweenValues $values = NULL, \Segment\Model\Statement $var_comp1 = NULL,
            \Segment\Model\Statement $var_comp2 = NULL, $required_param = TRUE
            )
    {
        $this->setDb($clmn->getDb());
        if(!empty($var_comp1))
            $this->setDb ($var_comp1->getInputOutput ()->getDbs());
        if(!empty($var_comp2))
            $this->setDb ($var_comp2->getInputOutput ()->getDbs());
        $this->setTable($clmn);
        $this->where_builder->setCallQueue(
                'addSearchRange',$clmn, $exclusive, $values, $var_comp1, $var_comp2);
    }

    public function addUnion(\Segment\Model\Statement $first_var, \Segment\Model\Statement ...$var_comp)
    {
        $this->setDb($first_var->getInputOutput()->getDbs());
        $this->setTable($first_var->getInputOutput()->getDbs());
        foreach($var_comp as $k => $statement){
            $this->setTable($statement->getInputOutput()->getDbs());
            $this->setTable($statement->getInputOutput()->getTables());
        }
        $this->return_builder->setCallQueue('addUnion', ...func_get_args());
    }
    
    protected function setDb($db)
    {
        if(!is_integer(array_search($db, $this->db_list)))
            $this->db_list[] = $db;
    }
    
    
    public function get($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __MODEL_PRODUCTION_NS__);
        parent::get($statement_class_name);
    }
    
    public function post($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __MODEL_PRODUCTION_NS__);
        parent::post($statement_class_name);
    }
    
    public function put($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __MODEL_PRODUCTION_NS__);
        parent::put($statement_class_name);
    }

    public function delete($statement_class_name)
    {
        $statement_class_name = $statement_class_name ?? $this->getClassName('Statement', __MODEL_PRODUCTION_NS__);
        parent::delete($statement_class_name);
    }

    public function getStatement()
    {
        $stmt_n = $this->getClassName('Statement', __MODEL_PRODUCTION_NS__);
        return new $stmt_n($this);
    }

    public function addReturnCountColumn(\Segment\Model\Column $clmn, StringValue $alias = NULL)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addReturnCountColumn',$clmn, $alias);
    }

    public function addSet(\Segment\Model\Column $target, \Segment\Model\SearchValues $value)
    {
        $this->setDb($target->getDb());
        $this->setTable($target->getTable());
        $this->set_builder->addSet($target, $value);
        $this->put_values[] = $value;
    }

    public function getSetClause()
    {
        return $this->set_builder->getClause();
    }
    
    /**
     * Add tables to the list of tables in scope for this query.
     * @param \Segment\Model\Column $table An object containing the table information.
     */
    public function setTable(\Segment\Model\Column $table)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args());
        if(count($this->tables)===0){
            $this->tables[0] = $table->getTable();
        } else {
            $this->tables[$table->getColumn()] = $table->getTable();
        }
    }

    public function addReturnExpression(\Segment\Model\Expression $exp, $parenthesis = false, StringValue $alias = NULL)
    {
        $this->return_builder->setCallQueue('addReturnExpression', $exp, $parenthesis, $alias);
    }

    public function addSearchWildcard(\Segment\Model\Column $clmn, SingleValue $value, $required_param = FALSE)
    {
        $this->setDb($clmn->getDb());
        $this->setTable($clmn);
        $this->return_builder->setCallQueue('addSearchWildcard',...func_get_args());
    }

    public function getAddendum()
    {
        return $this->addendum;
    }

    public function getAddendumClause()
    {
        return $this->getAddendum();
    }

    /**
     * @deprecated since version 1
     */
    public function getColumns(){
        
    }

    public function getDeleteClause()
    {
        return $this->delete_builder->getClause();
    }

    public function getFromClause()
    {
        return $this->from_builder->getClause();
    }

    public function getPutValues()
    {
        return $this->put_values;
    }

    /**
     * @deprecated since version 1
     */
    public function getWhere(){
        
    }

    public function getWhereClause()
    {
        return $this->where_builder->getClause();
    }

    public function getWhereValues()
    {
        return $this->where_values;
    }

    
    
    public function getClause(\Segment\Model\Table ...$tables)
    {}

    public function getReturnClause()
    {
        return $this->return_builder->getClause();
    }

}