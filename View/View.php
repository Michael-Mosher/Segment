  <?php
namespace Segment\View\production;

class SegmentConstruct implements \Segment\View\SegmentConstructor
{
    use \Segment\utilities\AbstractClassNamesGetter;
    
    private $rest;
    private $proj_acronym;
    private $proj_title;
    
    /**
     * 
     * @param string $project_acronym
     * @param string $project_title
     */
    public function __construct($project_acronym, $project_title)
    {
        $this->proj_acronym = $project_acronym;
        $this->proj_title = $project_title;
    }
    
    

    /**
     * Produces a \Segment\View\Segment instance based on 
     * @param array<\Segment\utilities\Record> $model_output
     * @return \Segment\View\Segment
     */
    public function segmentConstruct(\Segment\utilities\Record ...$model_output)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $record_id = $model_output[0]->getId();
        $class_name = implode(
                '',
                array_map(
                        \Segment\utilities\Utilities::callableUCFirst,
                        explode($class_name, '_')
                        )
                );
        $class_name = __NAMESPACE__.'\\'.ucfirst($this->proj_acronym).'\\'.
                ucfirst($this->proj_acronym).$class_name;
                
        return new $class_name($model_output);
    }
}

class SegmentConstructGetRestOnly extends \Segment\View\SegmentConstruct
{
    private $rest;
    
    public function __construct(\Segment\utilities\Rest $rest)
    {
        $this->rest = $rest;
    }

    public function segmentConstruct(\Segment\utilities\Record ...$model_output)
    {
        $class_name = $this->rest->getValue('x');
        if(strpos($class_name, '_')!==FALSE){
            $class_name = implode('', array_map("ucfirst", explode($class_name, '_')));
        } else
            ucfirst($class_name);
        return new $class_name($model_output);
    }
    
    public function __invoke(array $model_output)
    {
        return $this->segmentConstruct($model_output);
    }

}

class ViewSegment implements \Segment\View\Segment
{
    private $attributes = array(
        'location' => "",
        'type' => "",
        'value' => "",
        'function' => "",
        'name' => "",
        'children' => [],
        'value_options' => []
    );
    
    const FORMAT_TEXT = 'text';
    const FORMAT_INT = 'integer';
    const FORMAT_ACCOUNT = 'accounting';
    const FORMAT_SCI = 'scientific';
    const FORMAT_PASSW = 'password';
    const FORMAT_EMAIL = 'email';
    const FORMAT_URL = 'url';
    const FORMAT_FILE = 'file';
    const FORMAT_TIME = 'time';
    const FORMAT_DATE = 'date';
    const FORMAT_DATETIME = 'datetime';
    
    /**
     * 
     * @param string $value
     * @throws \InvalidArgumentException
     */
    public function setLocation($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        switch (strtolower($value)){
            case 'main':
            case 'secondary':
            case 'menu':
            case 'header':
            case 'footer':
            case 'foreground':
            case 'background': 
            case '': $this->attributes['location'] = $value;
                break;
            default:
                throw new \InvalidArgumentException(__NAMESPACE__ .'\\'.__CLASS__ . '->'.__METHOD__
                        .' invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    /**
     * 
     * @param string $value
     * @throws \InvalidArgumentException
     */
    public function setType($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        switch (strtolower($value)){
            case 'text':
            case 'embedded':
            case 'interactive':
            case '': $this->attributes['type'] = $value;
                break;
            default:
                throw new \InvalidArgumentException(__NAMESPACE__ .'\\'.__CLASS__ .'->'.
                        __METHOD__.' invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    /**
     * 
     * @param string $value
     * @throws \InvalidArgumentException
     */
    public function setFunction($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        switch (strtolower($value)){
            case 'static':
            case 'form':
            case 'hyper':
            case 'toggle':
            case 'radio':
            case 'check':
            case 'form_begin':
            case 'form_submit': 
            case '': $this->attributes['function'] = $value;
                break;
            default:
                throw new \InvalidArgumentException(__NAMESPACE__.'\\'.__CLASS__.
                        '->' . __METHOD__ .' invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    /**
     * 
     * @param string $value
     * @throws \InvalidArgumentException
     */
    public function setName($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['name'] = $value;
    }
    
    public function setValue($value)
    {
        $this->attributes['value'] = $value;
    }
    
    public function setChild(\Segment\View\Segment $value)/* children is array of segments */
    {
        $this->attributes['children'][] = $value;
    }
    
    /**
     * 
     * @param integer|float $value
     */
    public function setTextLengthOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['value_options']['text_length'] = $value;
    }
    
    /**
     * 
     * @param integer|float $value
     */
    public function setMinOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['value_options']['text_length'] = $value;
    }
    
    /**
     * 
     * @param integer|float $value
     */
    public function setMaxOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['value_options']['text_length'] = $value;
    }
    
    /**
     * 
     * @param integer|float $value
     */
    public function setIncrementOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['value_options']['text_length'] = $value;
    }
    
    public function setRestGetOpt()
    {
        $this->attributes['value_options']['rest_action'] = 'GET';
    }
    
    public function setRestPostOpt()
    {
        $this->attributes['value_options']['rest_action'] = 'POST';
    }
    
    public function setRestPutOpt()
    {
        $this->attributes['value_options']['rest_action'] = 'PUT';
    }

    public function setRestDeleteOpt()
    {
        $this->attributes['value_options']['rest_action'] = 'DELETE';
    }
    
    /**
     * 
     * @param string|resource $value
     */
    public function setTargetOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        if(is_string($value)&&!filter_var($value, FILTER_VALIDATE_URL)){
            
        } else {
            $this->attributes['value_options']['target'] = $value;
        }
    }
    
    /**
     * Adds the set of values the Segment is limited to
     * @param array<mixed> $value Scalar array
     */
    public function setPossibleValOpt(array $value)
    {
        $this->attributes['value_options']['possible_values'] =
                array_unique(array_values ($value), SORT_NATURAL);
    }
    
    /**
     * Adds the set of default values for the Segment when multiple are available and variable
     * @param array $value
     */
    public function setSuggestedValOpt(array $value)
    {
        $this->attributes['value_options']['suggested_values'] =
                array_unique(array_values ($value), SORT_NATURAL);
    }
    
    /**
     * Adds whether the Segment can be multiple values
     * @param boolean $value
     */
    public function setSelectMultiOpt($value)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $this->attributes['value_options']['select_multiple'] = $value;
    }
    
    /**
     * 
     * @param string $segment_target
     * @param string|resource $link_value
     */
    public function setSegmentLinkOpt($segment_target, $link_value)
    {
        if(is_string($key)&&strlen($key)>2&&$key[0]===':'&&
                $key[1]==='|'&&filter_var($value, FILTER_VALIDATE_URL)
                )
            $this->attributes[$key] = $value;
    }

    
    public function setValueOptions($key, $value)
    {
        if(!is_string($key))
            throw new \InvalidArgumentException(__NAMESPACE__ . __CLASS__ . '->'.
                    __CLASS__.' expects first argument to be text string.'
                    . ' Provided: ' . print_r($key, TRUE));
        switch (strtolower($key)){
            case 'text_length':
            case 'min':
            case 'max':
            case 'increment':
                if(is_numeric($value))
                    $this->attributes['value_options'][strtolower($key)] = $value;
                break;
            case 'rest_action':
                if(is_string($value)&&\Segment\utilities\Utilities::isRestAction($value))
                    $this->attributes['value_options'][strtolower(trim($key))] = $value;
                break;
            case 'target':
                if(filter_var($value, \FILTER_VALIDATE_URL))
                    $this->attributes['value_options'][strtolower($key)] = $value;
                break;
            case 'select_multiple':
                if(is_bool($value))
                        $this->attributes['value_options'][strtolower($key)] = $value;
                break;
            case 'possible_values':
            case 'suggested_values':
                if(is_array($value)&&\Segment\utilities\Utilities::isArrayScalar($value))
                    $this->attributes[strtolower($key)] = $value;
                break;
            case 'format': 
                if(is_string($value)&&$this->isFormatValue($value))
                    $this->attributes[strtolower(trim($key))] = $value;
                break;
            default:
                if(
                        is_string($key)&&strlen($key)>2&&$key[0]===':'&&
                        $key[1]==='|'&&filter_var($value, \FILTER_VALIDATE_URL)
                        ){
                            $this->attributes[$key] = $value;
                            break;
                } else
                    throw new \InvalidArgumentException(__NAMESPACE__ . __CLASS__ . '->setLocation invalid argument.'
                        . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    /**
     * 
     * @param string $value
     * @return boolean
     */
    private function setFormatOpt($format_const)
    {
        \Segment\utilities\Utilities::areArgumentsValid(__METHOD__, func_get_args(), $this);
        $reflec = new \ReflectionClass('ViewSegment');
        $consts = $reflec->getConstants();
        if($key = array_search($format_const, $consts)&&stripos($key, 'FORMAT_'))
            $this->attributes['format'] = strtolower ($value);
    }
    
    
    public function isFormatValue($value)
    {
        $answer = FALSE;
        $reflec = new \ReflectionClass('ViewSegment');
        $consts = $reflec->getConstants();
        if($key = array_search($value, $consts)&&stripos($key, 'FORMAT_'))
            $answer = TRUE;
        return $answer;
    }
    
    public function toJson()
    {
        return json_encode($this->attributes);
    }
}
