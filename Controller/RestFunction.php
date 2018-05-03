<?php

namespace Segment\Controller;

/**
 *
 * @author Michael-Mosher
 */
interface RestFunction
{
    public function __construct(RestRequest $rr);
    
    public function getRestRequest():TestRestRequest;
}
