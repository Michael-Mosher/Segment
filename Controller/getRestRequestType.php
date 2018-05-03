<?php

namespace Segment\Controller\production;

function getRestRequestType(Segment\utilities\production\Rest $rest, string $req_method)
{
    if($rest->hasKey("args")){
        $args = json_decode($rest->getValue("args"), TRUE);
        if(!is_null($args)){
            foreach ($args as $key => $value) {
                switch(strtolower($key)){
                    case "search":
                    case "field_set":
                    case "field_count":
                    case "field_avg":
                    case "field_mode":
                    case "field_median":
                    case "field_firstq":
                    case "field_thirdq":
                        return "SEARCH";
                    case "post":
                        return "POST";
                    case "put":
                        return "PUT";
                    case "delete":
                        return "DELETE";
                    default:
                }
            }
            return $req_method;
        } else
            return $req_method;
            
    } else
        return $req_method;
}