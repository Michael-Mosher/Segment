<?php

interface Factory
{
    /** 
     * @param {string} $output Output type to be handled by instance
     * @param {string} $input Input type to be handled by instance
     * @return {Object} Instance of type Factory creates
     */
    public function getInstance($output, $input);
}

interface FunctionSetter
{
    /**
     * @param {Object<string, Callable>} $functions The functions to set in $instance
     * @param {Object} $instance Object passed by reference and set with Callable
     *     objects from $functions
     */
    public function setInstanceFunctions(array $functions, &$instance);
}

class Utilities
{
    public static function parseREST($rest)
    {
        $answer = array();
        if(is_string($rest)&&!empty(trim($rest))){
            $temp_string = explode('?', $rest);
            $key = '';
            $temp_rest = count($temp_string)==2 ? explode('&', $temp_string[1]) : explode('&', $temp_string[0]);
            foreach($temp_rest as $temp_duple){
                if(strpos($temp_duple, "=")!==FALSE){
                    list($key, $value) = explode('=', $temp_duple);
                    error_log('Utilities::parseREST explode equal sign $key: ' . print_r($key, TRUE)
                            . ' versus modified: ' . print_r(trim(urldecode(html_entity_decode($key))),TRUE)
                            . "\nand value: " . print_r($value, TRUE) . ' versus modified: '
                            . print_r(trim(urldecode(html_entity_decode($value))), TRUE));
                    if(strlen(trim(urldecode(html_entity_decode($key))))>0
                            &&strlen(trim(urldecode(html_entity_decode($value))))>0)
                        $answer[trim(urldecode(html_entity_decode(strtolower($key), ENT_HTML5)))]
                            = trim(urldecode(html_entity_decode($value, ENT_HTML5)));
                }
            }
        }
        return $answer;
    }
    
    public static function callableUCFirst(&$value, $index, array $array = NULL, callable $other_call = NULL)
    {
        $value = ucfirst($value);
    }
    
    public static function callableCURLFailureTest($handle, $url)
    {
        if(!is_resource($handle))
            throw new InvalidArgumentException('CallableCURLStatusTest__invoke requires first argument to '
                    . ' be a cURL handle resource. Provided: ' . print_r($handle, TRUE));
        $status = curl_getinfo($handle);
        if(($status['http_code']>=300||$status['http_code']<200)&&$tries<$this->max_tries)
            return TRUE;
        else
            return FALSE;
    }
    
    public static function checkArrayEmpty(array $array)
    {
        $answer = true;
        foreach($array as $key => $value){
            if(is_array($value)){
                $answer = $answer&&Utilities::checkArrayEmpty($value);
            } else {
                $answer = $answer&&empty($value);
            }
            if(!$answer)
                break;
        }
        return $answer;
    }
    
    public static function extractFileName($php_self)
    {
        if(!is_string($php_self))
            throw new InvalidArgumentException('extractFileName requires first argument to be text string.'
                    . ' Given: ' . print_r(var_dump($php_self), true));
        $pieces = explode('.' , $php_self);
        if(strpos($pieces[0], '/')!==FALSE){
            $pieces = explode('/' , $pieces[0]);
            array_reverse($pieces);
        }

        if($pieces[0] == 'index'){
            $pieces[0] = '';
        }
        return $pieces[0];
    }
    
    public static function getRandomString($string_length)
    {
        if(!is_integer($string_length))
            throw new InvalidArgumentException('Utilities::getRandomString '
                    . 'requires first argument to be text string.'
                    . ' Provided: ' . print_r($string_length, TRUE));
        $pool = '`~1!2@3#45%6^7&8*9(0)-_=+|]}[{pPoOiIuUyYtTrReEwWqQaAsSDdfFGgHhJjKkLl:;"?/.>,<mMnNbBvVcCxXzZ';
        $answer = '';
        $last_char_pos = strlen($pool)-1;
        for($i=0;$i<$string_length; $i++){
            $answer .= $pool[random_int(0, $last_char_pos)];
        }
        return $answer;
    }
    
    public static function isImage($file_type)
    {
        $answer = FALSE;
        switch(strtolower($file_type)){
            case 'jgp': $answer = TRUE;
                break;
            case 'png': $answer = TRUE;
                break;
            case 'gif': $answer = TRUE;
                break;
            case 'svg': $answer = TRUE;
                break;
            case 'bmp': $answer = TRUE;
                break;
            case 'webp': $answer = TRUE;
                break;
            default:
                break;
        }
        return $answer;
    }
    
    public static function isArrayScalar(array $arr)
    {
        $answer = TRUE;
        foreach($arr as $key => $value){
            if($answer)
                $answer = is_int($value);
        }
        return $answer;
    }
    
    public static function isRestAction($rest_action)
    {
        $answer = FALSE;
        if(is_string($rest_action)){
            switch (strtolower(trim ($rest_action))){
                case 'get':
                case 'put':
                case 'post':
                case 'delete':
                case 'header':
                    $answer = TRUE;
                default :
                    break;
            }
            
        }
        return $answer;
    }

    public static function removeREST($php_self)
    {
        if(!is_string($php_self))
            throw new InvalidArgumentException('removeREST requires first argument to be text string.'
                    . ' Given: ' . print_r(var_dump($php_self), true));
        $pieces = explode('?' , $php_self);
        return $pieces[0];
    }

    public static function categoryExists($category)
    {
        if(!is_string($php_self))
            throw new InvalidArgumentException('categoryExists requires first argument to be text string.'
                    . ' Given: ' . print_r(var_dump($category), true));
        $category = strtolower($category);
        $answer = FALSE===array_search($category, json_decode(__CATEGORIES__), TRUE) ? FALSE : TRUE;
        return $answer;
    }

    public static function arrayCopy(array $array)
    {
        $result = array();
        foreach($array as $key => $val){
            if( is_array($val) ) {
                $result[$key] = self::arrayCopy($val);
            } else if(is_object($val)){
                $result[$key] = clone $val;
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }
    
    public static function makeRESTful(array $rest)
    {
        $answer = '';
        foreach($rest as $key => $value){
            $value = is_array($value)||is_object($value) ? json_encode($value) : $value;
            if((!is_null($key))&&$key!==''&&$key!==FALSE){
                $answer .= empty($answer) ? '?' : '';
                $addendum = urlencode(htmlspecialchars(trim($key), ENT_NOQUOTES | ENT_HTML5)) . '='
                        . urlencode(htmlspecialchars(trim($value), ENT_NOQUOTES | ENT_HTML5));
                $answer .= !empty($answer)&&!empty($addendum) ? '&' . $addendum : $addendum;
            }
        }
        return print_r($answer, true);
    }
    
    public static function JSONToArray($string)
    {
        if(!is_string($string))
            throw new InvalidArgumentException('JSONToArray requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($string, TRUE));
        if(strlen($string)>2&&$string[0]=="{"&&$string[strlen($string)-1]=="}"){
            $array = json_decode($string, TRUE);
            if(is_array($array))
                return $array;
            else
                $string = $array;
        }
        return [
            $string
        ];
    }
    
    public static function mapableJsonEncode(&$value, $key)
    {
        error_log('mapableJsonEncode $value: ' . print_r($value, TRUE) . ' versus encode: '
                . print_r(json_encode($value), TRUE));
        if(is_array($value)||(is_a($value, 'Traversable')&&is_a($value, 'ArrayAccess')))
            Utilities::traversableArrayWalk($value, "Utilities::mapableJsonEncode");
        if(is_object($value))
            $value = get_object_vars($value);
        $value = is_string($value)||is_bool($value)||is_numeric($value)||is_null($value) ?
                $value : json_encode($value);
    }
    
    public static function mapableJsonDecode(&$value, $key, $preserve_if_not_array = TRUE)
    {
        error_log('mapableJsonDecode $value: ' . print_r($value, TRUE) . ' versus decode: '
                . print_r(json_decode($value, TRUE), TRUE));
        if($preserve_if_not_array)
            $value = is_array (json_decode ($value, TRUE)) ? json_decode($value, TRUE) : $value;
        else
            $value = json_decode($value, TRUE);
    }
    
    public static function traversableArrayWalk(
            /*Traversable||ArrayAccess||array*/ &$collection, callable $fn, ...$mixed_args
            ){
        if(!is_array($collection)&&!is_a($collection, 'Traversable')&&!is_a($collection, 'ArrayAccess')){
            throw new InvalidArgumentException('Utilities::traversableArrayWalk expects first'
                    . ' argument to be either Traversable or ArrayAccess or array. Provided: '
                    . print_r($collection, TRUE));
        }
        if(is_a($collection, 'Traversable')){
                throw new InvalidArgumentException('Utilities::traversibleArrayWalk expects first'
                    . ' argument to be ArrayAccess. Provided: ' . print_r($collection, TRUE));
            foreach($collection as $key => $value){
                $fn($value, $key, $mixed_args);
                $collection[$key] = $value;
            }
        } else if(is_array($collection)||is_a($collection, 'ArrayAccess')){
            for($i=count($collection)-1;$i>-1;--$i){
                $fn($collection[$i], $i, $mixed_args);
                $collection[$i] = $collection[$i];
            }
        }
    }
    
    public static function traversableArrayFilter(
            /*Traversable||ArrayAccess||array*/ $collection, callable $fn, ...$mixed_args
            ){
        if(!is_array($collection)||!is_a($collection, 'Traversable')||!is_a($collection, 'ArrayAccess')){
            throw new InvalidArgumentException('Utilities::traversableArrayWalk expects first'
                    . ' argument to be either Traversable or ArrayAccess or an array. Provided: '
                    . print_r($collection, TRUE));
        }
        if(is_array($collection))
            $answer = array();
        else{
            $collection_class = ReflectionClass::getName($collection);
            $answer = new $collection_class();
        }
        if($mixed_args[0]===ARRAY_FILTER_USE_KEY){
            if(is_a($collection, 'Traversable')){
                foreach($collection as $key => $value){
                    if($fn($key, $mixed_args))
                        $answer[$key] = $value;
                }
            } else if(is_a($collection, "ArrayAccess")||  is_array($collection)){
                for($i=count($collection)-1;$i>-1;--$i){
                    if($fn($i, $mixed_args))
                        $answer[$i] = $collection[$i];
                }
            }
        } else if($mixed_args[0]===ARRAY_FILTER_USE_BOTH){
            if(is_a($collection, 'Traversable')){
                foreach($collection as $key => $value){
                    if($fn($value, $key, $mixed_args))
                        $answer[$key] = $value;
                }
            } else if(is_a($collection, "ArrayAccess")||  is_array($collection)){
                for($i=count($collection)-1;$i>-1;--$i){
                    if($fn($collection[$i],$i, $mixed_args))
                            $answer[$i] = $collection[$i];
                }
            }
        } else {
            if(is_a($collection, 'Traversable')){
                foreach($collection as $key => $value){
                    if($fn($value, $mixed_args))
                        $answer[$key] = $value;
                }
            } else if(is_a($collection, "ArrayAccess")||is_array($collection)){
                for($i=count($collection)-1;$i>-1;--$i){
                    if($fn($collection[$i], $mixed_args))
                            $answer[$i] = $collection[$i];
                }
            }
        }
        return $answer;
    }
}

class JsonHandler {
 
    protected static $_messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );
 
    public static function encode($value, $options = 0) {
        $result = json_encode($value, $options);
 
        if($result)  {
            return $result;
        }
 
        throw new RuntimeException(static::$_messages[json_last_error()]);
    }
 
    public static function decode($json, $assoc = false) {
        $result = json_decode($json, $assoc);
 
        if($result) {
            return $result;
        }
 
        throw new RuntimeException(static::$_messages[json_last_error()]);
    }
 
}

class AnObj
{
  protected $methods = array();
  protected $properties = array();
 
    public function __construct(array $options)
    {
        
        foreach($options as $key => $opt) {
            //integer, string, float, boolean, array
            if(is_array($opt) || is_scalar($opt)) {
                $this->properties[$key] = $opt;
                unset($options[$key]);
            }
        }
 
        $this->methods = $options;
         foreach($this->properties as $k => $value)
             $this->{$k} = $value;
    }
 
    public function __call($name, $arguments)
    {
        $callable = null;
        if (array_key_exists($name, $this->methods))
            $callable = $this->methods[$name];
        else if(isset($this->$name))
            $callable = $this->$name;
 
        if (!is_callable($callable))
            throw new BadMethodCallException("Method {$name} does not exists");
 
        return call_user_func_array($callable, $arguments);
    }
}

class Rest implements Iterator, ArrayAccess
{
    private $assoc_array;
    private $position = 0;
    public function __construct($string)
    {
        error_log(print_r(debug_backtrace(), TRUE));
        error_log('Rest construct $string: ' . print_r($string, TRUE));
        if(!is_string($string))
            throw new InvalidArgumentException('Rest constructor requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($string, TRUE));
        $this->assoc_array = Utilities::parseREST($string);
        array_walk($this->assoc_array, 'Utilities::mapableJsonDecode');
        error_log('Rest construct $this->assoc_array: ' . print_r($this->assoc_array, TRUE));
    }
    
    public function __clone()
    {
        $this->assoc_array = json_decode(json_encode($this->assoc_array), TRUE);
    }
    
    public function getAssociativeArray()
    {
        return $this->assoc_array;
    }
    
    public function getRest()
    {
        array_walk($this->assoc_array, 'Utilities::mapableJsonEncode');
        return Utilities::makeRESTful($this->assoc_array);
    }
    
    public function getValue($key)
    {
        if(!is_string($key))
            throw new InvalidArgumentException('getValue requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($key, TRUE));
        if($this->hasKey($key))
            return $this->assoc_array[$key];
    }
    
    public function hasKey($key = NULL)
    {
        if(!is_string($key)&&!is_null($key))
            throw new InvalidArgumentException('hasKey requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($key, TRUE));
        return isset($this->assoc_array[$key]);
    }
    
    public function setValue($key, $mixed_value)
    {
        if(!is_string($key))
            throw new InvalidArgumentException('setValue requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($key, TRUE));
        $duple = array($key => $mixed_value);
        if(isset($duple[$key])&&!is_null($duple[$key])&&(!is_string($duple[$key])||strlen($duple[$key])>0)){
            $duple[$key] = is_string($duple[$key])&&is_array(json_decode($duple[$key]))
                    ? json_decode($duple[$key]) : is_array($duple[$key]) ? $duple[$key]
                    : print_r($duple[$key], TRUE);
            array_walk($duple, $this->value_filter);
            $this->assoc_array = $duple[$key] + $this->assoc_array;
        }
    }
    
    public function removeKey($key)
    {
        if(!is_string($key))
            throw new InvalidArgumentException('Rest->removeKey requires first argument to '
                    . 'be of type text string. Provided: ' . print_r($key, TRUE));
        if($this->hasKey($key)){
            unset($this->assoc_array[$key]);
        }
    }
    
    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        $key = array_keys($this->assoc_array)[$this->position];
        return $this->getValue($key);
    }

    function key()
    {
        return array_keys($this->assoc_array)[$this->position];
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        $key = array_keys($this->assoc_array)[$this->position];
        return $this->hasKey($key);
    }
    
    public function offsetSet($offset, $value)
    {
        if(is_null($offset))
            $offset = PHP_INT_MAX;
        try{
            $this->setValue($offset, $value);
        } catch(Exception $e){
            error_log(print_r($e->getMessage(),TRUE));
        }
    }

    public function offsetExists($offset)
    {
        return $this->hasKey($offset);
    }

    public function offsetUnset($offset)
    {
        $this->removeKey($offset);
    }

    public function offsetGet($offset)
    {
        return $this->hasKey($offset) ? $this->getValue($offset) : NULL;
    }
    
}

class ViewSegment
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
    
    public function setLocation($value)
    {
        if(!is_string($value))
            throw new InvalidArgumentException(__CLASS__ . '->setLocation expects first argument to be text string.'
                    . ' Provided: ' . print_r($value, TRUE));
        switch (strtolower($value)){
            case 'main':
            case 'secondary':
            case 'menu':
            case 'header':
            case 'footer':
            case 'foreground':
            case 'background': $this->attributes['location'] = $value;
                break;
            default:
                throw new InvalidArgumentException(__CLASS__ . '->setLocation invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    public function setType($value)
    {
        if(!is_string($value))
            throw new InvalidArgumentException(__CLASS__ . '->setType expects first argument to be text string.'
                    . ' Provided: ' . print_r($value, TRUE));
        switch (strtolower($value)){
            case 'text':
            case 'embedded':
            case 'interactive':
                $this->attributes['type'] = $value;
            default:
                throw new InvalidArgumentException(__CLASS__ . '->setType invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    public function setFunction($value)
    {
        if(!is_string($value))
            throw new InvalidArgumentException(__CLASS__ . '->setFunction expects first argument to be text string.'
                    . ' Provided: ' . print_r($value, TRUE));
        switch (strtolower($value)){
            case 'static':
            case 'form':
            case 'hyper':
            case 'toggle':
            case 'radio':
            case 'check':
            case 'form_begin':
            case 'form_submit': $this->attributes['function'] = $value;
            default:
                throw new InvalidArgumentException(__CLASS__ . '->setFunction invalid argument.'
                    . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    public function setName($value)
    {
        if(!is_string($value))
            throw new InvalidArgumentException(__CLASS__ . '->setName expects first argument to be text string.'
                    . ' Provided: ' . print_r($value, TRUE));
        $this->attributes['name'] = $value;
    }
    
    public function setValue($value)
    {
        $this->attributes['value'] = $value;
    }
    
    public function setChild(Segment $value)/* children is array of segments */
    {
        if(!is_string($key))
            throw new InvalidArgumentException(__CLASS__ . '->setChild expects first argument to be text string.'
                    . ' Provided: ' . print_r($key, TRUE));
        $this->attributes['children'][] = $value;
    }
    
    public function setValueOptions($key, $value)
    {
        if(!is_string($key))
            throw new InvalidArgumentException(__CLASS__ . '->setValueOptions expects first argument to be text string.'
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
                if(is_string($value)&&Utilities::isRestAction($value))
                    $this->attributes['value_options'][strtolower(trim($key))] = $value;
                break;
            case 'target':
                if(filter_var($value, FILTER_VALIDATE_URL))
                    $this->attributes['value_options'][strtolower($key)] = $value;
                break;
            case 'select_multiple':
                if(is_bool($value))
                        $this->attributes['value_options'][strtolower($key)] = $value;
                break;
            case 'possible_values':
            case 'suggested_values':
                if(is_array($value)&&Utilities::isArrayScalar($value))
                    $this->attributes[strtolower($key)] = $value;
                break;
            case 'format': 
                if(is_string($value)&&$this->isFormatValue($value))
                    $this->attributes[strtolower(trim($key))] = $value;
                break;
            default:
                if(
                        is_string($key)&&strlen($key)>2&&$key[0]===':'&&
                        $key[1]==='|'&&filter_var($value, FILTER_VALIDATE_URL)
                        ){
                            $this->attributes[$key] = $value;
                            break;
                } else
                    throw new InvalidArgumentException(__CLASS__ . '->setLocation invalid argument.'
                        . ' Provided: ' . print_r($value, TRUE));
        }
    }
    
    private function isFormatValue($value)
    {
        $answer = FALSE;
        if(is_string($var)){
            switch(strtolower(trim($value))){
                case 'text':
                case 'integer':
                case 'accounting':
                case 'scientific':
                case 'password':
                case 'email':
                case 'url':
                case 'file':
                case 'time':
                case 'date':
                case 'datetime':
                    $answer = TRUE;
                default:
                    break;
            }
        }
        return $answer;
    }
    
    public function toJson()
    {
        return json_encode($this->attributes);
    }
}

class RESTAction
{
    public static function get($rest, $destination = '/test/controller/osmosis.php')
    {
        error_log('RESTAction::get $rest: ' . print_r($rest, true) . ' $destination: '
                . print_r(__DOMAIN__ . __TEST__ . $destination . $rest, true));
        try {
            $ch = curl_init();
            /*error_log('utilities/templates.php RESTAction::get $destination: ' . print_r(__DOMAIN__ . $destination . $rest, true) . ' is $rest an array: '
                    . print_r(is_array($rest), true) . ' and is $destination an array: ' . print_r(is_array($destination), true) . ' debug backtrace: ' . print_r(debug_backtrace(), true)); */
            // set URL and other appropriate options
            $curlopt_domain_argument =  __DOMAIN__ . __TEST__ . $destination . $rest;
            curl_setopt($ch, CURLOPT_URL, $curlopt_domain_argument);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION; true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            // grab URL and pass it to the browser    
            $json = curl_exec($ch);
            error_log('RESTAction::get JSON: ' . print_r($json,true));
            $status = curl_getinfo($ch);
                        // close cURL resource, and free up system resources    
                        curl_close($ch);

            if($status['http_code']!=200){
                if($status['http_code'] == 301 || $status['http_code'] == 302) {
                    list($header) = explode("\r\n\r\n", $json, 2);
                    $matches = array();
                    preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
                    $url = trim(str_replace($matches[1],"",$matches[0]));
                    $url_parsed = parse_url($url);
                    error_log('RESTAction::get (modified) 301 error $url_parsed: ' . print_r($url_parsed,true)
                            . ' exploded carriage returns from result: ' . print_r($header, true)
                            . ' after regular expression match /(Location:|URI:)[^(\n)]*/ '
                            . print_r($matches, true)
                            . ' after replaced matches: ' . print_r($url, true));
                    $url_parsed = str_replace('??', '?', $url_parsed);
                    error_log('RESTAction::get (modified) 301 error $url_parsed after ?? replace: '
                            . print_r($url_parsed,true));
                    $json = (isset($url_parsed))? geturl($url):'';
                }
            }
//return $html;
//}
        } catch (Exception $exc) {
            error_log(print_r($exc->getTraceAsString(),true));
        }
        
        return $json;
    }
}
